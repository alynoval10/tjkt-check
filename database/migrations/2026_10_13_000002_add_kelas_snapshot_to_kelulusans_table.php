<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('kelulusans', 'kelas_id')) {
            Schema::table('kelulusans', function (Blueprint $table): void {
                $table->foreignId('kelas_id')->nullable()->after('siswa_id')->constrained('kelas')->nullOnDelete();
            });
        }

        DB::table('kelulusans')
            ->select('kelulusans.id', 'siswas.kelas_id as kelas_saat_ini')
            ->join('siswas', 'siswas.id', '=', 'kelulusans.siswa_id')
            ->whereNull('kelulusans.kelas_id')
            ->whereNotNull('siswas.kelas_id')
            ->orderBy('kelulusans.id')
            ->chunkById(500, function ($kelulusans): void {
                foreach ($kelulusans as $kelulusan) {
                    DB::table('kelulusans')
                        ->where('id', $kelulusan->id)
                        ->whereNull('kelas_id')
                        ->update(['kelas_id' => $kelulusan->kelas_saat_ini]);
                }
            }, 'kelulusans.id', 'id');
    }

    public function down(): void
    {
        Schema::table('kelulusans', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('kelas_id');
        });
    }
};
