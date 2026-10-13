<?php

namespace App\Filament\Resources\Siswas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Schema;

class SiswaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('nis')->label('NIS'),
            TextEntry::make('nama')->label('Nama Siswa'),
            TextEntry::make('rombel.tingkat')->label('Tingkat')->placeholder('-'),
            TextEntry::make('rombel.nama')->label('Kelas')->placeholder('-'),
            TextEntry::make('status')->label('Status')->badge()
                ->formatStateUsing(fn (string $state): string => $state === 'alumni' ? 'Alumni' : 'Aktif')
                ->color(fn (string $state): string => $state === 'alumni' ? 'gray' : 'success'),
            TextEntry::make('tanggal_lulus')->label('Tanggal Lulus')->date()->placeholder('-'),
            TextEntry::make('periodeLulus.label')->label('Periode Kelulusan')->placeholder('-'),
            TextEntry::make('created_at')->dateTime()->placeholder('-'),
            TextEntry::make('updated_at')->dateTime()->placeholder('-'),
            RepeatableEntry::make('riwayatKelas')
                ->label('Riwayat Kelas')
                ->schema([
                    TextEntry::make('kelas_asal_nama')->label('Kelas asal')->placeholder('Data awal'),
                    TextEntry::make('kelas_tujuan_nama')->label('Kelas tujuan'),
                    TextEntry::make('periodeAkademik.label')->label('Periode')->placeholder('Periode sebelumnya belum tercatat'),
                    TextEntry::make('jenis_perubahan')
                        ->label('Perubahan')
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'data_awal' => 'Data awal',
                            'dikecualikan' => 'Tidak dinaikkan',
                            'lulus' => 'Lulus / Alumni',
                            default => 'Kenaikan kelas',
                        }),
                    TextEntry::make('catatan')->label('Catatan')->placeholder('-'),
                ])
                ->columns(5)
                ->columnSpanFull(),
            RepeatableEntry::make('kelulusans')
                ->label('Riwayat Nilai per Materi')
                ->schema([
                    TextEntry::make('materi.nama')->label('Materi'),
                    TextEntry::make('kelas_nama_snapshot')->label('Kelas')->placeholder('-'),
                    TextEntry::make('periodeAkademik.label')->label('Periode')->placeholder('Periode lama'),
                    TextEntry::make('tanggal_uji')->label('Tanggal Uji')->date(),
                    TextEntry::make('nilai')->label('Nilai')->placeholder('-'),
                    TextEntry::make('user.name')->label('Penguji')->placeholder('-'),
                    TextEntry::make('catatan')->label('Catatan')->placeholder('-'),
                ])
                ->columns(7)
                ->columnSpanFull(),
        ]);
    }
}
