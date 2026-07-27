<?php

namespace App\Filament\Resources\Kelulusans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KelulusanExport;

class KelulusansTable
{
    public static function configure(Table $table): Table
{
    return $table
       ->columns([
    TextColumn::make('siswa.nama')
        ->label('Siswa')
        ->searchable()
        ->sortable(),

    TextColumn::make('materi.nama')
        ->label('Materi')
        ->searchable()
        ->sortable(),

    TextColumn::make('user.name')
        ->label('Penguji')
        ->searchable()
        ->sortable(),

    TextColumn::make('tanggal_uji')
        ->date()
        ->sortable(),

   TextColumn::make('nilai')
    ->label('Nilai')
    ->formatStateUsing(function ($state) {

        if ($state >= 90) {
            return $state . ' (Sangat Baik)';
        }

        if ($state >= 75) {
            return $state . ' (Baik)';
        }

        if ($state >= 60) {
            return $state . ' (Cukup)';
        }

        return $state . ' (Remedial)';

    })
    ->badge()
    ->color(function ($state) {

        return match (true) {
            $state >= 90 => 'success',
            $state >= 75 => 'info',
            $state >= 60 => 'warning',
            default => 'danger',
        };

    }),

    TextColumn::make('catatan')
    ->label('Catatan')
    ->limit(30),

    TextColumn::make('created_at')
        ->dateTime()
        ->sortable()
        ->toggleable(isToggledHiddenByDefault: true),

    TextColumn::make('updated_at')
        ->dateTime()
        ->sortable()
        ->toggleable(isToggledHiddenByDefault: true),
])

        ->filters([
            //
        ])

        ->headerActions([
            Action::make('export')
                ->label('Download Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {

                    return Excel::download(
                        new KelulusanExport,
                        'daftar-kelulusan-tjkt.xlsx'
                    );

                }),
        ])

        ->recordActions([
            ViewAction::make(),
            EditAction::make(),
        ])

        ->toolbarActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ]);
}
}
