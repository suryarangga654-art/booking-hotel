<?php

namespace App\Http\Controllers\Api\Tamu;

use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PemesananController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $pemesanan = Pemesanan::with([
            'user',
            'pembayaran'
        ])
        ->where('users_id', $user->id)
        ->orderByDesc('id')
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data pemesanan berhasil diambil',
            'data' => $pemesanan
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $pemesanan = Pemesanan::with([
            'user',
            'pembayaran'
        ])
        ->where('users_id', $user->id)
        ->find($id);

        if (!$pemesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pemesanan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pemesanan berhasil diambil',
            'data' => $pemesanan
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'jumlah_total' => 'required|integer|min:0',
        ]);

        $pemesanan = Pemesanan::create([
            'kode_pemesanan' => $this->generateKodePemesanan(),
            'users_id' => $user->id,
            'jumlah_total' => $validated['jumlah_total'],
            'status_pemesanan' => 'menunggu',
            'status_pembayaran' => 'belum_dibayar',
        ]);

        $pemesanan->load([
            'user',
            'pembayaran'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pemesanan berhasil dibuat',
            'data' => $pemesanan
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();

        $pemesanan = Pemesanan::find($id);

        if (!$pemesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pemesanan tidak ditemukan'
            ], 404);
        }

        if ($pemesanan->users_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Pemesanan ini bukan milik Anda'
            ], 403);
        }

        $validated = $request->validate([
            'jumlah_total' => 'required|integer|min:0',
        ]);

        $pemesanan->update([
            'jumlah_total' => $validated['jumlah_total'],
        ]);

        $pemesanan->load([
            'user',
            'pembayaran'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pemesanan berhasil diubah',
            'data' => $pemesanan
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $pemesanan = Pemesanan::where('users_id', $user->id)
            ->find($id);

        if (!$pemesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pemesanan tidak ditemukan'
            ], 404);
        }

        if ($pemesanan->status_pemesanan !== 'menunggu') {
            return response()->json([
                'success' => false,
                'message' => 'Pemesanan yang sudah diproses tidak dapat dihapus'
            ], 422);
        }

        $pemesanan->update([
            'status_pemesanan' => 'dibatalkan'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pemesanan berhasil dibatalkan'
        ]);
    }

    private function generateKodePemesanan()
    {
        do {
            $kode = 'PSN-' . strtoupper(Str::random(8));
        } while (Pemesanan::where('kode_pemesanan', $kode)->exists());

        return $kode;
    }
}