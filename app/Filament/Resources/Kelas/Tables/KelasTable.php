<?php

namespace App\Filament\Resources\Kelas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KelasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tingkat')->label('Tingkat')->badge()->sortable(),
                TextColumn::make('nama')->label('Nama Kelas')->searchable()->sortable(),
                TextColumn::make('siswas_count')->label('Jumlah Siswa')->counts('siswas'),
            ])
            ->defaultSort('tingkat')
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
