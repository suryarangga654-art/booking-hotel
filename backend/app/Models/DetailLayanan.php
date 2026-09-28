<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailLayanan extends Model
{
    protected $table = 'detail_layanan';

    public $timestamps = false;

    protected $fillable = [
        'pemesanan_id',
        'layanan_tambahan_id',
        'jumlah',
        'total_harga',
    ];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id');
    }

    public function layananTambahan()
    {
        return $this->belongsTo(LayananTambahan::class, 'layanan_tambahan_id');
    }
}