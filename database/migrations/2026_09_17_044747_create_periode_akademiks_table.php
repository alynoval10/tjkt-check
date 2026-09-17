<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelulusans', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Hapus Unique Lama
            |--------------------------------------------------------------------------
            | Sebelumnya satu siswa hanya bisa memiliki satu penilaian untuk
            | satu materi sepanjang waktu.
            */

            $table->dropUnique([
                'siswa_id',
                'materi_id',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Unique Berdasarkan Periode
            |--------------------------------------------------------------------------
            | Sekarang materi yang sama boleh dinilai kembali pada semester /
            | tahun ajaran yang berbeda.
            */

            $table->unique([
                'siswa_id',
                'materi_id',
                'periode_akademik_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('kelulusans', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Kembalikan Struktur Lama
            |--------------------------------------------------------------------------
            */

            $table->dropUnique([
                'siswa_id',
                'materi_id',
                'periode_akademik_id',
            ]);

            $table->unique([
                'siswa_id',
                'materi_id',
            ]);
        });
    }
};