<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    // GET semua pembayaran
    public function index()
    {
        $pembayaran = Pembayaran::with('pemesanan')
            ->latest('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pembayaran
        ]);
    }

    // GET pembayaran berdasarkan ID
    public function show($id)
    {
        $pembayaran = Pembayaran::with('pemesanan')
            ->find($id);

        if (!$pembayaran) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $pembayaran
        ]);
    }

    // POST tambah pembayaran
    public function store(Request $request)
    {
        $request->validate([
            'pemesanan_id' => 'required|exists:pemesanan,id',
            'metode_pembayaran' => 'required|string|max:50',
            'nomor_transaksi' => 'required|string|max:100',
            'jumlah_bayar' => 'required|integer',
            'status' => 'required|string|max:50',
            'waktu_bayar' => 'nullable|date',
            'bukti_transfer' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $buktiTransfer = null;

        if ($request->hasFile('bukti_transfer')) {
            $buktiTransfer = $request->file('bukti_transfer')
                ->store('bukti_transfer', 'public');
        }

        $pembayaran = Pembayaran::create([
            'pemesanan_id' => $request->pemesanan_id,
            'metode_pembayaran' => $request->metode_pembayaran,
            'nomor_transaksi' => $request->nomor_transaksi,
            'jumlah_bayar' => $request->jumlah_bayar,
            'status' => $request->status,
            'waktu_bayar' => $request->waktu_bayar,
            'bukti_transfer' => $buktiTransfer,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil ditambahkan',
            'data' => $pembayaran
        ], 201);
    }

    // PUT ubah pembayaran
    public function update(Request $request, $id)
    {
        $pembayaran = Pembayaran::find($id);

        if (!$pembayaran) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'pemesanan_id' => 'required|exists:pemesanan,id',
            'metode_pembayaran' => 'required|string|max:50',
            'nomor_transaksi' => 'required|string|max:100',
            'jumlah_bayar' => 'required|integer',
            'status' => 'required|string|max:50',
            'waktu_bayar' => 'nullable|date',
            'bukti_transfer' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = [
            'pemesanan_id' => $request->pemesanan_id,
            'metode_pembayaran' => $request->metode_pembayaran,
            'nomor_transaksi' => $request->nomor_transaksi,
            'jumlah_bayar' => $request->jumlah_bayar,
            'status' => $request->status,
            'waktu_bayar' => $request->waktu_bayar,
        ];

        // Jika upload bukti transfer baru
        if ($request->hasFile('bukti_transfer')) {

            // Hapus file lama
            if ($pembayaran->bukti_transfer) {
                Storage::disk('public')->delete($pembayaran->bukti_transfer);
            }

            // Simpan file baru
            $data['bukti_transfer'] = $request->file('bukti_transfer')
                ->store('bukti_transfer', 'public');
        }

        $pembayaran->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil diubah',
            'data' => $pembayaran
        ]);
    }

    // DELETE pembayaran
    public function destroy($id)
    {
        $pembayaran = Pembayaran::find($id);

        if (!$pembayaran) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran tidak ditemukan'
            ], 404);
        }

        // Hapus bukti transfer
        if ($pembayaran->bukti_transfer) {
            Storage::disk('public')->delete($pembayaran->bukti_transfer);
        }

        $pembayaran->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dihapus'
        ]);
    }
}
