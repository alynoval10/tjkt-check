<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('siswas', 'kelas')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->dropColumn('kelas');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('siswas', 'kelas')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->string('kelas')->nullable();
            });
        }
    }
};