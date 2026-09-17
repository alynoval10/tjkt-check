<?php

namespace App\Filament\Resources\Kelulusans\Pages;

use App\Filament\Resources\Kelulusans\KelulusanResource;
use App\Models\Kelulusan;
use App\Models\PeriodeAkademik;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateKelulusan extends CreateRecord
{
    /*
    |--------------------------------------------------------------------------
    | Resource
    |--------------------------------------------------------------------------
    */

    protected static string $resource = KelulusanResource::class;

    /*
    |--------------------------------------------------------------------------
    | Persiapan Data Sebelum Create
    |--------------------------------------------------------------------------
    */

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /*
        |--------------------------------------------------------------------------
        | Periode Akademik Aktif
        |--------------------------------------------------------------------------
        */

        $periode = PeriodeAkademik::aktif();

        if (! $periode) {
            Notification::make()
                ->title('Periode akademik belum aktif')
                ->body(
                    'Aktifkan tahun ajaran dan semester terlebih dahulu.'
                )
                ->danger()
                ->send();

            $this->halt();
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan Periode Aktif
        |--------------------------------------------------------------------------
        */

        $data['periode_akademik_id'] =
            $periode->id;

        /*
        |--------------------------------------------------------------------------
        | Cek Duplikat
        |--------------------------------------------------------------------------
        | Untuk periode awal, data lama NULL juga dianggap sebagai data yang
        | sudah dinilai agar tidak menghasilkan penilaian ganda.
        */

        $sudahAda = Kelulusan::query()
            ->where(
                'siswa_id',
                $data['siswa_id']
            )
            ->where(
                'materi_id',
                $data['materi_id']
            )
            ->where(function ($query) use ($periode) {
                $query
                    ->where(
                        'periode_akademik_id',
                        $periode->id
                    )
                    ->orWhereNull(
                        'periode_akademik_id'
                    );
            })
            ->exists();

        /*
        |--------------------------------------------------------------------------
        | Hentikan Jika Sudah Dinilai
        |--------------------------------------------------------------------------
        */

        if ($sudahAda) {
            Notification::make()
                ->title('Penilaian sudah ada')
                ->body(
                    'Materi ini sudah pernah dinilai untuk siswa tersebut pada periode awal/aktif.'
                )
                ->warning()
                ->send();

            $this->halt();
        }

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Redirect Setelah Simpan
    |--------------------------------------------------------------------------
    */

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}