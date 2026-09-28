<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foto_tipe_kamar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipe_kamar_id')->constrained('tipe_kamar')->cascadeOnDelete();
            $table->string('path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_tipe_kamar');
    }
};