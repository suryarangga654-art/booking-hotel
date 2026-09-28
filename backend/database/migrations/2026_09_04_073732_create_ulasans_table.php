<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ulasan', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('pemesanan_id')->unique();

            $table->unsignedBigInteger('pengguna_id');

            $table->integer('penilaian');

            $table->text('komentar')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Foreign key ke pemesanan
            $table->foreign('pemesanan_id')
                ->references('id')
                ->on('pemesanan')
                ->onDelete('cascade');

            // Foreign key ke users
            $table->foreign('pengguna_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ulasan');
    }
};