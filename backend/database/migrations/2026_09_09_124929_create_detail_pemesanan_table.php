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
            $table->foreignId('pemesanan_id')->constrained('pemesanan')->onDelete('cascade');
            $table->foreignId('kamar_id')->constrained('kamar');
            $table->date('tanggal_check_in');
            $table->date('tanggal_check_out');
            $table->decimal('harga_per_malam', 12, 2);
            $table->decimal('jumlah_harga', 12, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pemesanan');
    }
};