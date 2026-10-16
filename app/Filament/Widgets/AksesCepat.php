<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\PenilaianMassal;
use App\Filament\Resources\Kelas\KelasResource;
use App\Filament\Resources\Materis\MateriResource;
use App\Filament\Resources\Siswas\SiswaResource;
use Filament\Widgets\Widget;

class AksesCepat extends Widget
{
    protected string $view = 'filament.widgets.akses-cepat';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected function getViewData(): array
    {
        return [
            'links' => [
                ['label' => 'Tambah Siswa', 'description' => 'Daftarkan peserta didik', 'icon' => 'heroicon-o-user-plus', 'url' => SiswaResource::getUrl('create')],
                ['label' => 'Penilaian Massal', 'description' => 'Input nilai satu kelas', 'icon' => 'heroicon-o-clipboard-document-check', 'url' => PenilaianMassal::getUrl()],
                ['label' => 'Kelola Materi', 'description' => 'Atur materi kompetensi', 'icon' => 'heroicon-o-book-open', 'url' => MateriResource::getUrl('index')],
                ['label' => 'Data Kelas', 'description' => 'Lihat dan kelola rombel', 'icon' => 'heroicon-o-academic-cap', 'url' => KelasResource::getUrl('index')],
            ],
        ];
    }
}
