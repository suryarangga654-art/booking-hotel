<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\DetailPemesanan;
use Illuminate\Http\Request;

class DetailPemesananController extends Controller
{
    // Menampilkan semua detail pemesanan
    public function index()
    {
        $detailPemesanan = DetailPemesanan::with([
            'pemesanan',
            'kamar'
        ])->latest('id')->get();

        return response()->json([
            'success' => true,
            'data' => $detailPemesanan
        ]);
    }

    // Menampilkan satu detail pemesanan
    public function show($id)
    {
        $detailPemesanan = DetailPemesanan::with([
            'pemesanan',
            'kamar'
        ])->find($id);

        if (!$detailPemesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Detail pemesanan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $detailPemesanan
        ]);
    }

    // Menambahkan detail pemesanan
    public function store(Request $request)
    {
        $request->validate([
            'pemesanan_id' => 'required|exists:pemesanan,id',
            'kamar_id' => 'required|exists:kamar,id',
            'tanggal_check_in' => 'required|date',
            'tanggal_check_out' => 'required|date|after:tanggal_check_in',
            'harga_per_malam' => 'required|integer',
            'jumlah_harga' => 'required|integer',
        ]);

        $detailPemesanan = DetailPemesanan::create([
            'pemesanan_id' => $request->pemesanan_id,
            'kamar_id' => $request->kamar_id,
            'tanggal_check_in' => $request->tanggal_check_in,
            'tanggal_check_out' => $request->tanggal_check_out,
            'harga_per_malam' => $request->harga_per_malam,
            'jumlah_harga' => $request->jumlah_harga,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail pemesanan berhasil ditambahkan',
            'data' => $detailPemesanan
        ], 201);
    }

    // Mengubah detail pemesanan
    public function update(Request $request, $id)
    {
        $detailPemesanan = DetailPemesanan::find($id);

        if (!$detailPemesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Detail pemesanan tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'pemesanan_id' => 'required|exists:pemesanan,id',
            'kamar_id' => 'required|exists:kamar,id',
            'tanggal_check_in' => 'required|date',
            'tanggal_check_out' => 'required|date|after:tanggal_check_in',
            'harga_per_malam' => 'required|integer',
            'jumlah_harga' => 'required|integer',
        ]);

        $detailPemesanan->update([
            'pemesanan_id' => $request->pemesanan_id,
            'kamar_id' => $request->kamar_id,
            'tanggal_check_in' => $request->tanggal_check_in,
            'tanggal_check_out' => $request->tanggal_check_out,
            'harga_per_malam' => $request->harga_per_malam,
            'jumlah_harga' => $request->jumlah_harga,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail pemesanan berhasil diubah',
            'data' => $detailPemesanan
        ]);
    }

    // Menghapus detail pemesanan
    public function destroy($id)
    {
        $detailPemesanan = DetailPemesanan::find($id);

        if (!$detailPemesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Detail pemesanan tidak ditemukan'
            ], 404);
        }

        $detailPemesanan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Detail pemesanan berhasil dihapus'
        ]);
    }
}