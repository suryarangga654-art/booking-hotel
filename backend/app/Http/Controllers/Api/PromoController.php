<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromoController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'data' => Promo::orderBy('tanggal_mulai')->get()]);
    }

    public function validateCode(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:40',
            'subtotal' => 'nullable|numeric|min:0',
        ]);
        $promo = Promo::where('kode', strtoupper(trim($validated['kode'])))
            ->where('aktif', true)
            ->whereDate('tanggal_mulai', '<=', today())
            ->whereDate('tanggal_selesai', '>=', today())
            ->first();

        if (!$promo) {
            return response()->json(['success' => false, 'message' => 'Kode promo tidak aktif atau sudah kedaluwarsa.'], 422);
        }

        $subtotal = max(0, (float) $request->input('subtotal', 0));
        $discount = $promo->jenis_diskon === 'persen'
            ? $subtotal * min((float) $promo->nilai, 100) / 100
            : min($subtotal, (float) $promo->nilai);

        return response()->json(['success' => true, 'data' => $promo, 'discount' => round($discount, 2)]);
    }

    public function store(Request $request)
    {
        $promo = Promo::create($this->validatedData($request));
        return response()->json(['success' => true, 'data' => $promo], 201);
    }

    public function update(Request $request, int $id)
    {
        $promo = Promo::findOrFail($id);
        $promo->update($this->validatedData($request, $promo));
        return response()->json(['success' => true, 'data' => $promo]);
    }

    public function destroy(int $id)
    {
        Promo::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Promo dihapus.']);
    }

    private function validatedData(Request $request, ?Promo $promo = null): array
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:40', Rule::unique('promos', 'kode')->ignore($promo?->id)],
            'jenis_diskon' => 'required|in:persen,nominal',
            'nilai' => 'required|numeric|min:1',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'aktif' => 'required|boolean',
        ]);
        if ($validated['jenis_diskon'] === 'persen' && $validated['nilai'] > 100) {
            abort(422, 'Diskon persentase maksimal 100%.');
        }
        $validated['kode'] = strtoupper(trim($validated['kode']));
        return $validated;
    }
}