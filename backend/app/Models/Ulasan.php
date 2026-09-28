<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ulasan extends Model
{
    use HasFactory;

    protected $table = 'ulasan';
=======
use Illuminate\Database\Eloquent\Model;
use App\Models\Pemesanan;
use App\Models\User;

class Ulasan extends Model
{
    protected $table = 'ulasan';

    public $timestamps = false;

    protected $fillable = [
        'pemesanan_id',
        'pengguna_id',
        'penilaian',
        'komentar',
    ];


=======
    protected $casts = [
        'penilaian' => 'integer',
        'created_at' => 'datetime',
    ];


    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id');
    }

    public function user()
=======
    public function pengguna()

    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
}