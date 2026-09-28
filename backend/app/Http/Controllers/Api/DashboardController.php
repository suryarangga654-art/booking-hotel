<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use App\Models\Kamar;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        // 1. STATISTIK KEUANGAN & PEMESANAN
        // Total Pendapatan Keseluruhan (Pemesanan yang Lunas / Selesai)
        $totalPendapatan = Pemesanan::whereIn('status_pemesanan', ['check_in', 'check_out'])
            ->sum('jumlah_total');

        // Pendapatan Bulan Ini
        $pendapatanBulanIni = Pemesanan::whereIn('status_pemesanan', ['check_in', 'check_out'])
            ->whereMonth('created_at', $thisMonth)
            ->whereYear('created_at', $thisYear)
            ->sum('jumlah_total');

        // Total Pemesanan Keseluruhan
        $totalPemesanan = Pemesanan::count();
        $pemesananHariIni = Pemesanan::whereDate('created_at', $today)->count();

        // Pemesanan Baru (Status Pending)
        $pemesananPending = Pemesanan::where('status_pemesanan', 'menunggu')->count();


        // 2. STATUS KAMAR FISIK REAL-TIME
        $totalKamar = Kamar::count();
        $kamarTersedia = Kamar::where('status', 'tersedia')->count();
        $kamarTerisi = Kamar::where('status', 'terisi')->count();
        $kamarKotor = Kamar::where('status', 'dibersihkan')->count();
        $kamarPemeliharaan = Kamar::where('status', 'perbaikan')->count();


        // 3. TAMU & CHECK-IN / CHECK-OUT HARI INI
        $checkInHariIni = Pemesanan::whereHas('detailPemesanan', fn ($query) => $query->whereDate('tanggal_check_in', $today))
            ->where('status_pemesanan', 'check_in')
            ->count();

        $checkOutHariIni = Pemesanan::whereHas('detailPemesanan', fn ($query) => $query->whereDate('tanggal_check_out', $today))
            ->where('status_pemesanan', 'check_out')
            ->count();

        $totalTamuTerdaftar = User::where('peran', 'tamu')->count();


        // 4. PEMESANAN TERBARU (5 Transaksi Terakhir)
        $pemesananTerbaru = Pemesanan::with(['user', 'detailPemesanan.kamar.tipeKamar'])
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ringkasan' => [
                    'total_pendapatan' => (float) $totalPendapatan,
                    'pendapatan_bulan_ini' => (float) $pendapatanBulanIni,
                    'total_pemesanan' => $totalPemesanan,
                    'total_pemesanan_hari_ini' => $pemesananHariIni,
                    'pemesanan_pending' => $pemesananPending,
                    'total_tamu' => $totalTamuTerdaftar,
                    'check_in_hari_ini' => $checkInHariIni,
                    'check_out_hari_ini' => $checkOutHariIni,
                ],
                'status_kamar' => [
                    'total' => $totalKamar,
                    'tersedia' => $kamarTersedia,
                    'terisi' => $kamarTerisi,
                    'kotor' => $kamarKotor,
                    'pemeliharaan' => $kamarPemeliharaan,
                ],
                'pemesanan_terbaru' => $pemesananTerbaru
            ]
        ]);
    }
}