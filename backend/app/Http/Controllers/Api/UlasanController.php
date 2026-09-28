<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ulasan;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UlasanController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Ulasan::with(['user', 'pemesanan.detailPemesanan.kamar.tipeKamar'])
                ->latest('created_at')
                ->get(),
        ]);
    }

    // PUBLIC: List Ulasan per Tipe Kamar
    public function getByTipeKamar($tipeKamarId)
    {
        $ulasan = Ulasan::with('user')
            ->whereHas('pemesanan.detailPemesanan.kamar', fn ($query) => $query->where('tipe_kamar_id', $tipeKamarId))
            ->latest('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $ulasan
        ]);
    }

    // TAMU: Kirim Ulasan
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pemesanan_id' => 'required|exists:pemesanan,id',
            'penilaian' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string',
        ]);

        $pemesanan = Pemesanan::whereKey($validated['pemesanan_id'])
            ->where('pengguna_id', $request->user()->id)
            ->first();

        if (!$pemesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pemesanan tidak ditemukan di akun kamu.',
            ], 404);
        }

        if ($pemesanan->status_pemesanan !== 'check_out') {
            throw ValidationException::withMessages([
                'pemesanan_id' => ['Ulasan hanya bisa dikirim setelah status pemesanan check-out.'],
            ]);
        }

        if ($pemesanan->ulasan()->exists()) {
            throw ValidationException::withMessages([
                'pemesanan_id' => ['Pemesanan ini sudah memiliki ulasan.'],
            ]);
        }

        $ulasan = Ulasan::create([
            'pengguna_id' => $request->user()->id,
            'pemesanan_id' => $pemesanan->id,
            'penilaian' => $validated['penilaian'],
            'komentar' => $validated['komentar'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas ulasan Anda!',
            'data' => $ulasan
        ], 201);
    }

    public function destroy(int $id)
    {
        Ulasan::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Ulasan berhasil dihapus.']);
    }
}