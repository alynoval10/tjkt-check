<?php
namespace App\Filament\Resources\Kelas\Pages;
use App\Filament\Resources\Kelas\KelasResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
class ViewKelas extends ViewRecord { protected static string $resource = KelasResource::class; protected function getHeaderActions(): array { return [EditAction::make()]; } }
