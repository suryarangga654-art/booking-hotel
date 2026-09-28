<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ulasan extends Model
{
    use HasFactory;

    protected $table = 'ulasan';
    public $timestamps = false;

    protected $fillable = [
        'pemesanan_id',
        'pengguna_id',
        'penilaian',
        'komentar',
    ];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
}