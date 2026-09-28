<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipeKamar extends Model
{
    use HasFactory;

    protected $table = 'tipe_kamar';
    public $timestamps = false; // Hanya ada created_at di DDL

    protected $fillable = [
        'nama',
        'harga_dasar',
        'kapasitas',
        'deskripsi',
    ];

    public function hargaMusiman()
    {
        return $this->hasMany(HargaMusiman::class, 'tipe_kamar_id');
    }

    public function kamar()
    {
        return $this->hasMany(Kamar::class, 'tipe_kamar_id');
    }

    public function foto()
    {
        return $this->hasMany(FotoTipeKamar::class, 'tipe_kamar_id');
    }
}