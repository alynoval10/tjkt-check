<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('materis', 'tingkat')) {
            Schema::table('materis', function (Blueprint $table) {
                $table->string('tingkat', 10)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('materis', 'tingkat')) {
            Schema::table('materis', function (Blueprint $table) {
                $table->dropColumn('tingkat');
            });
        }
    }
};
