<?php

namespace App\Filament\Resources\Materis\Pages;

use App\Filament\Resources\Materis\MateriResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMateri extends CreateRecord
{
    protected static string $resource = MateriResource::class;

    protected function getRedirectUrl(): string
{
    return $this->getResource()::getUrl('index');
}
}
