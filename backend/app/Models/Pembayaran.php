<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    public $timestamps = false;

    protected $fillable = [
        'pemesanan_id',
        'metode_pembayaran',
        'nomor_transaksi',
        'jumlah_bayar',
        'status',
        'waktu_bayar',
    ];

    protected $casts = [
        'waktu_bayar' => 'datetime',
    ];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id');
    }
}