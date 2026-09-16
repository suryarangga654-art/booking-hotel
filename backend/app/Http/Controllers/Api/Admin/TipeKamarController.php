<?php


namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;
use App\Models\TipeKamar;
use Illuminate\Http\Request;

class TipeKamarController extends Controller
{
    // Menampilkan semua tipe kamar
    public function index()
    {
        $tipeKamar = TipeKamar::latest('id')->get();

        return response()->json([
            'success' => true,
            'data' => $tipeKamar
        ]);
    }

    // Menampilkan satu tipe kamar
    public function show($id)
    {
        $tipeKamar = TipeKamar::find($id);

        if (!$tipeKamar) {
            return response()->json([
                'success' => false,
                'message' => 'Tipe kamar tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tipeKamar
        ]);
    }

    // Menambahkan tipe kamar
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'harga_dasar' => 'required|integer',
            'kapasitas' => 'required|integer',
            'deskripsi' => 'nullable|string',
        ]);

        $tipeKamar = TipeKamar::create([
            'nama' => $request->nama,
            'harga_dasar' => $request->harga_dasar,
            'kapasitas' => $request->kapasitas,
            'deskripsi' => $request->deskripsi,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tipe kamar berhasil ditambahkan',
            'data' => $tipeKamar
        ], 201);
    }

    // Mengubah tipe kamar
    public function update(Request $request, $id)
    {
        $tipeKamar = TipeKamar::find($id);

        if (!$tipeKamar) {
            return response()->json([
                'success' => false,
                'message' => 'Tipe kamar tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'nama' => 'required|string|max:100',
            'harga_dasar' => 'required|integer',
            'kapasitas' => 'required|integer',
            'deskripsi' => 'nullable|string',
        ]);

        $tipeKamar->update([
            'nama' => $request->nama,
            'harga_dasar' => $request->harga_dasar,
            'kapasitas' => $request->kapasitas,
            'deskripsi' => $request->deskripsi,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tipe kamar berhasil diubah',
            'data' => $tipeKamar
        ]);
    }

    // Menghapus tipe kamar
    public function destroy($id)
    {
        $tipeKamar = TipeKamar::find($id);

        if (!$tipeKamar) {
            return response()->json([
                'success' => false,
                'message' => 'Tipe kamar tidak ditemukan'
            ], 404);
        }

        $tipeKamar->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tipe kamar berhasil dihapus'
        ]);
    }
}