<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use App\Models\Kamar;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /api/admin/stats
     * Mengambil ringkasan statistik untuk dashboard admin.
     */
    public function stats(Request $request)
    {
        // Menghitung data statistik dasar sistem hotel
        $totalPemesanan = Pemesanan::count();
        $totalKamar = Kamar::count();
        $totalTamu = User::where('role', 'tamu')->count(); // Sesuaikan jika kolom role Anda berbeda
        
        // Contoh menghitung total pendapatan dari pemesanan yang status pembayarannya lunas
        $totalPendapatan = Pemesanan::where('status_pembayaran', 'lunas')->sum('jumlah_total');

        return response()->json([
            'success' => true,
            'message' => 'Statistik dashboard berhasil diambil',
            'data' => [
                'total_pemesanan' => $totalPemesanan,
                'total_kamar' => $totalKamar,
                'total_tamu' => $totalTamu,
                'total_pendapatan' => $totalPendapatan,
            ]
        ]);
    }
}