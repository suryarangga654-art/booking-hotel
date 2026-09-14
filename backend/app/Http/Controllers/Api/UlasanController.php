<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ulasan;
use Illuminate\Http\Request;

class UlasanController extends Controller
{
    // GET semua ulasan
    public function index()
    {
        $ulasan = Ulasan::with(['pemesanan', 'pengguna'])
            ->latest('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $ulasan
        ]);
    }

    // GET ulasan berdasarkan ID
    public function show($id)
    {
        $ulasan = Ulasan::with(['pemesanan', 'pengguna'])
            ->find($id);

        if (!$ulasan) {
            return response()->json([
                'success' => false,
                'message' => 'Ulasan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ulasan
        ]);
    }

    // POST tambah ulasan
    public function store(Request $request)
    {
        $request->validate([
            'pemesanan_id' => 'required|exists:pemesanan,id',
            'pengguna_id' => 'required|exists:users,id',
            'penilaian' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string',
        ]);

        $ulasan = Ulasan::create([
            'pemesanan_id' => $request->pemesanan_id,
            'pengguna_id' => $request->pengguna_id,
            'penilaian' => $request->penilaian,
            'komentar' => $request->komentar,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ulasan berhasil ditambahkan',
            'data' => $ulasan
        ], 201);
    }

    // PUT ubah ulasan
    public function update(Request $request, $id)
    {
        $ulasan = Ulasan::find($id);

        if (!$ulasan) {
            return response()->json([
                'success' => false,
                'message' => 'Ulasan tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'pemesanan_id' => 'required|exists:pemesanan,id',
            'pengguna_id' => 'required|exists:users,id',
            'penilaian' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string',
        ]);

        $ulasan->update([
            'pemesanan_id' => $request->pemesanan_id,
            'pengguna_id' => $request->pengguna_id,
            'penilaian' => $request->penilaian,
            'komentar' => $request->komentar,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ulasan berhasil diubah',
            'data' => $ulasan
        ]);
    }

    // DELETE ulasan
    public function destroy($id)
    {
        $ulasan = Ulasan::find($id);

        if (!$ulasan) {
            return response()->json([
                'success' => false,
                'message' => 'Ulasan tidak ditemukan'
            ], 404);
        }

        $ulasan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ulasan berhasil dihapus'
        ]);
    }
}