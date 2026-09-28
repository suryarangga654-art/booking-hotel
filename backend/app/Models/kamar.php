<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    protected $table = 'kamar';

    public $timestamps = false;

    protected $fillable = [
        'tipe_kamar_id',
        'nomor_kamar',
        'lantai',
        'status',
    ];

    public function tipeKamar()
    {
        return $this->belongsTo(TipeKamar::class, 'tipe_kamar_id');
    }
}