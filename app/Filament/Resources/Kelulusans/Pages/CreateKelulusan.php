<?php

namespace App\Filament\Resources\Kelulusans\Pages;

use App\Filament\Resources\Kelulusans\KelulusanResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateKelulusan extends CreateRecord
{
    protected static string $resource = KelulusanResource::class;


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $cek = \App\Models\Kelulusan::where('siswa_id', $data['siswa_id'])
            ->where('materi_id', $data['materi_id'])
            ->exists();


        if ($cek) {

            Notification::make()
                ->title('Gagal menyimpan')
                ->body('Siswa tersebut sudah memiliki kelulusan materi ini.')
                ->danger()
                ->send();


            $this->halt();

        }


        return $data;
    }
}