<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pemesanan', function (Blueprint $table) {
            $table->id();

          $table->unsignedInteger('pemesanan_id');
               

            $table->foreignId('kamar_id')
                ->constrained('kamar');

            $table->date('tanggal_check_in');
            $table->date('tanggal_check_out');
            $table->integer('harga_per_malam');
            $table->integer('jumlah_harga');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pemesanan');
    }
};