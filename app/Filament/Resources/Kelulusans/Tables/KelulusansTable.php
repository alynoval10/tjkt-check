<?php

namespace App\Filament\Resources\Kelulusans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KelulusanExport;
use App\Models\Kelas;

class KelulusansTable
{
    public static function configure(Table $table): Table
{
    return $table
       ->columns([
    TextColumn::make('siswa.rombel.nama')->label('Kelas')->searchable()->sortable(),

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
            SelectFilter::make('tingkat')
                ->label('Tingkat')
                ->options(['X' => 'X', 'XI' => 'XI', 'XII' => 'XII'])
                ->query(fn ($query, array $data) => $query->when(
                    $data['value'] ?? null,
                    fn ($query, $tingkat) => $query->whereHas('siswa.rombel', fn ($q) => $q->where('tingkat', $tingkat))
                )),
            SelectFilter::make('kelas_id')
                ->label('Kelas')
                ->options(fn () => Kelas::query()->orderBy('tingkat')->orderBy('nama')->pluck('nama', 'id'))
                ->query(fn ($query, array $data) => $query->when(
                    $data['value'] ?? null,
                    fn ($query, $kelasId) => $query->whereHas('siswa', fn ($q) => $q->where('kelas_id', $kelasId))
                )),
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
