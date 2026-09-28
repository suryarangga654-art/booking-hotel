<?php

namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;
use App\Models\HargaMusiman;
use Illuminate\Http\Request;

class HargaMusimanController extends Controller
{
    // Menampilkan semua harga musiman
    public function index()
    {
        $hargaMusiman = HargaMusiman::with('tipeKamar')->latest('id')->get();

        return response()->json([
            'success' => true,
            'data' => $hargaMusiman
        ]);
    }

    // Menampilkan satu harga musiman
    public function show($id)
    {
        $hargaMusiman = HargaMusiman::with('tipeKamar')->find($id);

        if (!$hargaMusiman) {
            return response()->json([
                'success' => false,
                'message' => 'Harga musiman tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $hargaMusiman
        ]);
    }

    // Menambahkan harga musiman
    public function store(Request $request)
    {
        $request->validate([
            'tipe_kamar_id' => 'required|exists:tipe_kamar,id',
            'nama_harga' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'harga_per_malam' => 'required|integer',
        ]);

        $hargaMusiman = HargaMusiman::create([
            'tipe_kamar_id' => $request->tipe_kamar_id,
            'nama_harga' => $request->nama_harga,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'harga_per_malam' => $request->harga_per_malam,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Harga musiman berhasil ditambahkan',
            'data' => $hargaMusiman
        ], 201);
    }

    // Mengubah harga musiman
    public function update(Request $request, $id)
    {
        $hargaMusiman = HargaMusiman::find($id);

        if (!$hargaMusiman) {
            return response()->json([
                'success' => false,
                'message' => 'Harga musiman tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'tipe_kamar_id' => 'required|exists:tipe_kamar,id',
            'nama_harga' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'harga_per_malam' => 'required|integer',
        ]);

        $hargaMusiman->update([
            'tipe_kamar_id' => $request->tipe_kamar_id,
            'nama_harga' => $request->nama_harga,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'harga_per_malam' => $request->harga_per_malam,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Harga musiman berhasil diubah',
            'data' => $hargaMusiman
        ]);
    }

    // Menghapus harga musiman
    public function destroy($id)
    {
        $hargaMusiman = HargaMusiman::find($id);

        if (!$hargaMusiman) {
            return response()->json([
                'success' => false,
                'message' => 'Harga musiman tidak ditemukan'
            ], 404);
        }

        $hargaMusiman->delete();

        return response()->json([
            'success' => true,
            'message' => 'Harga musiman berhasil dihapus'
        ]);
    }
}