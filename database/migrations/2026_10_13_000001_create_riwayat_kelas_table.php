<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_kelas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('kelas_asal_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('kelas_tujuan_id')->constrained('kelas')->restrictOnDelete();
            $table->foreignId('periode_akademik_id')->nullable()->constrained('periode_akademiks')->nullOnDelete();
            $table->string('jenis_perubahan')->default('kenaikan');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['siswa_id', 'periode_akademik_id']);
        });

        // Simpan kelas yang sedang berlaku sebagai titik awal sebelum fitur riwayat digunakan.
        DB::table('siswas')
            ->whereNotNull('kelas_id')
            ->orderBy('id')
            ->chunkById(500, function ($siswas): void {
                $now = now();
                $rows = $siswas->map(fn ($siswa): array => [
                    'siswa_id' => $siswa->id,
                    'kelas_asal_id' => null,
                    'kelas_tujuan_id' => $siswa->kelas_id,
                    'periode_akademik_id' => null,
                    'jenis_perubahan' => 'data_awal',
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                DB::table('riwayat_kelas')->insert($rows);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_kelas');
    }
};
