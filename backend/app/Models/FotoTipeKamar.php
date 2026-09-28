<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FotoTipeKamar extends Model
{
    protected $table = 'foto_tipe_kamar';
    public $timestamps = false;

    protected $fillable = ['tipe_kamar_id', 'path'];

    public function tipeKamar()
    {
        return $this->belongsTo(TipeKamar::class, 'tipe_kamar_id');
    }
}