<?php

namespace Tests\Feature;

use App\Filament\Pages\PenilaianMassal;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Siswa;
use App\Models\Kelulusan;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PenilaianMassalTest extends TestCase
{
    use RefreshDatabase;

    private function assessment(): array
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());
        $kelas = Kelas::create(['nama' => 'X TJKT 1', 'tingkat' => 'X']);
        $materi = Materi::create(['kode' => 'J01', 'nama' => 'Jaringan', 'tingkat' => 'X']);
        $first = Siswa::create(['nis' => '001', 'nama' => 'Ani', 'kelas_id' => $kelas->id]);
        $second = Siswa::create(['nis' => '002', 'nama' => 'Budi', 'kelas_id' => $kelas->id]);

        return [Livewire::test(PenilaianMassal::class)->set('kelasId', $kelas->id)->set('materiId', $materi->id), $first, $second];
    }

    public function test_invalid_later_score_prevents_all_writes(): void
    {
        [$page, $first, $second] = $this->assessment();

        foreach ([101, -1, 'abc', '80.5'] as $invalid) {
            $page->set('nilai', [$first->id => 80, $second->id => $invalid])
                ->call('simpan')->assertHasErrors(['nilai.'.$second->id])
                ->assertNotDispatched('penilaian-disimpan');
            $this->assertDatabaseCount('kelulusans', 0);
        }
    }

    public function test_saves_zero_skips_blank_and_does_not_duplicate_on_repeat(): void
    {
        [$page, $first, $second] = $this->assessment();
        $page->set('nilai', [$first->id => 0, $second->id => ''])
            ->call('simpan')->assertHasNoErrors()->assertDispatched('penilaian-disimpan')
            ->call('simpan')->assertHasNoErrors();

        $this->assertDatabaseCount('kelulusans', 1);
        $this->assertDatabaseHas('kelulusans', ['siswa_id' => $first->id, 'nilai' => 0]);
    }

    public function test_invalid_date_prevents_writes(): void
    {
        [$page, $first] = $this->assessment();
        $page->set('nilai', [$first->id => 80])->set('tanggalUji', '2026-02-31')
            ->call('simpan')->assertHasErrors(['tanggalUji']);
        $this->assertDatabaseCount('kelulusans', 0);
    }

    public function test_database_failure_rolls_back_earlier_student(): void
    {
        [$page, $first, $second] = $this->assessment();
        Kelulusan::creating(function ($record) use ($second): void {
            if ($record->siswa_id === $second->id) {
                throw new \RuntimeException('Simulated write failure');
            }
        });

        try {
            $page->set('nilai', [$first->id => 80, $second->id => 90])->call('simpan');
            $this->fail('Expected a write failure.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated write failure', $exception->getMessage());
            $this->assertDatabaseCount('kelulusans', 0);
        } finally {
            Kelulusan::flushEventListeners();
        }
    }

    public function test_page_renders_with_material_selection_disabled_until_class_is_selected(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs(User::factory()->create());

        Livewire::test(PenilaianMassal::class)
            ->assertSuccessful()
            ->assertSee('Pilih kelas')
            ->assertSeeHtml('disabled');
    }
}
