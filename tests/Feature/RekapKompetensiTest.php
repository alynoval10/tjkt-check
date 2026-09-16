<?php

namespace Tests\Feature;

use App\Exports\RekapKompetensiExport;
use App\Filament\Widgets\MatriksKompetensi;
use App\Models\{Kelas, Materi, Siswa, Kelulusan, User};
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class RekapKompetensiTest extends TestCase
{
    use RefreshDatabase;

    public function test_filters_statuses_and_real_excel_output(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $user = User::factory()->create();
        $this->actingAs($user);
        $kelas = Kelas::create(['nama' => 'X TJKT', 'tingkat' => 'X']);
        $materi = Materi::create(['kode' => 'A', 'nama' => 'Jaringan', 'tingkat' => 'X']);
        $other = Materi::create(['kode' => 'B', 'nama' => 'Routing', 'tingkat' => 'X']);
        $siswa = Siswa::create(['nis' => '001', 'nama' => '=Ani', 'kelas_id' => $kelas->id]);
        Siswa::create(['nis' => '002', 'nama' => 'Belum Ujian', 'kelas_id' => $kelas->id]);
        foreach ([$materi->id => 75, $other->id => 0] as $id => $nilai) {
            Kelulusan::create(['siswa_id' => $siswa->id, 'materi_id' => $id, 'nilai' => $nilai, 'user_id' => $user->id, 'tanggal_uji' => '2026-09-16']);
        }

        $widget = Livewire::test(MatriksKompetensi::class, ['pageFilters' => ['kelas_id' => $kelas->id]])
            ->assertSee('Ekspor Excel')->assertSet('siswas.0.progres', 50)
            ->assertSet('siswas.0.remedial', ['Routing'])
            ->assertSet('siswas.1.matrix.'.$materi->id.'.status', 'belum_diuji')
            ->set('hanyaRemedial', true)->assertCount('siswas', 1);

        // Uji file XLSX nyata, termasuk tipe sel NIS dan nama.
        $export = new RekapKompetensiExport('X TJKT', $widget->get('materis'), $widget->get('siswas'));
        $file = tempnam(sys_get_temp_dir(), 'rekap');
        try {
            file_put_contents($file, Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX));
            $sheet = IOFactory::load($file)->getActiveSheet();
            $this->assertSame('001', $sheet->getCell('B2')->getValue());
            $this->assertSame('s', $sheet->getCell('C2')->getDataType());
            $this->assertSame('0 - Remedial', $sheet->getCell('E2')->getValue());
            $this->assertEquals(50, $sheet->getCell('F2')->getValue());
        } finally {
            unlink($file);
        }

        $widget->call('exportExcel')->assertFileDownloaded('rekap-kompetensi-kelas-'.$kelas->id.'.xlsx')
            ->set('materiId', $materi->id)->assertCount('siswas', 0)
            ->set('hanyaRemedial', false)->assertSet('siswas.0.progres', 100);

        $empty = Kelas::create(['nama' => 'XI TJKT', 'tingkat' => 'XI']);
        Livewire::test(MatriksKompetensi::class, ['pageFilters' => ['kelas_id' => $empty->id]])
            ->assertSet('materiId', null)->assertCount('materis', 0);
    }
}
