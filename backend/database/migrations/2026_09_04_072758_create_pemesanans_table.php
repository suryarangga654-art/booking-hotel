<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemesanan', function (Blueprint $table) {
            $table->increments('id');

            $table->string('kode_pemesanan')->unique();

            $table->foreignId('users_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->integer('jumlah_total');

            $table->enum('status_pemesanan', [
                'menunggu',
                'dikonfirmasi',
                'check_in',
                'check_out',
                'dibatalkan'
            ])->default('menunggu');

            $table->enum('status_pembayaran', [
                'belum_dibayar',
                'dibayar_sebagian',
                'lunas',
                'dikembalikan'
            ])->default('belum_dibayar');

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan');
    }
};