<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan kolom jika memang belum ada
        if (! Schema::hasColumn('siswas', 'public_id')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->string('public_id', 26)->nullable();
            });
        }

        // Isi public_id untuk semua siswa yang belum punya
        DB::table('siswas')
            ->whereNull('public_id')
            ->orderBy('id')
            ->get()
            ->each(function ($siswa) {
                DB::table('siswas')
                    ->where('id', $siswa->id)
                    ->update([
                        'public_id' => (string) Str::ulid(),
                    ]);
            });

        // Tambahkan unique index
        Schema::table('siswas', function (Blueprint $table) {
            $table->unique('public_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('siswas', 'public_id')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->dropUnique(['public_id']);
                $table->dropColumn('public_id');
            });
        }
    }
};