<?php

namespace App\Filament\Resources\Materis\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MateriForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('kode')
                ->label('Kode Materi')
                ->required()
                ->maxLength(255),

            TextInput::make('nama')
                ->label('Nama Materi')
                ->required()
                ->maxLength(255),

            Select::make('tingkat')
                ->label('Untuk Tingkat')
                ->options([
                    'X' => 'Kelas X',
                    'XI' => 'Kelas XI',
                    'XII' => 'Kelas XII',
                ])
                ->required(),

            Textarea::make('deskripsi')
                ->label('Deskripsi')
                ->rows(4)
                ->columnSpanFull(),

            FileUpload::make('pdf_file')
                ->label('File Materi PDF')
                ->helperText('Hanya file PDF, maksimal 10 MB.')
                ->disk('public')
                ->directory('materi')
                ->acceptedFileTypes([
                    'application/pdf',
                ])
                ->maxSize(10240)
                ->openable()
                ->downloadable()
                ->preserveFilenames()
                ->columnSpanFull(),
        ]);
    }
}