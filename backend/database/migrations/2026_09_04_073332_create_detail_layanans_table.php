<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_layanan', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('pemesanan_id');
            $table->unsignedInteger('layanan_tambahan_id');

            $table->integer('jumlah')->default(1);
            $table->decimal('total_harga', 12, 2);

            $table->foreign('pemesanan_id')
                ->references('id')
                ->on('pemesanan')
                ->onDelete('cascade');

            $table->foreign('layanan_tambahan_id')
                ->references('id')
                ->on('layanan_tambahan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_layanan');
    }
};