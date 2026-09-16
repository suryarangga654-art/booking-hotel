<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HargaMusiman extends Model
{
    protected $table = 'harga_musiman';

    public $timestamps = false;

    protected $fillable = [
        'tipe_kamar_id',
        'nama_harga',
        'tanggal_mulai',
        'tanggal_selesai',
        'harga_per_malam',
    ];

    public function tipeKamar()
    {
        return $this->belongsTo(TipeKamar::class, 'tipe_kamar_id');
    }
}