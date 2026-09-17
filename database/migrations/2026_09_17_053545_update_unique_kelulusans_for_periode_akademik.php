<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Hapus Unique Index Lama
        |--------------------------------------------------------------------------
        | Sebelumnya kombinasi siswa_id + materi_id harus unik untuk selamanya.
        | Nama index lama pada database adalah "unique_siswa_materi".
        |
        | IF EXISTS digunakan agar migration tetap aman jika index lama
        | ternyata sudah tidak ada.
        */

        DB::statement(
            'DROP INDEX IF EXISTS "unique_siswa_materi"'
        );

        /*
        |--------------------------------------------------------------------------
        | Buat Unique Index Baru
        |--------------------------------------------------------------------------
        | Sekarang satu siswa boleh dinilai pada materi yang sama selama
        | periode akademiknya berbeda.
        |
        | Contoh:
        | Ahmad + VLAN + 2026/2027 Ganjil  -> boleh
        | Ahmad + VLAN + 2026/2027 Genap   -> boleh
        |
        | Tetapi kombinasi yang sama persis dalam satu periode tidak boleh
        | diduplikasi.
        */

        DB::statement(
            'CREATE UNIQUE INDEX IF NOT EXISTS "unique_siswa_materi_periode"
            ON "kelulusans" (
                "siswa_id",
                "materi_id",
                "periode_akademik_id"
            )'
        );
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Hapus Unique Index Periode
        |--------------------------------------------------------------------------
        */

        DB::statement(
            'DROP INDEX IF EXISTS "unique_siswa_materi_periode"'
        );

        /*
        |--------------------------------------------------------------------------
        | Kembalikan Unique Index Lama
        |--------------------------------------------------------------------------
        | Digunakan jika migration di-rollback.
        */

        DB::statement(
            'CREATE UNIQUE INDEX IF NOT EXISTS "unique_siswa_materi"
            ON "kelulusans" (
                "siswa_id",
                "materi_id"
            )'
        );
    }
};