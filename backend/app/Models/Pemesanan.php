<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan';
    public $timestamps = false;

    protected $fillable = [
        'kode_pemesanan',
        'pengguna_id',
        'jumlah_total',
        'status_pemesanan',
        'status_pembayaran',
        'promo_id',
        'nilai_diskon',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    public function detailPemesanan()
    {
        return $this->hasMany(DetailPemesanan::class, 'pemesanan_id');
    }

    public function detailLayanan()
    {
        return $this->hasMany(DetailLayanan::class, 'pemesanan_id');
    }

    public function layananTambahan()
    {
        return $this->belongsToMany(LayananTambahan::class, 'detail_layanan', 'pemesanan_id', 'layanan_tambahan_id')
                    ->withPivot('jumlah', 'total_harga');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'pemesanan_id');
    }

    public function ulasan()
    {
        return $this->hasOne(Ulasan::class, 'pemesanan_id');
    }
}