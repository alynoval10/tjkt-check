<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelulusans', function (Blueprint $table) {
            $table->foreignId('periode_akademik_id')
                ->nullable()
                ->constrained('periode_akademiks')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kelulusans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('periode_akademik_id');
        });
    }
};
