<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table): void {
            $table->string('status')->default('aktif')->index();
            $table->date('tanggal_lulus')->nullable();
            $table->foreignId('periode_lulus_id')->nullable()->constrained('periode_akademiks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('periode_lulus_id');
            $table->dropIndex(['status']);
            $table->dropColumn(['tanggal_lulus', 'status']);
        });
    }
};
