<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use App\Models\Kamar;
use App\Models\LayananTambahan;
use App\Models\Pembayaran;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PemesananController extends Controller
{
    // TAMU: List Pemesanan milik Tamu yang sedang Login
    public function myBookings(Request $request)
    {
        $pemesanan = Pemesanan::with(['detailPemesanan.kamar.tipeKamar', 'layananTambahan', 'pembayaran', 'ulasan'])
            ->where('pengguna_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pemesanan
        ]);
    }

    // PUBLIC / TAMU: Buat Reservasi Baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kamar_id' => 'required|exists:kamar,id',
            'tanggal_check_in' => 'required|date|after_or_equal:today',
            'tanggal_check_out' => 'required|date|after:tanggal_check_in',
            'layanan' => 'nullable|array',
            'layanan.*.id' => 'required_with:layanan|exists:layanan_tambahan,id',
            'layanan.*.jumlah' => 'nullable|integer|min:1',
            'metode_pembayaran' => 'required|in:transfer_bank,qris',
            'bukti_transfer' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'kode_promo' => 'nullable|string|max:40',
        ]);

        $kamar = Kamar::with('tipeKamar')->findOrFail($validated['kamar_id']);
        if ($kamar->status !== 'tersedia') {
            return response()->json(['success' => false, 'message' => 'Kamar sedang tidak tersedia.'], 422);
        }

        $checkIn = Carbon::parse($validated['tanggal_check_in']);
        $checkOut = Carbon::parse($validated['tanggal_check_out']);
        $totalMalam = $checkIn->diffInDays($checkOut);
        $bentrok = $kamar->detailPemesanan()
            ->where('tanggal_check_in', '<', $checkOut->toDateString())
            ->where('tanggal_check_out', '>', $checkIn->toDateString())
            ->whereHas('pemesanan', fn ($query) => $query->whereNotIn('status_pemesanan', ['dibatalkan', 'check_out']))
            ->exists();
        if ($bentrok) {
            return response()->json(['success' => false, 'message' => 'Kamar sudah dipesan pada tanggal tersebut.'], 422);
        }

        $subtotalKamar = $kamar->tipeKamar->harga_dasar * $totalMalam;
        $totalLayanan = 0;
        $layananData = [];

        foreach ($validated['layanan'] ?? [] as $layanan) {
            $item = LayananTambahan::findOrFail($layanan['id']);
            $jumlah = $layanan['jumlah'] ?? 1;
            $pengaliMalam = $item->satuan === 'per_hari' ? $totalMalam : 1;
            $subtotalItem = $item->harga * $jumlah * $pengaliMalam;
            $totalLayanan += $subtotalItem;
            $layananData[$item->id] = ['jumlah' => $jumlah, 'total_harga' => $subtotalItem];
        }

        $promo = null;
        $diskon = 0;
        if (!empty($validated['kode_promo'])) {
            $promo = Promo::where('kode', strtoupper(trim($validated['kode_promo'])))
                ->where('aktif', true)
                ->whereDate('tanggal_mulai', '<=', today())
                ->whereDate('tanggal_selesai', '>=', today())
                ->first();
            if (!$promo) {
                return response()->json(['success' => false, 'message' => 'Kode promo tidak aktif atau sudah kedaluwarsa.'], 422);
            }
            $diskon = $promo->jenis_diskon === 'persen'
                ? $subtotalKamar * min((float) $promo->nilai, 100) / 100
                : min($subtotalKamar, (float) $promo->nilai);
        }
        $grandTotal = max(0, $subtotalKamar + $totalLayanan - $diskon);
        $buktiPath = $request->file('bukti_transfer')->store('bukti-transfer', 'public');

        try {
            $pemesanan = DB::transaction(function () use ($request, $validated, $kamar, $subtotalKamar, $grandTotal, $diskon, $promo, $layananData, $buktiPath) {
                $pemesanan = Pemesanan::create([
                    'kode_pemesanan' => 'BK-' . strtoupper(Str::random(8)),
                    'pengguna_id' => $request->user()->id,
                    'jumlah_total' => $grandTotal,
                    'status_pemesanan' => 'menunggu',
                    'status_pembayaran' => 'belum_dibayar',
                    'promo_id' => $promo?->id,
                    'nilai_diskon' => $diskon,
                ]);
                $pemesanan->detailPemesanan()->create([
                    'kamar_id' => $kamar->id,
                    'tanggal_check_in' => $validated['tanggal_check_in'],
                    'tanggal_check_out' => $validated['tanggal_check_out'],
                    'harga_per_malam' => $kamar->tipeKamar->harga_dasar,
                    'jumlah_harga' => $subtotalKamar,
                ]);
                if ($layananData) {
                    $pemesanan->layananTambahan()->attach($layananData);
                }
                Pembayaran::create([
                    'pemesanan_id' => $pemesanan->id,
                    'metode_pembayaran' => $validated['metode_pembayaran'],
                    'bukti_transfer' => $buktiPath,
                    'jumlah_bayar' => $grandTotal,
                    'status' => 'menunggu',
                ]);
                return $pemesanan;
            });
        } catch (\Throwable $error) {
            Storage::disk('public')->delete($buktiPath);
            throw $error;
        }

        return response()->json([
            'success' => true,
            'message' => 'Pemesanan berhasil dibuat, silakan lakukan pembayaran.',
            'data' => $pemesanan->load(['detailPemesanan.kamar.tipeKamar', 'layananTambahan', 'pembayaran'])
        ], 201);
    }

    // ADMIN / RESEPSIONIS: List Semua Pemesanan (Dashboard)
    public function index(Request $request)
    {
        $query = Pemesanan::with(['user', 'detailPemesanan.kamar.tipeKamar', 'layananTambahan', 'pembayaran'])->latest();

        if ($request->has('status')) {
            $query->where('status_pemesanan', $request->status);
        }
        if ($request->has('limit')) {
            $query->limit(min(max($request->integer('limit'), 1), 100));
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status_pemesanan' => 'required|in:menunggu,dikonfirmasi,check_in,check_out,dibatalkan',
        ]);
        $pemesanan = Pemesanan::with('detailPemesanan')->findOrFail($id);
        $pemesanan->update(['status_pemesanan' => $validated['status_pemesanan']]);

        $kamarId = $pemesanan->detailPemesanan->first()?->kamar_id;
        if ($kamarId && $validated['status_pemesanan'] === 'check_in') {
            Kamar::whereKey($kamarId)->update(['status' => 'terisi']);
        } elseif ($kamarId && in_array($validated['status_pemesanan'], ['check_out', 'dibatalkan'], true)) {
            Kamar::whereKey($kamarId)->update(['status' => $validated['status_pemesanan'] === 'check_out' ? 'dibersihkan' : 'tersedia']);
        }

        return response()->json([
            'success' => true,
            'data' => $pemesanan->load(['user', 'detailPemesanan.kamar.tipeKamar', 'pembayaran']),
        ]);
    }

    // ADMIN / RESEPSIONIS: Assign Kamar & Check-In
    public function checkIn(Request $request, $id)
    {
        $request->validate([
            'kamar_id' => 'required|exists:kamar,id',
        ]);

        $pemesanan = Pemesanan::findOrFail($id);
        $kamar = Kamar::findOrFail($request->kamar_id);

        if ($kamar->status !== 'tersedia') {
            return response()->json([
                'success' => false,
                'message' => 'Kamar tidak tersedia atau sedang terisi.'
            ], 400);
        }

        $detail = $pemesanan->detailPemesanan()->firstOrFail();
        $detail->update(['kamar_id' => $kamar->id]);
        $pemesanan->update(['status_pemesanan' => 'check_in']);

        // Ubah status kamar fisik menjadi terisi
        $kamar->update(['status' => 'terisi']);

        return response()->json([
            'success' => true,
            'message' => 'Tamu berhasil Check-In.',
            'data' => $pemesanan->load(['user', 'detailPemesanan.kamar.tipeKamar'])
        ]);
    }

    // ADMIN / RESEPSIONIS: Check-Out
    public function checkOut($id)
    {
        $pemesanan = Pemesanan::findOrFail($id);

        $pemesanan->update(['status_pemesanan' => 'check_out']);

        $kamarId = $pemesanan->detailPemesanan()->value('kamar_id');
        if ($kamarId) {
            // Tandai kamar fisik butuh dibersihkan oleh Petugas Kebersihan
            Kamar::where('id', $kamarId)->update(['status' => 'dibersihkan']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tamu berhasil Check-Out. Status kamar diperbarui menjadi Dibersihkan.'
        ]);
    }
}