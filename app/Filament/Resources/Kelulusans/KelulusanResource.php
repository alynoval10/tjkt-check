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
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Materi;

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
        return $schema->components([
            Select::make('kelas_id')
                ->label('Kelas')
                ->options(fn () => Kelas::query()->orderBy('tingkat')->orderBy('nama')->pluck('nama', 'id'))
                ->searchable()
                ->preload()
                ->live()
                ->dehydrated(false)
                ->afterStateUpdated(function (Set $set): void {
                    $set('siswa_id', null);
                    $set('materi_id', null);
                })
                ->required(),

            Select::make('siswa_id')
                ->label('Siswa')
                ->options(function (Get $get) {
                    $kelasId = $get('kelas_id');

                    if (! $kelasId) {
                        return [];
                    }

                    return Siswa::query()
                        ->where('kelas_id', $kelasId)
                        ->orderBy('nama')
                        ->pluck('nama', 'id');
                })
                ->searchable()
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('materi_id', null))
                ->required(),

            Select::make('materi_id')
                ->label('Materi')
                ->options(function (Get $get, ?Kelulusan $record) {
                    $siswaId = $get('siswa_id');

                    if (! $siswaId) {
                        return [];
                    }

                    $siswa = Siswa::with('rombel')->find($siswaId);

                    if (! $siswa?->rombel) {
                        return [];
                    }

                    $sudahDinilai = Kelulusan::query()
                        ->where('siswa_id', $siswaId)
                        ->when($record, fn ($query) => $query->whereKeyNot($record->getKey()))
                        ->pluck('materi_id');

                    return Materi::query()
                        ->where('tingkat', $siswa->rombel->tingkat)
                        ->whereNotIn('id', $sudahDinilai)
                        ->orderBy('nama')
                        ->pluck('nama', 'id');
                })
                ->searchable()
                ->required(),

            Select::make('user_id')
                ->label('Penguji')
                ->relationship('user', 'name')
                ->default(auth()->id())
                ->required(),

            DatePicker::make('tanggal_uji')
                ->label('Tanggal Uji')
                ->default(now())
                ->required(),

            TextInput::make('nilai')
                ->label('Nilai')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->required(),

            Textarea::make('catatan')
                ->label('Catatan')
                ->rows(3)
                ->columnSpanFull(),
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
