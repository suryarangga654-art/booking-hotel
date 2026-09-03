<?php

namespace App\Http\Controllers;

use App\Models\Pemesanan;
use Illuminate\Http\Request;

class PemesananController extends Controller
{
    public function index()
    {
        $pemesanan = Pemesanan::with(['user', 'kamar'])->get();

        return response()->json($pemesanan);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'kamar_id' => 'required|exists:kamar,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'jumlah_tamu' => 'required|integer',
            'total_harga' => 'required|integer',
            'status' => 'nullable',
        ]);

        $pemesanan = Pemesanan::create($request->all());

        return response()->json([
            'message' => 'Pemesanan berhasil dibuat',
            'data' => $pemesanan
        ], 201);
    }

    public function show(string $id)
    {
        $pemesanan = Pemesanan::with(['user', 'kamar'])->find($id);

        if (!$pemesanan) {
            return response()->json([
                'message' => 'Pemesanan tidak ditemukan'
            ], 404);
        }

        return response()->json($pemesanan);
    }

    public function update(Request $request, string $id)
    {
        $pemesanan = Pemesanan::find($id);

        if (!$pemesanan) {
            return response()->json([
                'message' => 'Pemesanan tidak ditemukan'
            ], 404);
        }

        $pemesanan->update($request->all());

        return response()->json([
            'message' => 'Pemesanan berhasil diubah',
            'data' => $pemesanan
        ]);
    }

    public function destroy(string $id)
    {
        $pemesanan = Pemesanan::find($id);

        if (!$pemesanan) {
            return response()->json([
                'message' => 'Pemesanan tidak ditemukan'
            ], 404);
        }

        $pemesanan->delete();

        return response()->json([
            'message' => 'Pemesanan berhasil dihapus'
        ]);
    }
}