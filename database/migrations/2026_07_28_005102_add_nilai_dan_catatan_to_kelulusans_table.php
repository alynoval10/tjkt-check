<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('kelulusans', 'nilai')) {
            Schema::table('kelulusans', function (Blueprint $table) {
                $table->integer('nilai')->nullable();
            });
        }

        if (!Schema::hasColumn('kelulusans', 'catatan')) {
            Schema::table('kelulusans', function (Blueprint $table) {
                $table->text('catatan')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('kelulusans', 'catatan')) {
            Schema::table('kelulusans', function (Blueprint $table) {
                $table->dropColumn('catatan');
            });
        }

        if (Schema::hasColumn('kelulusans', 'nilai')) {
            Schema::table('kelulusans', function (Blueprint $table) {
                $table->dropColumn('nilai');
            });
        }
    }
};