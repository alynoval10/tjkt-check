<?php

namespace App\Filament\Resources\Materis\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MateriForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('kode')->label('Kode Materi')->required(),
            TextInput::make('nama')->label('Nama Materi')->required(),
            Select::make('tingkat')
                ->label('Untuk Tingkat')
                ->options(['X' => 'Kelas X', 'XI' => 'Kelas XI', 'XII' => 'Kelas XII'])
                ->required(),
            Textarea::make('deskripsi')->label('Deskripsi')->columnSpanFull(),
        ]);
    }
}
