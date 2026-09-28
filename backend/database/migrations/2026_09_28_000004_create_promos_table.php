<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 40)->unique();
            $table->enum('jenis_diskon', ['persen', 'nominal']);
            $table->decimal('nilai', 12, 2);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('aktif')->default(true);
        });

        Schema::table('pemesanan', function (Blueprint $table) {
            $table->foreignId('promo_id')->nullable()->constrained('promos')->nullOnDelete();
            $table->decimal('nilai_diskon', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promo_id');
            $table->dropColumn('nilai_diskon');
        });
        Schema::dropIfExists('promos');
    }
};