<?php

namespace App\Filament\Resources\Siswas\Schemas;

use Filament\Infolists\Components\TextEntry;
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
            TextEntry::make('created_at')->dateTime()->placeholder('-'),
            TextEntry::make('updated_at')->dateTime()->placeholder('-'),
        ]);
    }
}
