<?php

namespace App\Filament\Resources\Kelas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class KelasInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('tingkat')->label('Tingkat'),
            TextEntry::make('nama')->label('Nama Kelas'),
            TextEntry::make('siswas_count')->label('Jumlah Siswa')->state(fn ($record) => $record->siswas()->count()),
        ]);
    }
}
