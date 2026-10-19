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

    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            '2xl' => 12,
        ];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Dashboard')
                    ->description('Pilih satu rombel untuk memusatkan ringkasan. Kosongkan pilihan untuk melihat seluruh kelas aktif.')
                    ->schema([
                        Select::make('kelas_id')
                            ->label('Kelas')
                            ->options(
                                Kelas::query()
                                    ->where('is_active', true)
                                    ->orderBy('tingkat')
                                    ->orderBy('nama')
                                    ->pluck('nama', 'id')
                            )
                            ->placeholder('Semua kelas')
                            ->searchable()
                            ->preload()
                            ->live(),
                    ]),
            ]);
    }
}
