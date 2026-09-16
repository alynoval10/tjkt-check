<?php

namespace App\Filament\Resources\Siswas\Pages;

use App\Filament\Resources\Siswas\SiswaResource;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewSiswa extends ViewRecord
{
    protected static string $resource = SiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Buka kartu A4 tanpa meninggalkan halaman detail siswa.
            Action::make('cetakKartu')->label('Cetak Kartu')->icon('heroicon-o-printer')
                ->url(fn () => route('siswa.kartu-kompetensi', $this->getRecord()))->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
