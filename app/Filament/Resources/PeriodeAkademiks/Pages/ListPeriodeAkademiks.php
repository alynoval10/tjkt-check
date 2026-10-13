<?php

namespace App\Filament\Resources\PeriodeAkademiks\Pages;

use App\Filament\Resources\PeriodeAkademiks\PeriodeAkademikResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPeriodeAkademiks extends ListRecords
{
    protected static string $resource = PeriodeAkademikResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Tambah Periode')];
    }
}
