<?php

namespace App\Filament\Resources\Kelulusans\Pages;

use App\Filament\Resources\Kelulusans\KelulusanResource;
use App\Models\Siswa;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditKelulusan extends EditRecord
{
    protected static string $resource = KelulusanResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['kelas_id'] = Siswa::find($data['siswa_id'] ?? null)?->kelas_id;
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [ViewAction::make(), DeleteAction::make()];
    }
}
