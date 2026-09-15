<?php

namespace App\Filament\Widgets;

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Siswa;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProgresKelas extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Progres Kompetensi Kelas';

    protected ?string $description = 'Monitoring pencapaian kompetensi siswa berdasarkan kelas.';

    protected function getStats(): array
    {
        $kelasId = $this->pageFilters['kelas_id'] ?? null;

        $kelas = $kelasId
            ? Kelas::find($kelasId)
            : Kelas::query()
                ->orderBy('tingkat')
                ->orderBy('nama')
                ->first();

        if (! $kelas) {
            return [];
        }

        $siswas = Siswa::query()
            ->where('kelas_id', $kelas->id)
            ->with('kelulusans')
            ->get();

        $materis = Materi::query()
            ->where('tingkat', $kelas->tingkat)
            ->get();

        $materiIds = $materis->pluck('id');

        $totalSiswa = $siswas->count();
        $totalMateri = $materis->count();

        $progresSiswa = $siswas->map(function ($siswa) use (
            $materiIds,
            $totalMateri
        ) {
            if ($totalMateri === 0) {
                return 0;
            }

            $jumlahLulus = $siswa->kelulusans
                ->whereIn('materi_id', $materiIds)
                ->filter(
                    fn ($kelulusan) =>
                        ! is_null($kelulusan->nilai)
                        && $kelulusan->nilai >= 75
                )
                ->count();

            return round(
                ($jumlahLulus / $totalMateri) * 100
            );
        });

        $rataRata = $progresSiswa->isNotEmpty()
            ? round($progresSiswa->avg())
            : 0;

        $kompetenPenuh = $progresSiswa
            ->filter(fn ($progres) => $progres >= 100)
            ->count();

        $perluPerhatian = $progresSiswa
            ->filter(fn ($progres) => $progres < 50)
            ->count();

        return [
            Stat::make('Jumlah Siswa', $totalSiswa)
                ->description($kelas->nama)
                ->descriptionIcon('heroicon-m-users')
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('Rata-rata Progres', $rataRata . '%')
                ->description('Pencapaian kompetensi kelas')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->icon('heroicon-o-chart-bar')
                ->color(
                    $rataRata >= 75
                        ? 'success'
                        : ($rataRata >= 50 ? 'warning' : 'danger')
                ),

            Stat::make('Kompeten 100%', $kompetenPenuh)
                ->description('Siswa menyelesaikan seluruh materi')
                ->descriptionIcon('heroicon-m-check-badge')
                ->icon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make('Perlu Perhatian', $perluPerhatian)
                ->description('Progres kompetensi di bawah 50%')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->icon('heroicon-o-exclamation-triangle')
                ->color(
                    $perluPerhatian > 0
                        ? 'danger'
                        : 'success'
                ),
        ];
    }
}