<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PemesananController extends Controller
{
    /**
     * GET /api/admin/pemesanan
     */
    public function index()
    {
        $pemesanan = Pemesanan::with([
            'user',
            'pembayaran'
        ])
        ->orderByDesc('id')
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data pemesanan berhasil diambil',
            'data' => $pemesanan
        ]);
    }

    /**
     * GET /api/admin/pemesanan/{id}
     */
    public function show($id)
    {
        $pemesanan = Pemesanan::with([
            'user',
            'pembayaran'
        ])->find($id);

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

    /**
     * POST /api/admin/pemesanan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'users_id' => 'required|exists:users,id',
            'jumlah_total' => 'required|integer|min:0',
            'status_pemesanan' => 'nullable|in:menunggu,dikonfirmasi,check_in,check_out,dibatalkan',
            'status_pembayaran' => 'nullable|in:belum_dibayar,dibayar_sebagian,lunas,dikembalikan',
        ]);

        $validated['kode_pemesanan'] = $this->generateKodePemesanan();

        $validated['status_pemesanan'] =
            $validated['status_pemesanan'] ?? 'menunggu';

        $validated['status_pembayaran'] =
            $validated['status_pembayaran'] ?? 'belum_dibayar';

        $pemesanan = Pemesanan::create($validated);

        $pemesanan->load([
            'user',
            'pembayaran'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pemesanan berhasil ditambahkan',
            'data' => $pemesanan
        ], 201);
    }

    /**
     * PUT /api/admin/pemesanan/{id}
     */
    public function update(Request $request, $id)
    {
        $pemesanan = Pemesanan::find($id);

        if (!$pemesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pemesanan tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'users_id' => 'required|exists:users,id',
            'jumlah_total' => 'required|integer|min:0',
            'status_pemesanan' => 'required|in:menunggu,dikonfirmasi,check_in,check_out,dibatalkan',
            'status_pembayaran' => 'required|in:belum_dibayar,dibayar_sebagian,lunas,dikembalikan',
        ]);

        $pemesanan->update($validated);

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

    /**
     * DELETE /api/admin/pemesanan/{id}
     */
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

    /**
     * Generate kode pemesanan
     */
    private function generateKodePemesanan()
    {
        do {
            $kode = 'PSN-' . strtoupper(Str::random(8));
        } while (Pemesanan::where('kode_pemesanan', $kode)->exists());

        return $kode;
    }
}
