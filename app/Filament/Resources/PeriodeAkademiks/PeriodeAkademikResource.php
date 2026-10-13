<?php

namespace App\Filament\Resources\PeriodeAkademiks;

use App\Filament\Resources\PeriodeAkademiks\Pages\CreatePeriodeAkademik;
use App\Filament\Resources\PeriodeAkademiks\Pages\EditPeriodeAkademik;
use App\Filament\Resources\PeriodeAkademiks\Pages\ListPeriodeAkademiks;
use App\Models\PeriodeAkademik;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class PeriodeAkademikResource extends Resource
{
    protected static ?string $model = PeriodeAkademik::class;
    protected static string|UnitEnum|null $navigationGroup = 'Data Akademik';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Periode Akademik';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;
    protected static ?string $modelLabel = 'Periode Akademik';
    protected static ?string $pluralModelLabel = 'Periode Akademik';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('tahun_ajaran')
                ->label('Tahun Ajaran')
                ->placeholder('2026/2027')
                ->required()
                ->maxLength(20),
            Select::make('semester')
                ->options(['Ganjil' => 'Ganjil', 'Genap' => 'Genap'])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tahun_ajaran')->label('Tahun Ajaran')->sortable(),
                TextColumn::make('semester')->label('Semester')->badge(),
                IconColumn::make('is_active')->label('Status')->boolean(),
            ])
            ->recordActions([
                Action::make('activate')
                    ->label('Aktifkan')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (PeriodeAkademik $record): bool => ! $record->is_active)
                    ->requiresConfirmation()
                    ->action(function (PeriodeAkademik $record): void {
                        DB::transaction(function () use ($record): void {
                            PeriodeAkademik::query()->where('is_active', true)->update(['is_active' => false]);
                            $record->update(['is_active' => true]);
                        });
                    }),
                Action::make('assignLegacyAssessments')
                    ->label('Masukkan kelulusan lama')
                    ->icon(Heroicon::OutlinedArrowRightCircle)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Masukkan kelulusan lama ke periode ini?')
                    ->modalDescription('Penilaian lama tanpa periode akan dipindahkan. Nilai dan catatan tetap sama. Data yang sudah memiliki penilaian pada periode ini akan dilewati.')
                    ->action(function (PeriodeAkademik $record): void {
                        $moved = 0;
                        $skipped = 0;

                        DB::transaction(function () use ($record, &$moved, &$skipped): void {
                            \App\Models\Kelulusan::query()
                                ->whereNull('periode_akademik_id')
                                ->orderBy('id')
                                ->chunkById(200, function ($kelulusans) use ($record, &$moved, &$skipped): void {
                                    foreach ($kelulusans as $kelulusan) {
                                        $alreadyExists = \App\Models\Kelulusan::query()
                                            ->where('periode_akademik_id', $record->id)
                                            ->where('siswa_id', $kelulusan->siswa_id)
                                            ->where('materi_id', $kelulusan->materi_id)
                                            ->exists();

                                        if ($alreadyExists) {
                                            $skipped++;
                                            continue;
                                        }

                                        $kelulusan->update(['periode_akademik_id' => $record->id]);
                                        $moved++;
                                    }
                                });
                        });

                        Notification::make()
                            ->title('Kelulusan lama diproses')
                            ->body("{$moved} data dimasukkan ke {$record->tahun_ajaran} — Semester {$record->semester}; {$skipped} data dilewati karena sudah ada penilaian pada periode tersebut.")
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPeriodeAkademiks::route('/'),
            'create' => CreatePeriodeAkademik::route('/create'),
            'edit' => EditPeriodeAkademik::route('/{record}/edit'),
        ];
    }
}
