<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['X', 'XI', 'XII'] as $tingkat) {
            $materis = DB::table('materis')
                ->where('tingkat', $tingkat)
                ->orderBy('id')
                ->get(['id']);

            foreach ($materis as $index => $materi) {
                DB::table('materis')
                    ->where('id', $materi->id)
                    ->update(['kode' => sprintf('%s-%03d', $tingkat, $index + 1)]);
            }
        }
    }

    public function down(): void
    {
        // Kode lama tidak dapat dipulihkan setelah diseragamkan.
    }
};
