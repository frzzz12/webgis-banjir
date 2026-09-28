<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titik_banjir', function (Blueprint $table) {
            $table->id();
            $table->decimal('longitude', 12, 8);
            $table->decimal('latitude', 12, 8);
            $table->decimal('curah_hujan', 10, 4)->nullable()->comment('mm/tahun');
            $table->string('jenis_tanah', 100)->nullable();
            $table->string('kemiringan', 100)->nullable()->comment('kelas kemiringan lereng');
            $table->string('penggunaan_lahan', 100)->nullable();
            $table->decimal('drainase_skor', 10, 4)->nullable();
            $table->string('flood_hist', 100)->nullable()->comment('riwayat kejadian banjir');
            $table->string('label', 50)->nullable()->comment('sangat rendah/rendah/sedang/tinggi');
            $table->string('geology', 255)->nullable();
            $table->decimal('slope', 10, 6)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titik_banjir');
    }
};
