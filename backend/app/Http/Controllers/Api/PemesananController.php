<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PemesananController extends Controller
{
    // Menampilkan semua pemesanan
    public function index()
    {
        $pemesanan = Pemesanan::with([
            'pengguna',
            'detailPemesanan',
            'pembayaran',
            'ulasan'
        ])->latest('id')->get();

        return response()->json([
            'success' => true,
            'data' => $pemesanan
        ]);
    }

    // Menampilkan satu pemesanan
    public function show($id)
    {
        $pemesanan = Pemesanan::with([
            'pengguna',
            'detailPemesanan',
            'pembayaran',
            'ulasan'
        ])->find($id);

        if (!$pemesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pemesanan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $pemesanan
        ]);
    }

    // Menambahkan pemesanan
    public function store(Request $request)
    {
        $request->validate([
            'users_id' => 'required|exists:users,id',
            'jumlah_total' => 'required|integer|min:1',
            'status_pemesanan' => 'nullable|in:menunggu,dikonfirmasi,check_in,check_out,dibatalkan',
            'status_pembayaran' => 'nullable|in:belum_dibayar,dibayar_sebagian,lunas,dikembalikan',
        ]);

        $pemesanan = Pemesanan::create([
            'kode_pemesanan' => 'BOOK-' . strtoupper(Str::random(8)),
            'users_id' => $request->users_id,
            'jumlah_total' => $request->jumlah_total,
            'status_pemesanan' => $request->status_pemesanan ?? 'menunggu',
            'status_pembayaran' => $request->status_pembayaran ?? 'belum_dibayar',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pemesanan berhasil ditambahkan',
            'data' => $pemesanan
        ], 201);
    }

    // Mengubah pemesanan
    public function update(Request $request, $id)
    {
        $pemesanan = Pemesanan::find($id);

        if (!$pemesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pemesanan tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'users_id' => 'required|exists:users,id',
            'jumlah_total' => 'required|integer|min:1',
            'status_pemesanan' => 'required|in:menunggu,dikonfirmasi,check_in,check_out,dibatalkan',
            'status_pembayaran' => 'required|in:belum_dibayar,dibayar_sebagian,lunas,dikembalikan',
        ]);

        $pemesanan->update([
            'users_id' => $request->users_id,
            'jumlah_total' => $request->jumlah_total,
            'status_pemesanan' => $request->status_pemesanan,
            'status_pembayaran' => $request->status_pembayaran,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pemesanan berhasil diubah',
            'data' => $pemesanan
        ]);
    }

    // Menghapus pemesanan
    public function destroy($id)
    {
        $pemesanan = Pemesanan::find($id);

        if (!$pemesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pemesanan tidak ditemukan'
            ], 404);
        }

        $pemesanan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pemesanan berhasil dihapus'
        ]);
    }
}