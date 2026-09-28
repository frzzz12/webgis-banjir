<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('titik_banjir', function (Blueprint $table) {
            $table->string('kelurahan', 100)->nullable()->after('label')
                  ->comment('nama kelurahan hasil spatial join');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('titik_banjir', function (Blueprint $table) {
            $table->dropColumn('kelurahan');
        });
    }
};
