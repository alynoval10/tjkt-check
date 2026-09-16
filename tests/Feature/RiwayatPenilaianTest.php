<?php

namespace Tests\Feature;

use App\Models\{Kelas, Materi, Siswa, Kelulusan, RiwayatPenilaian, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatPenilaianTest extends TestCase
{
    use RefreshDatabase;

    private function assessment(): Kelulusan
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $kelas = Kelas::create(['nama' => 'X TJKT', 'tingkat' => 'X']);
        $siswa = Siswa::create(['nis' => '001', 'nama' => 'Ani', 'kelas_id' => $kelas->id]);
        $materi = Materi::create(['kode' => 'A', 'nama' => 'Jaringan', 'tingkat' => 'X']);

        return Kelulusan::create([
            'siswa_id' => $siswa->id, 'materi_id' => $materi->id,
            'user_id' => $user->id, 'tanggal_uji' => '2026-09-16', 'nilai' => 60,
        ]);
    }

    public function test_preserves_previous_score_and_ignores_unchanged_save(): void
    {
        $record = $this->assessment();
        $record->update(['nilai' => 80, 'catatan' => 'Sudah mandiri']);
        $record->save();

        $this->assertSame([60, 80], $record->riwayat()->orderBy('id')->pluck('nilai')->all());
        $this->assertEquals(80, $record->fresh()->nilai);
        $this->assertSame(auth()->user()->name, $record->riwayat()->first()->diubah_oleh);

        $this->view('filament.kelulusan-riwayat', ['riwayat' => $record->riwayat()->get()])
            ->assertSee('60 - Remedial')->assertSee('80 - Lulus')->assertSee('Sudah mandiri');
    }

    public function test_history_failure_rolls_back_score(): void
    {
        $record = $this->assessment();
        RiwayatPenilaian::creating(fn () => throw new \RuntimeException('History failed'));

        try {
            $record->update(['nilai' => 90]);
            $this->fail('Expected history failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('History failed', $exception->getMessage());
            $this->assertEquals(60, $record->fresh()->nilai);
            $this->assertDatabaseCount('riwayat_penilaians', 1);
        } finally {
            RiwayatPenilaian::flushEventListeners();
        }
    }

    public function test_migration_preserves_existing_score_as_baseline(): void
    {
        $record = $this->assessment();
        $migration = require database_path('migrations/2026_09_16_000001_create_riwayat_penilaians_table.php');
        $migration->down();
        $migration->up();

        $this->assertDatabaseHas('riwayat_penilaians', [
            'kelulusan_id' => $record->id, 'nilai' => 60, 'jenis' => 'data_awal',
        ]);
        $record->update(['nilai' => 85]);
        $this->assertSame([60, 85], $record->riwayat()->orderBy('id')->pluck('nilai')->all());
    }
}
