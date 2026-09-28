<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayananTambahan extends Model
{
    protected $table = 'layanan_tambahan';

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'harga',
        'satuan',
    ];
}