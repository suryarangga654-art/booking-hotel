<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
=======

use Illuminate\Database\Eloquent\Model;

class LayananTambahan extends Model
{

    use HasFactory;

    protected $table = 'layanan_tambahan';
=======
    protected $table = 'layanan_tambahan';


    public $timestamps = false;

    protected $fillable = [
        'nama',
        'harga',
        'satuan',
    ];


    public function detailLayanan()
    {
        return $this->hasMany(DetailLayanan::class, 'layanan_tambahan_id');
    }
=======

}