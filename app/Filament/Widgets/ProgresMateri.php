<?php

namespace App\Filament\Widgets;

use App\Models\Kelas;
use App\Models\Kelulusan;
use App\Models\Materi;
use App\Models\Siswa;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ProgresMateri extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Progres Kompetensi per Materi';

    protected ?string $description =
        'Distribusi siswa lulus, belum lulus, dan belum diuji pada setiap materi.';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '600px';

    protected function getData(): array
    {
        $kelasId = $this->pageFilters['kelas_id'] ?? null;

        $kelas = $kelasId
            ? Kelas::find($kelasId)
            : Kelas::query()
                ->orderBy('tingkat')
                ->orderBy('nama')
                ->first();

        if (! $kelas) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Materi sesuai tingkat kelas
        |--------------------------------------------------------------------------
        */

        $materis = Materi::query()
            ->where('tingkat', $kelas->tingkat)
            ->orderBy('nama')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Siswa pada kelas yang dipilih
        |--------------------------------------------------------------------------
        */

        $siswaIds = Siswa::query()
            ->where('kelas_id', $kelas->id)
            ->pluck('id');

        $totalSiswa = $siswaIds->count();

        /*
        |--------------------------------------------------------------------------
        | Semua penilaian siswa pada materi yang relevan
        |--------------------------------------------------------------------------
        */

        $penilaians = Kelulusan::query()
            ->whereIn('siswa_id', $siswaIds)
            ->whereIn('materi_id', $materis->pluck('id'))
            ->get()
            ->groupBy('materi_id');

        $dataLulus = [];
        $dataBelumLulus = [];
        $dataBelumDiuji = [];

        foreach ($materis as $materi) {

            $penilaianMateri = $penilaians->get(
                $materi->id,
                collect()
            );

            /*
             * Lulus = nilai >= 75
             */
            $lulus = $penilaianMateri
                ->filter(
                    fn ($kelulusan) =>
                        ! is_null($kelulusan->nilai)
                        && $kelulusan->nilai >= 75
                )
                ->count();

            /*
             * Belum lulus = sudah diuji tetapi nilai < 75
             */
            $belumLulus = $penilaianMateri
                ->filter(
                    fn ($kelulusan) =>
                        ! is_null($kelulusan->nilai)
                        && $kelulusan->nilai < 75
                )
                ->count();

            /*
             * Jumlah siswa yang sudah mempunyai penilaian
             */
            $sudahDiuji = $penilaianMateri
                ->pluck('siswa_id')
                ->unique()
                ->count();

            /*
             * Belum diuji = total siswa - siswa yang sudah diuji
             */
            $belumDiuji = max(
                0,
                $totalSiswa - $sudahDiuji
            );

            $dataLulus[] = $lulus;
            $dataBelumLulus[] = $belumLulus;
            $dataBelumDiuji[] = $belumDiuji;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Lulus',
                    'data' => $dataLulus,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.75)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 1,
                    'stack' => 'kompetensi',
                ],

                [
                    'label' => 'Belum Lulus',
                    'data' => $dataBelumLulus,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.75)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 1,
                    'stack' => 'kompetensi',
                ],

                [
                    'label' => 'Belum Diuji',
                    'data' => $dataBelumDiuji,
                    'backgroundColor' => 'rgba(148, 163, 184, 0.65)',
                    'borderColor' => 'rgb(148, 163, 184)',
                    'borderWidth' => 1,
                    'stack' => 'kompetensi',
                ],
            ],

            'labels' => $materis
                ->pluck('nama')
                ->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            /*
             * Horizontal bar
             */
            'indexAxis' => 'y',

            'responsive' => true,

            'maintainAspectRatio' => false,

            /*
             * Batang ditumpuk
             */
            'scales' => [
                'x' => [
                    'stacked' => true,
                    'beginAtZero' => true,

                    'ticks' => [
                        'precision' => 0,
                    ],

                    'title' => [
                        'display' => true,
                        'text' => 'Jumlah Siswa',
                    ],
                ],

                'y' => [
                    'stacked' => true,
                ],
            ],

            /*
             * Legend di atas
             */
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],

                'tooltip' => [
                    'enabled' => true,
                ],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Refresh chart ketika filter kelas berubah
    |--------------------------------------------------------------------------
    */

    public function updatedPageFilters(
        mixed $value = null,
        ?string $key = null
    ): void {
        $this->cachedData = null;

        $this->updateChartData();
    }
}