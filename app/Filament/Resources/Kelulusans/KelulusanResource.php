<?php

namespace App\Filament\Resources\Kelulusans;

use App\Filament\Resources\Kelulusans\Pages\CreateKelulusan;
use App\Filament\Resources\Kelulusans\Pages\EditKelulusan;
use App\Filament\Resources\Kelulusans\Pages\ListKelulusans;
use App\Filament\Resources\Kelulusans\Pages\ViewKelulusan;
use App\Filament\Resources\Kelulusans\Schemas\KelulusanForm;
use App\Filament\Resources\Kelulusans\Schemas\KelulusanInfolist;
use App\Filament\Resources\Kelulusans\Tables\KelulusansTable;
use App\Models\Kelulusan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;

class KelulusanResource extends Resource
{
    protected static ?string $model = Kelulusan::class;
    protected static ?string $navigationLabel = 'Kelulusan';

    protected static ?string $modelLabel = 'Kelulusan';
    
    protected static ?string $pluralModelLabel = 'Kelulusan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
{
    return $schema
        ->components([

            Select::make('siswa_id')
                ->label('Siswa')
                ->relationship('siswa','nama')
                ->searchable()
                ->required(),


            Select::make('materi_id')
                ->label('Materi')
                ->relationship('materi','nama')
                ->searchable()
                ->required(),


            Select::make('user_id')
    ->label('Penguji')
    ->relationship('user','name')
    ->default(auth()->id())
    ->required()
    ->dehydrated(),


            DatePicker::make('tanggal_uji')
                ->default(now())
                ->required(),

        ]);
}

    public static function infolist(Schema $schema): Schema
    {
        return KelulusanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KelulusansTable::configure($table);
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
            'index' => ListKelulusans::route('/'),
            'create' => CreateKelulusan::route('/create'),
            'view' => ViewKelulusan::route('/{record}'),
            'edit' => EditKelulusan::route('/{record}/edit'),
        ];
    }
}
