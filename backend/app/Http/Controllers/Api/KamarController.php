<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KamarController extends Controller
{
    // Menampilkan semua kamar
    public function index()
    {
        $kamar = Kamar::with('tipeKamar')->latest('id')->get();

        return response()->json([
            'success' => true,
            'data' => $kamar
        ]);
    }

    // Menampilkan satu kamar
    public function show($id)
    {
        $kamar = Kamar::with('tipeKamar')->find($id);

        if (!$kamar) {
            return response()->json([
                'success' => false,
                'message' => 'Kamar tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $kamar
        ]);
    }

    // Menambahkan kamar
    public function store(Request $request)
    {
        $request->validate([
            'tipe_kamar_id' => 'required|exists:tipe_kamar,id',
            'nomor_kamar' => 'required|string|max:255|unique:kamar,nomor_kamar',
            'lantai' => 'required|integer',
            'status' => 'nullable|in:tersedia,terisi,perbaikan,dibersihkan',
        ]);

        $kamar = Kamar::create([
            'tipe_kamar_id' => $request->tipe_kamar_id,
            'nomor_kamar' => $request->nomor_kamar,
            'lantai' => $request->lantai,
            'status' => $request->status ?? 'tersedia',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kamar berhasil ditambahkan',
            'data' => $kamar
        ], 201);
    }

    // Mengubah kamar
    public function update(Request $request, $id)
    {
        $kamar = Kamar::find($id);

        if (!$kamar) {
            return response()->json([
                'success' => false,
                'message' => 'Kamar tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'tipe_kamar_id' => 'required|exists:tipe_kamar,id',
            'nomor_kamar' => 'required|string|max:255|unique:kamar,nomor_kamar,' . $id,
            'lantai' => 'required|integer',
            'status' => 'required|in:tersedia,terisi,perbaikan,dibersihkan',
        ]);

        $kamar->update([
            'tipe_kamar_id' => $request->tipe_kamar_id,
            'nomor_kamar' => $request->nomor_kamar,
            'lantai' => $request->lantai,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kamar berhasil diubah',
            'data' => $kamar
        ]);
    }

    // Menghapus kamar
    public function destroy($id)
    {
        $kamar = Kamar::find($id);

        if (!$kamar) {
            return response()->json([
                'success' => false,
                'message' => 'Kamar tidak ditemukan'
            ], 404);
        }

        $kamar->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kamar berhasil dihapus'
        ]);
    }
}