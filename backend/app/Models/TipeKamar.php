<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipeKamar extends Model
{
    protected $table = 'tipe_kamar';

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'harga_dasar',
        'kapasitas',
        'deskripsi',
    ];
}