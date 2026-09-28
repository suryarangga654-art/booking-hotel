<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KamarController extends Controller
{
    // ADMIN / RESEPSIONIS: List semua kamar beserta tipe kamarnya
    public function index(Request $request)
    {
        $query = Kamar::with('tipeKamar');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    // ADMIN: Tambah Kamar Fisik Baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipe_kamar_id' => 'required|exists:tipe_kamar,id',
            'nomor_kamar' => ['required', 'string', 'max:20', Rule::unique('kamar', 'nomor_kamar')],
            'lantai' => 'required|integer|min:1',
            'status' => 'required|in:tersedia,terisi,perbaikan,dibersihkan',
        ], [
            'nomor_kamar.unique' => 'Nomor kamar tersebut sudah digunakan.',
        ]);

        $kamar = Kamar::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kamar berhasil ditambahkan',
            'data' => $kamar
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $kamar = Kamar::findOrFail($id);
        $validated = $request->validate([
            'tipe_kamar_id' => 'sometimes|required|exists:tipe_kamar,id',
            'nomor_kamar' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('kamar', 'nomor_kamar')->ignore($kamar->id)],
            'lantai' => 'sometimes|required|integer|min:1',
            'status' => 'sometimes|required|in:tersedia,terisi,perbaikan,dibersihkan',
        ], [
            'nomor_kamar.unique' => 'Nomor kamar tersebut sudah digunakan.',
        ]);

        $kamar->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kamar berhasil diperbarui',
            'data' => $kamar->load('tipeKamar'),
        ]);
    }

    // ADMIN / RESEPSIONIS / PETUGAS KEBERSIHAN: Update Status Kamar
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:tersedia,terisi,perbaikan,dibersihkan',
        ]);

        $kamar = Kamar::findOrFail($id);
        $kamar->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status kamar berhasil diperbarui',
            'data' => $kamar
        ]);
    }

    // ADMIN: Hapus Kamar
    public function destroy($id)
    {
        $kamar = Kamar::findOrFail($id);

        if ($kamar->detailPemesanan()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kamar sudah memiliki riwayat pemesanan dan tidak dapat dihapus.',
            ], 422);
        }

        $kamar->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kamar berhasil dihapus'
        ]);
    }
}