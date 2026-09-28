<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LayananTambahan;
use Illuminate\Http\Request;

class LayananTambahanController extends Controller
{
    // PUBLIC / TAMU / ADMIN
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => LayananTambahan::all()
        ]);
    }

    // ADMIN
    public function store(Request $request)
    {
        $layanan = LayananTambahan::create($this->validatedData($request));

        return response()->json([
            'success' => true,
            'data' => $layanan
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $layanan = LayananTambahan::findOrFail($id);
        $layanan->update($this->validatedData($request));
        return response()->json(['success' => true, 'data' => $layanan]);
    }

    // ADMIN
    public function destroy($id)
    {
        $layanan = LayananTambahan::findOrFail($id);
        if ($layanan->detailLayanan()->exists()) {
            return response()->json(['success' => false, 'message' => 'Layanan yang sudah dipakai pada pemesanan tidak dapat dihapus.'], 422);
        }
        $layanan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil dihapus'
        ]);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'nama' => 'required|string|max:100',
            'harga' => 'required|numeric|min:0',
            'satuan' => 'required|in:per_tamu,per_kamar,per_hari,sekali_pakai',
        ]);
    }
}