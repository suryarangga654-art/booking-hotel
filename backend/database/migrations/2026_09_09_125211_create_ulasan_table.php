<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ulasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_id')->unique()->constrained('pemesanan');
            // Menghubungkan ke tabel 'users' menggantikan 'pengguna'
            $table->foreignId('pengguna_id')->constrained('users');
            $table->unsignedTinyInteger('penilaian'); // Alternatif CHECK (1..5) di Laravel
            $table->text('komentar')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ulasan');
    }
};