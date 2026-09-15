<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->increments('id');

            // Harus sama dengan pemesanan.id
            $table->unsignedInteger('pemesanan_id');

            $table->string('metode_pembayaran', 50);

            $table->unsignedInteger('nomor_transaksi')
                ->unique()
                ->nullable();

            $table->integer('jumlah_bayar');

            $table->enum('status', [
                'menunggu',
                'berhasil',
                'gagal',
                'kedaluwarsa'
            ])->default('menunggu');
            $table->string('bukti_transfer')->nullable();
            $table->timestamp('waktu_bayar')->nullable();

            // Foreign key
            $table->foreign('pemesanan_id')
                ->references('id')
                ->on('pemesanan')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};