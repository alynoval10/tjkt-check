<?php

namespace App\Filament\Resources\Siswas;

use App\Filament\Resources\Siswas\Pages\CreateSiswa;
use App\Filament\Resources\Siswas\Pages\EditSiswa;
use App\Filament\Resources\Siswas\Pages\ListSiswas;
use App\Filament\Resources\Siswas\Pages\ViewSiswa;
use App\Filament\Resources\Siswas\Schemas\SiswaForm;
use App\Filament\Resources\Siswas\Schemas\SiswaInfolist;
use App\Filament\Resources\Siswas\Tables\SiswasTable;
use App\Models\Siswa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SiswaExport;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\FileUpload;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class SiswaResource extends Resource
{
    protected static ?string $model = Siswa::class;
    protected static ?string $navigationLabel = 'Siswa';

    protected static ?string $modelLabel = 'Siswa';

    protected static ?string $pluralModelLabel = 'Siswa';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama';

    public static function getNavigationLabel(): string
{
    return 'Siswa';
}

public static function getModelLabel(): string
{
    return 'Siswa';
}

public static function getPluralModelLabel(): string
{
    return 'Siswa';
}

    public static function form(Schema $schema): Schema
    {
        return SiswaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SiswaInfolist::configure($schema);
    }

  public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('nis')
                ->label('NIS')
                ->searchable()
                ->sortable(),

            TextColumn::make('nama')
                ->label('Nama Siswa')
                ->searchable()
                ->sortable(),

            TextColumn::make('kelas')
                ->label('Kelas')
                ->searchable()
                ->sortable(),
        ])

        ->headerActions([
              Action::make('downloadTemplate')
        ->label('Download Template')
        ->icon('heroicon-o-document-arrow-down')
        ->url(route('siswa.template'))
        ->openUrlInNewTab(),




            Action::make('import')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('file')
                        ->label('File Excel')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ]),
                ])
                ->action(function (array $data) {
                    Excel::import(
                        new \App\Imports\SiswaImport(),
                        $data['file']
                    );
                }),

            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    return Excel::download(
                        new \App\Exports\SiswaExport(),
                        'data-siswa.xlsx'
                    );
                }),
        ])

        ->actions([
            \Filament\Actions\EditAction::make(),
            \Filament\Actions\DeleteAction::make(),
        ])

        ->bulkActions([
            \Filament\Actions\BulkActionGroup::make([
                \Filament\Actions\DeleteBulkAction::make(),
            ]),
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
            'index' => ListSiswas::route('/'),
            'create' => CreateSiswa::route('/create'),
            'view' => ViewSiswa::route('/{record}'),
            'edit' => EditSiswa::route('/{record}/edit'),
        ];
    }
}
