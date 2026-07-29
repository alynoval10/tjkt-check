<?php

namespace App\Filament\Widgets;

use App\Models\Kelas;
use App\Models\Kelulusan;
use App\Models\Materi;
use App\Models\Siswa;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatistikAkademik extends StatsOverviewWidget
{
    protected ?string $heading = 'Ringkasan Akademik';

    protected ?string $description = 'Informasi terbaru dari sistem TJKT CHECK.';

    protected function getStats(): array
    {
        $totalPenilaian = Kelulusan::query()->count();

        $totalLulus = Kelulusan::query()
            ->whereNotNull('nilai')
            ->where('nilai', '>=', 75)
            ->count();

        $totalBelumLulus = Kelulusan::query()
            ->whereNotNull('nilai')
            ->where('nilai', '<', 75)
            ->count();

        return [
            Stat::make('Jumlah Siswa', Siswa::query()->count())
                ->description('Seluruh siswa terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('Jumlah Kelas', Kelas::query()->count())
                ->description('Kelas aktif di sistem')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->icon('heroicon-o-academic-cap')
                ->color('success'),

            Stat::make('Jumlah Materi', Materi::query()->count())
                ->description('Materi kompetensi tersedia')
                ->descriptionIcon('heroicon-m-book-open')
                ->icon('heroicon-o-book-open')
                ->color('warning'),

            Stat::make('Sudah Lulus', $totalLulus)
                ->description('Penilaian dengan nilai minimal 75')
                ->descriptionIcon('heroicon-m-check-badge')
                ->icon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make('Belum Lulus', $totalBelumLulus)
                ->description('Masih membutuhkan penguatan')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger'),

            Stat::make('Total Penilaian', $totalPenilaian)
                ->description('Seluruh pengujian tercatat')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('gray'),
        ];
    }
}