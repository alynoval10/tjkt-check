<?php

namespace App\Filament\Pages;

use App\Models\Kelas;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Dashboard')
                    ->description('Pilih kelas untuk melihat progres kompetensi siswa.')
                    ->schema([
                        Select::make('kelas_id')
                            ->label('Kelas')
                            ->options(
                                Kelas::query()
                                    ->orderBy('tingkat')
                                    ->orderBy('nama')
                                    ->pluck('nama', 'id')
                            )
                            ->placeholder('Pilih kelas')
                            ->searchable()
                            ->preload()
                            ->live(),
                    ]),
            ]);
    }
}