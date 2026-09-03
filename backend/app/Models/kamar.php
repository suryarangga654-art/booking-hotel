<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    protected $table = 'kamars';

    protected $fillable = [
        'nomor_kamar',
        'tipe_kamar',
        'harga',
        'kapasitas',
        'deskripsi',
        'foto',
        'status',
    ];

    public function pemesanans()
    {
        return $this->hasMany(Pemesanan::class);
    }
    
}
