<?php

namespace App\Filament\Resources\Siswas\Pages;

use App\Exports\SiswaTemplateExport;
use App\Filament\Resources\Siswas\SiswaResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListSiswas extends ListRecords
{
    protected static string $resource = SiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('downloadTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {
                    return Excel::download(
                        new SiswaTemplateExport(),
                        'template-import-siswa.xlsx'
                    );
                }),
        ];
    }
}