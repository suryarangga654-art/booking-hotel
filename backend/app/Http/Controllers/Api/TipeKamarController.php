<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TipeKamar;
use App\Models\FotoTipeKamar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TipeKamarController extends Controller
{
    // PUBLIC: List tipe kamar + filter kapasitas / harga
    public function index(Request $request)
    {
        $query = TipeKamar::with([
            'foto',
            'kamar' => fn ($kamar) => $kamar->where('status', 'tersedia')->limit(1),
        ])->withCount('kamar')->withCount([
            'kamar as kamar_tersedia_count' => fn ($kamar) => $kamar->where('status', 'tersedia'),
        ]);

        if ($request->has('kapasitas')) {
            $query->where('kapasitas', '>=', $request->kapasitas);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    // PUBLIC: Detail 1 Tipe Kamar
    public function show($id)
    {
        $tipeKamar = TipeKamar::with(['foto', 'kamar'])->withCount([
            'kamar as kamar_tersedia_count' => fn ($kamar) => $kamar->where('status', 'tersedia'),
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $tipeKamar
        ]);
    }

    // ADMIN PANEL: Tambah Tipe Kamar
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'harga_dasar' => 'required|numeric|min:0',
            'kapasitas' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
        ]);

        $tipeKamar = TipeKamar::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tipe kamar berhasil ditambahkan',
            'data' => $tipeKamar
        ], 201);
    }

    // ADMIN PANEL: Update Tipe Kamar
    public function update(Request $request, $id)
    {
        $tipeKamar = TipeKamar::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'harga_dasar' => 'sometimes|required|numeric|min:0',
            'kapasitas' => 'sometimes|required|integer|min:1',
            'deskripsi' => 'nullable|string',
        ]);

        $tipeKamar->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tipe kamar berhasil diperbarui',
            'data' => $tipeKamar
        ]);
    }

    public function storePhotos(Request $request, $id)
    {
        $validated = $request->validate([
            'fotos' => 'required|array|min:1|max:8',
            'fotos.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $tipeKamar = TipeKamar::findOrFail($id);
        if ($tipeKamar->foto()->count() + count($validated['fotos']) > 8) {
            return response()->json([
                'success' => false,
                'message' => 'Maksimal 8 foto untuk satu tipe kamar.',
                'errors' => ['fotos' => ['Hapus foto lama atau pilih lebih sedikit file.']],
            ], 422);
        }

        foreach ($validated['fotos'] as $file) {
            $path = $file->store('tipe-kamar', 'public');
            $tipeKamar->foto()->create(['path' => $path]);
        }

        return response()->json([
            'success' => true,
            'data' => $tipeKamar->load('foto'),
        ], 201);
    }

    public function destroyPhoto($id, $fotoId)
    {
        $foto = FotoTipeKamar::where('tipe_kamar_id', $id)->findOrFail($fotoId);
        Storage::disk('public')->delete($foto->path);
        $foto->delete();

        return response()->json(['success' => true, 'message' => 'Foto kamar dihapus.']);
    }

    // ADMIN PANEL: Hapus Tipe Kamar
    public function destroy($id)
    {
        $tipeKamar = TipeKamar::findOrFail($id);

        if ($tipeKamar->kamar()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Tipe kamar masih memiliki unit kamar dan tidak dapat dihapus.',
            ], 422);
        }

        foreach ($tipeKamar->foto as $foto) {
            Storage::disk('public')->delete($foto->path);
        }

        $tipeKamar->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tipe kamar berhasil dihapus'
        ]);
    }
}