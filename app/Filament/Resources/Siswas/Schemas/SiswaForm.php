<?php

namespace App\Filament\Resources\Siswas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SiswaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nis')->label('NIS')->required()->unique(ignoreRecord: true),
            TextInput::make('nama')->label('Nama Siswa')->required(),
            Select::make('kelas_id')
                ->label('Kelas')
                ->relationship('rombel', 'nama', modifyQueryUsing: function ($query, $record) {
                    return $query->where(function ($query) use ($record): void {
                        $query->where('is_active', true);

                        if ($record?->kelas_id) {
                            $query->orWhereKey($record->kelas_id);
                        }
                    });
                })
                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nama} ({$record->tingkat})")
                ->searchable()
                ->preload()
                ->required(),
        ]);
    }
}
