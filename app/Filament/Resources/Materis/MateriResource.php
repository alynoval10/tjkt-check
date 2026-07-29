<?php

namespace App\Filament\Resources\Materis;

use App\Filament\Resources\Materis\Pages\CreateMateri;
use App\Filament\Resources\Materis\Pages\EditMateri;
use App\Filament\Resources\Materis\Pages\ListMateris;
use App\Filament\Resources\Materis\Pages\ViewMateri;
use App\Filament\Resources\Materis\Schemas\MateriForm;
use App\Filament\Resources\Materis\Schemas\MateriInfolist;
use App\Filament\Resources\Materis\Tables\MaterisTable;
use App\Models\Materi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;

class MateriResource extends Resource
{
    protected static ?string $model = Materi::class;
    protected static ?string $navigationLabel = 'Materi';

protected static ?string $modelLabel = 'Materi';

protected static ?string $pluralModelLabel = 'Materi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama';

    public static function getNavigationLabel(): string
{
    return 'Materi';
}

public static function getModelLabel(): string
{
    return 'Materi';
}

public static function getPluralModelLabel(): string
{
    return 'Materi';
}

    public static function form(Schema $schema): Schema
    {
        return MateriForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MateriInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode')->label('Kode')->searchable()->sortable(),
                TextColumn::make('nama')->label('Nama Materi')->searchable()->sortable(),
                TextColumn::make('tingkat')
    ->label('Tingkat')
    ->badge()
    ->icon(fn (string $state): string => match ($state) {
        'X'   => 'heroicon-m-academic-cap',
        'XI'  => 'heroicon-m-book-open',
        'XII' => 'heroicon-m-trophy',
        default => 'heroicon-m-tag',
    })
    ->color(fn (string $state): string => match ($state) {
        'X'   => 'success',
        'XI'  => 'info',
        'XII' => 'warning',
        default => 'gray',
    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMateris::route('/'),
            'create' => CreateMateri::route('/create'),
            'view' => ViewMateri::route('/{record}'),
            'edit' => EditMateri::route('/{record}/edit'),
        ];
    }
}
