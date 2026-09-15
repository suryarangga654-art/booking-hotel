<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan';

    public $timestamps = false;

    protected $fillable = [
        'kode_pemesanan',
        'users_id',
        'jumlah_total',
        'status_pemesanan',
        'status_pembayaran',
    ];

    protected $casts = [
        'jumlah_total' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Relasi ke user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    /**
     * Relasi ke pembayaran
     */
    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'pemesanan_id');
    }
}
