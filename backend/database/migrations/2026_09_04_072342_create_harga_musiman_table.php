<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harga_musiman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipe_kamar_id')
                    ->constrained('tipe_kamar')
                    ->onDelete('cascade');

            $table->string('nama_harga');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('harga_per_malam');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harga_musiman');
    }
};