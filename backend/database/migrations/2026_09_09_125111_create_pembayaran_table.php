<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_id')->constrained('pemesanan')->onDelete('cascade');
            $table->string('metode_pembayaran', 50);
            $table->string('nomor_transaksi', 100)->nullable()->unique();
            $table->decimal('jumlah_bayar', 12, 2);
            $table->enum('status', [
                'menunggu',
                'berhasil',
                'gagal',
                'kedaluwarsa'
            ])->default('menunggu');
            $table->timestamp('waktu_bayar')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};