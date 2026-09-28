<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $table = 'promos';
    public $timestamps = false;

    protected $fillable = ['kode', 'jenis_diskon', 'nilai', 'tanggal_mulai', 'tanggal_selesai', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }
}