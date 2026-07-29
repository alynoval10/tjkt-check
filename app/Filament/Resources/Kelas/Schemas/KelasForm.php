<?php

namespace App\Filament\Resources\Kelas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KelasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('tingkat')
                ->label('Tingkat')
                ->options(['X' => 'X', 'XI' => 'XI', 'XII' => 'XII'])
                ->required(),
            TextInput::make('nama')
                ->label('Nama Kelas')
                ->placeholder('Contoh: X TKJ 1')
                ->required()
                ->unique(ignoreRecord: true),
        ]);
    }
}
