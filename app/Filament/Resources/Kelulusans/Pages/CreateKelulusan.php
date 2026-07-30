<?php

namespace App\Filament\Resources\Kelulusans\Pages;

use App\Filament\Resources\Kelulusans\KelulusanResource;
use App\Models\Kelulusan;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateKelulusan extends CreateRecord
{
    protected static string $resource = KelulusanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $cek = Kelulusan::where('siswa_id', $data['siswa_id'])
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

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}