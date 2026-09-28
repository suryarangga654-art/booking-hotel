<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan_tambahan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->decimal('harga', 12, 2);
            $table->enum('satuan', [
                'per_tamu',
                'per_kamar',
                'per_hari',
                'sekali_pakai'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layanan_tambahan');
    }
};