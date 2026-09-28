<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPemesanan extends Model
{
    protected $table = 'detail_pemesanan';

    public $timestamps = false;

    protected $fillable = [
        'pemesanan_id',
        'kamar_id',
        'tanggal_check_in',
        'tanggal_check_out',
        'harga_per_malam',
        'jumlah_harga',
    ];

    protected $casts = [
        'tanggal_check_in' => 'date',
        'tanggal_check_out' => 'date',
    ];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id');
    }

    public function kamar()
    {
        return $this->belongsTo(Kamar::class, 'kamar_id');
    }
}