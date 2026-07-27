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
    Schema::table('kelulusans', function (Blueprint $table) {

        $table->unique(
            ['siswa_id', 'materi_id'],
            'unique_siswa_materi'
        );

    });
}


public function down(): void
{
    Schema::table('kelulusans', function (Blueprint $table) {

        $table->dropUnique('unique_siswa_materi');

    });
}

    /**
     * Reverse the migrations.
     */
   
};
