<?php

namespace App\Filament\Resources\Kelulusans\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class KelulusanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('siswa.nama')->label('Siswa'),
            TextEntry::make('siswa.rombel.nama')->label('Kelas'),
            TextEntry::make('materi.nama')->label('Materi'),
            TextEntry::make('user.name')->label('Penguji'),
            TextEntry::make('tanggal_uji')->label('Tanggal Uji')->date(),
            TextEntry::make('nilai')->label('Nilai'),
            TextEntry::make('catatan')->label('Catatan')->placeholder('-')->columnSpanFull(),
        ]);
    }
}
