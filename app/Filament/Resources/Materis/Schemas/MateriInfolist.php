<?php

namespace App\Filament\Resources\Materis\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MateriInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('kode')
                    ->placeholder('-'),
                TextEntry::make('nama'),
                TextEntry::make('deskripsi')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
