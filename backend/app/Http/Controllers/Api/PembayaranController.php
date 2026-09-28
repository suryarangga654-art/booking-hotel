<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PembayaranController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Pembayaran::with([
                'pemesanan.user',
                'pemesanan.detailPemesanan.kamar.tipeKamar',
            ])->latest('id')->get(),
        ]);
    }

    public function confirm(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:diterima,ditolak',
        ]);

        $result = DB::transaction(function () use ($id, $validated) {
            $payment = Pembayaran::with('pemesanan')->lockForUpdate()->findOrFail($id);
            if ($payment->status !== 'menunggu') {
                throw ValidationException::withMessages([
                    'status' => ['Pembayaran ini sudah diproses.'],
                ]);
            }

            $accepted = $validated['status'] === 'diterima';
            $payment->update([
                'status' => $accepted ? 'berhasil' : 'gagal',
                'waktu_bayar' => $accepted ? now() : $payment->waktu_bayar,
            ]);
            $payment->pemesanan->update([
                'status_pemesanan' => $accepted ? 'dikonfirmasi' : 'dibatalkan',
                'status_pembayaran' => $accepted ? 'lunas' : 'belum_dibayar',
            ]);

            return $payment->fresh()->load([
                'pemesanan.user',
                'pemesanan.detailPemesanan.kamar.tipeKamar',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => $validated['status'] === 'diterima'
                ? 'Pembayaran berhasil diverifikasi.'
                : 'Pembayaran ditolak.',
            'data' => $result,
        ]);
    }
}