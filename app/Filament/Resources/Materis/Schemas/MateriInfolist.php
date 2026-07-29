<?php

namespace App\Filament\Resources\Materis\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class MateriInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('kode')
                ->label('Kode Materi')
                ->placeholder('-'),

            TextEntry::make('nama')
                ->label('Nama Materi'),

            TextEntry::make('tingkat')
                ->label('Tingkat')
                ->placeholder('Belum diatur'),

            TextEntry::make('deskripsi')
                ->label('Deskripsi')
                ->placeholder('-')
                ->columnSpanFull(),

            TextEntry::make('pdf_file')
                ->label('File PDF')
                ->formatStateUsing(
                    fn (?string $state): string => $state
                        ? basename($state)
                        : 'Belum ada PDF'
                )
                ->url(
                    fn ($record): ?string => $record->pdf_file
                        ? Storage::disk('public')->url($record->pdf_file)
                        : null
                )
                ->openUrlInNewTab()
                ->columnSpanFull(),

            TextEntry::make('created_at')
                ->label('Dibuat')
                ->dateTime()
                ->placeholder('-'),

            TextEntry::make('updated_at')
                ->label('Diperbarui')
                ->dateTime()
                ->placeholder('-'),
        ]);
    }
}