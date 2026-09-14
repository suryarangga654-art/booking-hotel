<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LayananTambahan;
use Illuminate\Http\Request;

class LayananTambahanController extends Controller
{
    // GET semua layanan
    public function index()
    {
        $layanan = LayananTambahan::latest('id')->get();

        return response()->json([
            'success' => true,
            'data' => $layanan
        ]);
    }

    // GET layanan berdasarkan ID
    public function show($id)
    {
        $layanan = LayananTambahan::find($id);

        if (!$layanan) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan tambahan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $layanan
        ]);
    }

    // POST tambah layanan
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'harga' => 'required|integer',
            'satuan' => 'required|string|max:50',
        ]);

        $layanan = LayananTambahan::create([
            'nama' => $request->nama,
            'harga' => $request->harga,
            'satuan' => $request->satuan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Layanan tambahan berhasil ditambahkan',
            'data' => $layanan
        ], 201);
    }

    // PUT ubah layanan
    public function update(Request $request, $id)
    {
        $layanan = LayananTambahan::find($id);

        if (!$layanan) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan tambahan tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'nama' => 'required|string|max:100',
            'harga' => 'required|integer',
            'satuan' => 'required|string|max:50',
        ]);

        $layanan->update([
            'nama' => $request->nama,
            'harga' => $request->harga,
            'satuan' => $request->satuan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Layanan tambahan berhasil diubah',
            'data' => $layanan
        ]);
    }

    // DELETE layanan
    public function destroy($id)
    {
        $layanan = LayananTambahan::find($id);

        if (!$layanan) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan tambahan tidak ditemukan'
            ], 404);
        }

        $layanan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Layanan tambahan berhasil dihapus'
        ]);
    }
}