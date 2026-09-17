<?php

namespace App\Filament\Resources\Kelulusans;

use App\Filament\Resources\Kelulusans\Pages\CreateKelulusan;
use App\Filament\Resources\Kelulusans\Pages\EditKelulusan;
use App\Filament\Resources\Kelulusans\Pages\ListKelulusans;
use App\Filament\Resources\Kelulusans\Pages\ViewKelulusan;
use App\Filament\Resources\Kelulusans\Schemas\KelulusanInfolist;
use App\Filament\Resources\Kelulusans\Tables\KelulusansTable;
use App\Models\Kelas;
use App\Models\Kelulusan;
use App\Models\Materi;
use App\Models\PeriodeAkademik;
use App\Models\Siswa;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KelulusanResource extends Resource
{
    /*
    |--------------------------------------------------------------------------
    | Model
    |--------------------------------------------------------------------------
    | Resource ini menggunakan model Kelulusan.
    */

    protected static ?string $model = Kelulusan::class;

    /*
    |--------------------------------------------------------------------------
    | Navigasi Sidebar
    |--------------------------------------------------------------------------
    | Kelulusan ditempatkan pada grup Penilaian.
    */

    protected static string|UnitEnum|null $navigationGroup = 'Penilaian';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Kelulusan';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedCheckBadge;

    /*
    |--------------------------------------------------------------------------
    | Label Resource
    |--------------------------------------------------------------------------
    */

    protected static ?string $modelLabel = 'Kelulusan';

    protected static ?string $pluralModelLabel = 'Kelulusan';

    protected static ?string $recordTitleAttribute = 'id';

    /*
    |--------------------------------------------------------------------------
    | Form Kelulusan
    |--------------------------------------------------------------------------
    */

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | Periode Akademik Aktif
                |--------------------------------------------------------------------------
                | Penilaian baru otomatis menggunakan periode aktif.
                | Field ini tidak perlu ditampilkan kepada guru.
                */

                Hidden::make('periode_akademik_id')
                    ->default(
                        fn () => PeriodeAkademik::aktif()?->id
                    ),

                /*
                |--------------------------------------------------------------------------
                | Kelas
                |--------------------------------------------------------------------------
                | Kelas hanya digunakan sebagai filter siswa.
                | Tidak disimpan pada tabel kelulusans.
                */

                Select::make('kelas_id')
                    ->label('Kelas')
                    ->options(
                        fn () => Kelas::query()
                            ->orderBy('tingkat')
                            ->orderBy('nama')
                            ->pluck('nama', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->live()
                    ->dehydrated(false)
                    ->afterStateUpdated(function (Set $set): void {
                        /*
                         * Saat kelas berubah, siswa dan materi harus direset
                         * agar tidak membawa pilihan dari kelas sebelumnya.
                         */
                        $set('siswa_id', null);
                        $set('materi_id', null);
                    })
                    ->required(),

                /*
                |--------------------------------------------------------------------------
                | Siswa
                |--------------------------------------------------------------------------
                | Hanya menampilkan siswa dari kelas yang dipilih.
                */

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
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set): void {
                        /*
                         * Saat siswa berubah, materi harus dipilih ulang.
                         */
                        $set('materi_id', null);
                    })
                    ->required(),

                /*
                |--------------------------------------------------------------------------
                | Materi
                |--------------------------------------------------------------------------
                | Materi mengikuti tingkat siswa.
                |
                | Untuk periode pertama:
                | - data pada periode aktif dianggap sudah dinilai
                | - data lama dengan periode_akademik_id NULL juga dianggap
                |   sudah dinilai agar tidak muncul kembali
                |
                | Pada periode akademik berikutnya, materi lama dapat dinilai
                | kembali karena memiliki periode berbeda.
                */

                Select::make('materi_id')
                    ->label('Materi')
                    ->options(function (Get $get, ?Kelulusan $record) {
                        $siswaId = $get('siswa_id');

                        /*
                         * Jika siswa belum dipilih, tidak ada materi
                         * yang dapat ditampilkan.
                         */
                        if (! $siswaId) {
                            return [];
                        }

                        /*
                         * Ambil siswa sekaligus relasi rombel/kelasnya.
                         */
                        $siswa = Siswa::with('rombel')->find($siswaId);

                        if (! $siswa?->rombel) {
                            return [];
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Tentukan Periode Penilaian
                        |--------------------------------------------------------------------------
                        | Saat edit, gunakan periode milik record.
                        | Saat create, gunakan periode aktif.
                        */

                        $periodeId = $record?->periode_akademik_id
                            ?? PeriodeAkademik::aktif()?->id;

                        /*
                        |--------------------------------------------------------------------------
                        | Materi yang Sudah Dinilai
                        |--------------------------------------------------------------------------
                        | Data lama NULL ikut dianggap sudah dinilai agar sistem
                        | tetap berperilaku seperti sebelum fitur periode dibuat.
                        */

                        $sudahDinilai = Kelulusan::query()
                            ->where('siswa_id', $siswaId)
                            ->where(function ($query) use ($periodeId): void {
                                /*
                                 * Jika ada periode aktif, ambil data pada
                                 * periode tersebut.
                                 */
                                if ($periodeId) {
                                    $query->where(
                                        'periode_akademik_id',
                                        $periodeId
                                    );
                                }

                                /*
                                 * Data lama sebelum sistem periode
                                 * diperkenalkan.
                                 */
                                $query->orWhereNull(
                                    'periode_akademik_id'
                                );
                            })
                            ->when(
                                $record,
                                fn ($query) =>
                                    $query->whereKeyNot($record->getKey())
                            )
                            ->pluck('materi_id');

                        /*
                        |--------------------------------------------------------------------------
                        | Tampilkan Materi yang Belum Dinilai
                        |--------------------------------------------------------------------------
                        */

                        return Materi::query()
                            ->where(
                                'tingkat',
                                $siswa->rombel->tingkat
                            )
                            ->whereNotIn(
                                'id',
                                $sudahDinilai
                            )
                            ->orderBy('nama')
                            ->pluck('nama', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->required(),

                /*
                |--------------------------------------------------------------------------
                | Penguji
                |--------------------------------------------------------------------------
                | Default menggunakan user yang sedang login.
                */

                Select::make('user_id')
                    ->label('Penguji')
                    ->relationship('user', 'name')
                    ->default(auth()->id())
                    ->searchable()
                    ->preload()
                    ->required(),

                /*
                |--------------------------------------------------------------------------
                | Tanggal Uji
                |--------------------------------------------------------------------------
                */

                DatePicker::make('tanggal_uji')
                    ->label('Tanggal Uji')
                    ->default(now())
                    ->required(),

                /*
                |--------------------------------------------------------------------------
                | Nilai
                |--------------------------------------------------------------------------
                | Nilai valid antara 0 sampai 100.
                */

                TextInput::make('nilai')
                    ->label('Nilai')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(),

                /*
                |--------------------------------------------------------------------------
                | Saran Catatan
                |--------------------------------------------------------------------------
                | Hanya sebagai shortcut untuk mengisi field catatan.
                | Nilai saran tidak disimpan sebagai kolom tersendiri.
                */

                ToggleButtons::make('saran_catatan')
                    ->label('Saran Catatan')
                    ->options([
                        'Kompetensi sudah dikuasai dengan baik.' =>
                            'Kompetensi sudah dikuasai dengan baik.',

                        'Mampu menyelesaikan praktik secara mandiri.' =>
                            'Mampu menyelesaikan praktik secara mandiri.',

                        'Perlu meningkatkan ketelitian dalam praktik.' =>
                            'Perlu meningkatkan ketelitian dalam praktik.',

                        'Perlu latihan kembali pada bagian konfigurasi.' =>
                            'Perlu latihan kembali pada bagian konfigurasi.',

                        'Perlu bimbingan dan penguatan materi.' =>
                            'Perlu bimbingan dan penguatan materi.',

                        'Belum mampu menyelesaikan praktik secara mandiri.' =>
                            'Belum mampu menyelesaikan praktik secara mandiri.',
                    ])
                    ->inline()
                    ->dehydrated(false)
                    ->live()
                    ->afterStateUpdated(
                        function ($state, Set $set): void {
                            if ($state) {
                                $set('catatan', $state);
                            }
                        }
                    )
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | Catatan
                |--------------------------------------------------------------------------
                | Bisa berasal dari saran di atas atau ditulis manual.
                */

                Textarea::make('catatan')
                    ->label('Catatan')
                    ->placeholder(
                        'Pilih saran di atas atau tulis catatan secara manual...'
                    )
                    ->rows(3)
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Infolist
    |--------------------------------------------------------------------------
    */

    public static function infolist(Schema $schema): Schema
    {
        return KelulusanInfolist::configure($schema);
    }

    /*
    |--------------------------------------------------------------------------
    | Tabel
    |--------------------------------------------------------------------------
    */

    public static function table(Table $table): Table
    {
        return KelulusansTable::configure($table);
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */

    public static function getRelations(): array
    {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Halaman Resource
    |--------------------------------------------------------------------------
    */

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