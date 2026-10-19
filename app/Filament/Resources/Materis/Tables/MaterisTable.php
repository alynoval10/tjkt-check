<?php

namespace App\Filament\Resources\Materis\Tables;

use App\Filament\Resources\Materis\MateriResource;
use App\Models\Materi;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class MaterisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama')
                    ->label('Nama Materi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tingkat')
                    ->label('Tingkat')
                    ->badge()
                    ->sortable(),

                IconColumn::make('pdf_file')
                    ->label('PDF')
                    ->boolean(fn ($state): bool => filled($state))
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-document-minus')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('kode', 'asc')
            ->filters([
                SelectFilter::make('tingkat')
                    ->label('Tingkat')
                    ->options([
                        'X' => 'Kelas X',
                        'XI' => 'Kelas XI',
                        'XII' => 'Kelas XII',
                    ]),
            ])
            ->headerActions([
                Action::make('tambahMateri')
                    ->label('Tambah Materi')
                    ->icon('heroicon-o-plus')
                    ->url(MateriResource::getUrl('create')),

                Action::make('sesuaikanKodeLama')
                    ->label('Sesuaikan Kode Lama')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Sesuaikan kode semua materi?')
                    ->modalDescription('Kode akan disusun ulang berdasarkan tingkat dengan format X-001, XI-001, dan XII-001. Nilai siswa tetap terhubung ke materi yang sama.')
                    ->action(function (): void {
                        $jumlahDiubah = 0;

                        DB::transaction(function () use (&$jumlahDiubah): void {
                            foreach (['X', 'XI', 'XII'] as $tingkat) {
                                Materi::query()
                                    ->where('tingkat', $tingkat)
                                    ->orderBy('id')
                                    ->get()
                                    ->values()
                                    ->each(function (Materi $materi, int $index) use ($tingkat, &$jumlahDiubah): void {
                                        $kodeBaru = sprintf('%s-%03d', $tingkat, $index + 1);

                                        if ($materi->kode === $kodeBaru) {
                                            return;
                                        }

                                        DB::table('materis')
                                            ->where('id', $materi->id)
                                            ->update([
                                                'kode' => $kodeBaru,
                                                'updated_at' => now(),
                                            ]);

                                        $jumlahDiubah++;
                                    });
                            }
                        });

                        Notification::make()
                            ->title($jumlahDiubah > 0 ? 'Kode materi berhasil disesuaikan' : 'Semua kode sudah sesuai')
                            ->body($jumlahDiubah > 0 ? "{$jumlahDiubah} kode materi diperbarui." : 'Tidak ada kode lama yang perlu diubah.')
                            ->success()
                            ->send();
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
