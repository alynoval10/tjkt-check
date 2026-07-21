<?php

namespace App\Filament\Resources\Kelulusans\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KelulusanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('siswa_id')
                    ->required()
                    ->numeric(),
                TextInput::make('materi_id')
                    ->required()
                    ->numeric(),
                TextInput::make('penguji')
                    ->required(),
                DatePicker::make('tanggal_uji')
                    ->required(),
            ]);
    }
}
