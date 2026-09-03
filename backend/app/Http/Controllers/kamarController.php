<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use Illuminate\Http\Request;

class KamarController extends Controller
{
    public function index()
    {
        $kamar = Kamar::all();

        return response()->json($kamar);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_kamar' => 'required',
            'tipe_kamar' => 'required',
            'harga' => 'required|integer',
            'kapasitas' => 'required|integer',
            'deskripsi' => 'nullable',
            'foto' => 'nullable',
            'status' => 'nullable',
        ]);

        $kamar = Kamar::create($request->all());

        return response()->json([
            'message' => 'Kamar berhasil ditambahkan',
            'data' => $kamar
        ], 201);
    }

    public function show(string $id)
    {
        $kamar = Kamar::find($id);

        if (!$kamar) {
            return response()->json([
                'message' => 'Kamar tidak ditemukan'
            ], 404);
        }

        return response()->json($kamar);
    }

    public function update(Request $request, string $id)
    {
        $kamar = Kamar::find($id);

        if (!$kamar) {
            return response()->json([
                'message' => 'Kamar tidak ditemukan'
            ], 404);
        }

        $kamar->update($request->all());

        return response()->json([
            'message' => 'Kamar berhasil diubah',
            'data' => $kamar
        ]);
    }

    public function destroy(string $id)
    {
        $kamar = Kamar::find($id);

        if (!$kamar) {
            return response()->json([
                'message' => 'Kamar tidak ditemukan'
            ], 404);
        }

        $kamar->delete();

        return response()->json([
            'message' => 'Kamar berhasil dihapus'
        ]);
    }
}