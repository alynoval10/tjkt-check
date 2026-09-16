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
        $this->assertDatabaseCount('riwayat_penilaians', 1);
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
            $this->assertDatabaseCount('riwayat_penilaians', 0);
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

    public function test_search_and_status_filters_preserve_hidden_drafts(): void
    {
        [$page, $first, $second] = $this->assessment();
        $page->set('nilai', [$first->id => 0, $second->id => 75])->call('simpan')
            ->set('filterStatus', 'remedial')->assertSee('1 dari 2 siswa')
            ->assertSee('NIS: 001')->assertDontSee('NIS: 002')
            ->set('nilai.'.$first->id, 90)->set('catatan.'.$first->id, 'Perbaikan selesai')
            ->set('pencarian', 'BUDI')->assertSee('0 dari 2 siswa')
            ->assertSee('Tidak ada siswa yang cocok')
            ->assertSet('nilai.'.$first->id, 90)
            ->assertSet('catatan.'.$first->id, 'Perbaikan selesai')
            ->set('filterStatus', 'semua')->assertSee('NIS: 002')->assertDontSee('NIS: 001')
            ->set('pencarian', ' 001 ')->assertSee('NIS: 001')->assertDontSee('NIS: 002')
            ->set('pencarian', 'Budi')->call('simpan')->assertHasNoErrors();

        // Simpan Semua tetap mencakup isian pada baris yang sedang tersembunyi.
        $this->assertDatabaseHas('kelulusans', ['siswa_id' => $first->id, 'nilai' => 90, 'catatan' => 'Perbaikan selesai']);
        $page->set('pencarian', '')->set('filterStatus', 'lulus')->assertSee('2 dari 2 siswa')
            ->set('filterStatus', 'belum_diuji')->assertSee('0 dari 2 siswa');
    }

    public function test_unassessed_filter_and_material_change_reset(): void
    {
        [$page, $first, $second] = $this->assessment();
        $page->set('nilai', [$first->id => 74])->call('simpan')
            ->set('filterStatus', 'belum_diuji')->assertSee('NIS: 002')->assertDontSee('NIS: 001')
            ->set('pencarian', 'Budi')->set('materiId', null)
            ->assertSet('pencarian', '')->assertSet('filterStatus', 'semua')
            ->assertSet('statusTersimpan', []);
    }

    public function test_confirmation_counts_hidden_scores_and_cancel_keeps_drafts(): void
    {
        [$page, $first, $second] = $this->assessment();
        $page->set('nilai', [$first->id => 0, $second->id => 80])
            ->set('pencarian', 'Ani')->mountAction('konfirmasiSimpan')->assertActionMounted('konfirmasiSimpan');
        $summary = $page->instance()->getMountedAction()->getModalContent();
        $this->assertSame('X TJKT 1', $summary->getData()['kelas']);
        $this->assertSame('Jaringan', $summary->getData()['materi']);
        $this->assertSame(2, $summary->getData()['jumlah']);
        $this->assertSame(1, $summary->getData()['tersembunyi']);
        $this->assertStringContainsString('Nilai akan diproses', $summary->render());
        $this->assertDatabaseCount('kelulusans', 0);

        $page->call('unmountAction')->assertSet('nilai.'.$first->id, 0)
            ->assertSet('nilai.'.$second->id, 80);
        $this->assertDatabaseCount('kelulusans', 0);

        $page->mountAction('konfirmasiSimpan')->callMountedAction()->assertHasNoErrors();
        $this->assertDatabaseCount('kelulusans', 2);
    }

    public function test_confirmation_does_not_bypass_validation(): void
    {
        [$page, $first, $second] = $this->assessment();
        $page->set('nilai', [$first->id => 101, $second->id => ''])
            ->mountAction('konfirmasiSimpan');
        $this->assertSame(1, $page->instance()->getMountedAction()->getModalContent()->getData()['kosong']);
        $page->callMountedAction()->assertHasErrors(['nilai.'.$first->id]);
        $this->assertDatabaseCount('kelulusans', 0);
    }
}
