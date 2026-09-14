<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailLayanan;
use Illuminate\Http\Request;

class DetailLayananController extends Controller
{
    public function index()
    {
        $detailLayanan = DetailLayanan::with([
            'pemesanan',
            'layananTambahan'
        ])->latest('id')->get();

        return response()->json([
            'success' => true,
            'data' => $detailLayanan
        ]);
    }

    public function show($id)
    {
        $detailLayanan = DetailLayanan::with([
            'pemesanan',
            'layananTambahan'
        ])->find($id);

        if (!$detailLayanan) {
            return response()->json([
                'success' => false,
                'message' => 'Detail layanan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $detailLayanan
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pemesanan_id' => 'required|exists:pemesanan,id',
            'layanan_tambahan_id' => 'required|exists:layanan_tambahan,id',
            'jumlah' => 'required|integer|min:1',
            'total_harga' => 'required|integer|min:0',
        ]);

        $detailLayanan = DetailLayanan::create([
            'pemesanan_id' => $request->pemesanan_id,
            'layanan_tambahan_id' => $request->layanan_tambahan_id,
            'jumlah' => $request->jumlah,
            'total_harga' => $request->total_harga,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail layanan berhasil ditambahkan',
            'data' => $detailLayanan
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $detailLayanan = DetailLayanan::find($id);

        if (!$detailLayanan) {
            return response()->json([
                'success' => false,
                'message' => 'Detail layanan tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'pemesanan_id' => 'required|exists:pemesanan,id',
            'layanan_tambahan_id' => 'required|exists:layanan_tambahan,id',
            'jumlah' => 'required|integer|min:1',
            'total_harga' => 'required|integer|min:0',
        ]);

        $detailLayanan->update([
            'pemesanan_id' => $request->pemesanan_id,
            'layanan_tambahan_id' => $request->layanan_tambahan_id,
            'jumlah' => $request->jumlah,
            'total_harga' => $request->total_harga,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail layanan berhasil diubah',
            'data' => $detailLayanan
        ]);
    }

    public function destroy($id)
    {
        $detailLayanan = DetailLayanan::find($id);

        if (!$detailLayanan) {
            return response()->json([
                'success' => false,
                'message' => 'Detail layanan tidak ditemukan'
            ], 404);
        }

        $detailLayanan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Detail layanan berhasil dihapus'
        ]);
    }
}