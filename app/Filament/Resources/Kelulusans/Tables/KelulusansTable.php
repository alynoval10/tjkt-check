<?php

namespace App\Filament\Resources\Kelulusans\Tables;

use App\Exports\KelulusanExport;
use App\Models\Kelas;
use App\Models\Kelulusan;
use App\Models\PeriodeAkademik;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class KelulusansTable
{
    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Tabel Kelulusan
    |--------------------------------------------------------------------------
    | Menampilkan data hasil penilaian siswa, periode akademik,
    | filter, export Excel, serta riwayat penilaian.
    */

    public static function configure(Table $table): Table
    {
        return $table

            /*
            |--------------------------------------------------------------------------
            | Kolom
            |--------------------------------------------------------------------------
            */

            ->columns([

                /*
                |--------------------------------------------------------------------------
                | Kelas
                |--------------------------------------------------------------------------
                */

                TextColumn::make('siswa.rombel.nama')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | Siswa
                |--------------------------------------------------------------------------
                */

                TextColumn::make('siswa.nama')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | Materi
                |--------------------------------------------------------------------------
                */

                TextColumn::make('materi.nama')
                    ->label('Materi')
                    ->searchable()
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | Tahun Ajaran
                |--------------------------------------------------------------------------
                | Data sebelum fitur periode akademik dibuat masih memiliki
                | periode_akademik_id NULL dan ditampilkan sebagai "Data Lama".
                */

                TextColumn::make('periodeAkademik.tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->formatStateUsing(
                        fn ($state, Kelulusan $record): string =>
                            $record->periodeAkademik?->tahun_ajaran
                            ?? 'Data Lama'
                    )
                    ->badge()
                    ->color(
                        fn (Kelulusan $record): string =>
                            $record->periode_akademik_id
                                ? 'info'
                                : 'gray'
                    )
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | Semester
                |--------------------------------------------------------------------------
                */

                TextColumn::make('periodeAkademik.semester')
                    ->label('Semester')
                    ->formatStateUsing(
                        fn ($state, Kelulusan $record): string =>
                            $record->periodeAkademik?->semester
                            ?? '-'
                    )
                    ->badge()
                    ->color(
                        fn (Kelulusan $record): string =>
                            $record->periode_akademik_id
                                ? 'success'
                                : 'gray'
                    )
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | Penguji
                |--------------------------------------------------------------------------
                */

                TextColumn::make('user.name')
                    ->label('Penguji')
                    ->searchable()
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | Tanggal Uji
                |--------------------------------------------------------------------------
                */

                TextColumn::make('tanggal_uji')
                    ->label('Tanggal Uji')
                    ->date('d M Y')
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | Nilai
                |--------------------------------------------------------------------------
                | 90 - 100 : Sangat Baik
                | 75 - 89  : Baik / Lulus
                | 60 - 74  : Cukup / Remedial
                | < 60     : Remedial
                */

                TextColumn::make('nilai')
                    ->label('Nilai')
                    ->formatStateUsing(function ($state): string {
                        /*
                         * Antisipasi jika ada data nilai NULL.
                         */
                        if (is_null($state)) {
                            return 'Belum Dinilai';
                        }

                        $nilai = (int) $state;

                        if ($nilai >= 90) {
                            return "{$nilai} (Sangat Baik)";
                        }

                        if ($nilai >= 75) {
                            return "{$nilai} (Baik)";
                        }

                        if ($nilai >= 60) {
                            return "{$nilai} (Cukup)";
                        }

                        return "{$nilai} (Remedial)";
                    })
                    ->badge()
                    ->color(function ($state): string {
                        /*
                         * Nilai NULL diberi warna abu-abu.
                         */
                        if (is_null($state)) {
                            return 'gray';
                        }

                        $nilai = (int) $state;

                        return match (true) {
                            $nilai >= 90 => 'success',
                            $nilai >= 75 => 'info',
                            $nilai >= 60 => 'warning',
                            default => 'danger',
                        };
                    })
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | Catatan
                |--------------------------------------------------------------------------
                */

                TextColumn::make('catatan')
                    ->label('Catatan')
                    ->limit(30)
                    ->placeholder('-')
                    ->tooltip(
                        fn ($state): ?string =>
                            $state ?: null
                    ),

                /*
                |--------------------------------------------------------------------------
                | Waktu Dibuat
                |--------------------------------------------------------------------------
                */

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),

                /*
                |--------------------------------------------------------------------------
                | Waktu Diubah
                |--------------------------------------------------------------------------
                */

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),
            ])

            /*
            |--------------------------------------------------------------------------
            | Filter
            |--------------------------------------------------------------------------
            */

            ->filters([

                /*
                |--------------------------------------------------------------------------
                | Filter Tingkat
                |--------------------------------------------------------------------------
                */

                SelectFilter::make('tingkat')
                    ->label('Tingkat')
                    ->options([
                        'X' => 'X',
                        'XI' => 'XI',
                        'XII' => 'XII',
                    ])
                    ->query(
                        fn ($query, array $data) =>
                            $query->when(
                                $data['value'] ?? null,
                                fn ($query, $tingkat) =>
                                    $query->whereHas(
                                        'siswa.rombel',
                                        fn ($q) =>
                                            $q->where(
                                                'tingkat',
                                                $tingkat
                                            )
                                    )
                            )
                    ),

                /*
                |--------------------------------------------------------------------------
                | Filter Kelas
                |--------------------------------------------------------------------------
                */

                SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->options(
                        fn () => Kelas::query()
                            ->orderBy('tingkat')
                            ->orderBy('nama')
                            ->pluck('nama', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->query(
                        fn ($query, array $data) =>
                            $query->when(
                                $data['value'] ?? null,
                                fn ($query, $kelasId) =>
                                    $query->whereHas(
                                        'siswa',
                                        fn ($q) =>
                                            $q->where(
                                                'kelas_id',
                                                $kelasId
                                            )
                                    )
                            )
                    ),

                /*
                |--------------------------------------------------------------------------
                | Filter Periode Akademik
                |--------------------------------------------------------------------------
                | Memungkinkan guru melihat nilai berdasarkan semester
                | dan tahun ajaran tertentu.
                */

                SelectFilter::make('periode_akademik_id')
                    ->label('Periode Akademik')
                    ->options(
                        fn () => PeriodeAkademik::query()
                            ->orderByDesc('tahun_ajaran')
                            ->orderBy('semester')
                            ->get()
                            ->mapWithKeys(
                                fn (PeriodeAkademik $periode) => [
                                    $periode->id =>
                                        "{$periode->tahun_ajaran} - {$periode->semester}",
                                ]
                            )
                            ->toArray()
                    )
                    ->searchable()
                    ->preload(),

                /*
                |--------------------------------------------------------------------------
                | Filter Data Lama
                |--------------------------------------------------------------------------
                | Berguna untuk menemukan penilaian sebelum fitur Tahun
                | Ajaran / Semester diterapkan.
                */

                SelectFilter::make('status_periode')
                    ->label('Status Periode')
                    ->options([
                        'periode' => 'Sudah Ada Periode',
                        'legacy' => 'Data Lama',
                    ])
                    ->query(
                        function ($query, array $data) {
                            return match ($data['value'] ?? null) {
                                'periode' =>
                                    $query->whereNotNull(
                                        'periode_akademik_id'
                                    ),

                                'legacy' =>
                                    $query->whereNull(
                                        'periode_akademik_id'
                                    ),

                                default => $query,
                            };
                        }
                    ),
            ])

            /*
            |--------------------------------------------------------------------------
            | Header Action
            |--------------------------------------------------------------------------
            | Export seluruh data Kelulusan ke Excel.
            */

            ->headerActions([
                Action::make('export')
                    ->label('Download Excel')
                    ->icon(
                        'heroicon-o-arrow-down-tray'
                    )
                    ->action(
                        function () {
                            return Excel::download(
                                new KelulusanExport(),
                                'daftar-kelulusan-tjkt.xlsx'
                            );
                        }
                    ),
            ])

            /*
            |--------------------------------------------------------------------------
            | Action per Record
            |--------------------------------------------------------------------------
            */

            ->recordActions([

                /*
                |--------------------------------------------------------------------------
                | Riwayat
                |--------------------------------------------------------------------------
                | Riwayat hanya dibaca.
                | Koreksi data tetap dilakukan melalui form penilaian.
                */

                Action::make('riwayat')
                    ->label('Riwayat')
                    ->icon('heroicon-o-clock')
                    ->modalHeading(
                        'Riwayat Penilaian'
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(
                        fn (Kelulusan $record) =>
                            view(
                                'filament.kelulusan-riwayat',
                                [
                                    'riwayat' =>
                                        $record
                                            ->riwayat()
                                            ->orderByDesc('id')
                                            ->get(),
                                ]
                            )
                    ),

                /*
                |--------------------------------------------------------------------------
                | Lihat
                |--------------------------------------------------------------------------
                */

                ViewAction::make(),

                /*
                |--------------------------------------------------------------------------
                | Edit
                |--------------------------------------------------------------------------
                */

                EditAction::make(),
            ])

            /*
            |--------------------------------------------------------------------------
            | Bulk Action
            |--------------------------------------------------------------------------
            */

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}