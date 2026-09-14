<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\DetailPemesanan;
use App\Models\Pembayaran;
use App\Models\Ulasan;

class Pemesanan extends Model
{
    protected $table = 'pemesanan';

    public $timestamps = false;

    protected $fillable = [
        'kode_pemesanan',
        'users_id',
        'jumlah_total',
        'status_pemesanan',
        'status_pembayaran',
    ];

    public function pengguna()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function detailPemesanan()
    {
        return $this->hasMany(DetailPemesanan::class, 'pemesanan_id');
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