<?php

namespace App\Filament\Resources\Materis\Pages;

use App\Filament\Resources\Materis\MateriResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMateri extends ViewRecord
{
    protected static string $resource = MateriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
