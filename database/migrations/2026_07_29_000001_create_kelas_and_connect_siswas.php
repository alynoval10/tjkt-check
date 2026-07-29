<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('kelas')) {
            Schema::create('kelas', function (Blueprint $table) {
                $table->id();
                $table->string('tingkat', 10);
                $table->string('nama')->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('siswas', 'kelas_id')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->foreignId('kelas_id')
                    ->nullable()
                    ->constrained('kelas')
                    ->nullOnDelete();
            });
        }

        // Pindahkan kelas lama yang masih berupa teks menjadi data master kelas.
        if (Schema::hasColumn('siswas', 'kelas')) {
            $namaKelas = DB::table('siswas')
                ->whereNotNull('kelas')
                ->where('kelas', '!=', '')
                ->distinct()
                ->pluck('kelas');

            foreach ($namaKelas as $nama) {
                $nama = trim((string) $nama);
                $tingkat = match (true) {
                    preg_match('/^XII\b/i', $nama) === 1 => 'XII',
                    preg_match('/^XI\b/i', $nama) === 1 => 'XI',
                    default => 'X',
                };

                $kelasId = DB::table('kelas')->where('nama', $nama)->value('id');

                if (! $kelasId) {
                    $kelasId = DB::table('kelas')->insertGetId([
                        'tingkat' => $tingkat,
                        'nama' => $nama,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('siswas')
                    ->where('kelas', $nama)
                    ->whereNull('kelas_id')
                    ->update(['kelas_id' => $kelasId]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('siswas', 'kelas_id')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->dropForeign(['kelas_id']);
                $table->dropColumn('kelas_id');
            });
        }

        Schema::dropIfExists('kelas');
    }
};
