<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_id')->constrained('pemesanan')->onDelete('cascade');
            $table->foreignId('layanan_tambahan_id')->constrained('layanan_tambahan');
            $table->integer('jumlah')->default(1);
            $table->decimal('total_harga', 12, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_layanan');
    }
};