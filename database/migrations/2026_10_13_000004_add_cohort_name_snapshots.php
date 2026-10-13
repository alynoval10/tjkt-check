<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('kelas', 'is_active')) {
            Schema::table('kelas', function (Blueprint $table): void {
                $table->boolean('is_active')->default(true)->index();
            });
        }

        if (! Schema::hasColumn('kelulusans', 'kelas_nama_snapshot')) {
            Schema::table('kelulusans', function (Blueprint $table): void {
                $table->string('kelas_nama_snapshot')->nullable();
                $table->string('tingkat_snapshot', 10)->nullable();
            });
        }

        if (! Schema::hasColumn('riwayat_kelas', 'kelas_asal_nama')) {
            Schema::table('riwayat_kelas', function (Blueprint $table): void {
                $table->string('kelas_asal_nama')->nullable();
                $table->string('kelas_asal_tingkat', 10)->nullable();
                $table->string('kelas_tujuan_nama')->nullable();
                $table->string('kelas_tujuan_tingkat', 10)->nullable();
            });
        }

        DB::table('kelulusans')
            ->whereNull('kelas_nama_snapshot')
            ->orderBy('id')
            ->chunkById(500, function ($kelulusans): void {
                foreach ($kelulusans as $kelulusan) {
                    $kelas = $kelulusan->kelas_id
                        ? DB::table('kelas')->where('id', $kelulusan->kelas_id)->first()
                        : null;

                    if (! $kelas) {
                        continue;
                    }

                    DB::table('kelulusans')->where('id', $kelulusan->id)->update([
                        'kelas_nama_snapshot' => $kelas->nama,
                        'tingkat_snapshot' => $kelas->tingkat,
                    ]);
                }
            });

        DB::table('riwayat_kelas')
            ->orderBy('id')
            ->chunkById(500, function ($riwayat): void {
                foreach ($riwayat as $item) {
                    $asal = $item->kelas_asal_id
                        ? DB::table('kelas')->where('id', $item->kelas_asal_id)->first()
                        : null;
                    $tujuan = $item->kelas_tujuan_id
                        ? DB::table('kelas')->where('id', $item->kelas_tujuan_id)->first()
                        : null;

                    DB::table('riwayat_kelas')->where('id', $item->id)->update([
                        'kelas_asal_nama' => $asal?->nama,
                        'kelas_asal_tingkat' => $asal?->tingkat,
                        'kelas_tujuan_nama' => $tujuan?->nama,
                        'kelas_tujuan_tingkat' => $tujuan?->tingkat,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('riwayat_kelas', function (Blueprint $table): void {
            $table->dropColumn([
                'kelas_asal_nama',
                'kelas_asal_tingkat',
                'kelas_tujuan_nama',
                'kelas_tujuan_tingkat',
            ]);
        });

        Schema::table('kelulusans', function (Blueprint $table): void {
            $table->dropColumn(['kelas_nama_snapshot', 'tingkat_snapshot']);
        });

        Schema::table('kelas', function (Blueprint $table): void {
            $table->dropIndex(['is_active']);
            $table->dropColumn('is_active');
        });
    }
};
