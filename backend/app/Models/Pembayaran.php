<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';
    public $timestamps = false;

    protected $fillable = [
        'pemesanan_id',
        'metode_pembayaran',
        'bukti_transfer',
        'nomor_transaksi',
        'jumlah_bayar',
        'status',
        'waktu_bayar',
    ];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id');
    }
}