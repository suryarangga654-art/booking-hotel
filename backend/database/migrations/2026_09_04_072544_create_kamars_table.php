<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kamar', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('tipe_kamar_id');

            $table->string('nomor_kamar')->unique();
            $table->integer('lantai');

            $table->enum('status', [
                'tersedia',
                'terisi',
                'perbaikan',
                'dibersihkan'
            ])->default('tersedia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamar');
    }
};