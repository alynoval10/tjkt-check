<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelulusan_id')->constrained()->cascadeOnDelete();
            // Nama disalin agar riwayat tetap terbaca ketika profil berganti nama.
            $table->string('siswa');
            $table->string('materi');
            $table->string('penguji');
            $table->string('diubah_oleh')->nullable();
            $table->date('tanggal_uji');
            $table->unsignedTinyInteger('nilai')->nullable();
            $table->text('catatan')->nullable();
            $table->string('jenis');
            $table->timestamp('dicatat_pada');
        });

        // Data lama menjadi titik awal, bukan riwayat ujian yang direkonstruksi.
        DB::table('kelulusans')->orderBy('id')->chunkById(500, function ($records): void {
            foreach ($records as $record) {
                DB::table('riwayat_penilaians')->insert([
                    'kelulusan_id' => $record->id,
                    'siswa' => DB::table('siswas')->where('id', $record->siswa_id)->value('nama') ?? '-',
                    'materi' => DB::table('materis')->where('id', $record->materi_id)->value('nama') ?? '-',
                    'penguji' => DB::table('users')->where('id', $record->user_id)->value('name') ?? '-',
                    'diubah_oleh' => null,
                    'tanggal_uji' => $record->tanggal_uji,
                    'nilai' => $record->nilai,
                    'catatan' => $record->catatan,
                    'jenis' => 'data_awal',
                    'dicatat_pada' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_penilaians');
    }
};
