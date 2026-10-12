<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class PengaturanIdentitas extends Page
{
    protected string $view = 'filament.pages.pengaturan-identitas';

    protected static ?string $title = 'Pengaturan Identitas';

    protected static ?string $navigationLabel = 'Pengaturan Identitas';

    protected static string|UnitEnum|null $navigationGroup = 'Pemeliharaan';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 20;

    protected function getHeaderActions(): array
    {
        return [$this->editIdentityAction()];
    }

    public function editIdentityAction(): Action
    {
        return Action::make('editIdentity')
            ->label('Ubah Identitas')
            ->icon('heroicon-o-pencil-square')
            ->fillForm(fn (): array => [
                'title' => SiteSetting::value('site_title', 'TJKT CHECK'),
                'logo' => SiteSetting::value('site_logo'),
            ])
            ->schema([
                TextInput::make('title')
                    ->label('Judul aplikasi')
                    ->required()
                    ->maxLength(80),
                FileUpload::make('logo')
                    ->label('Logo')
                    ->image()
                    ->disk('public')
                    ->directory('site-settings')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->nullable()
                    ->helperText('Opsional, maksimal 2 MB. Hapus logo untuk menampilkan inisial judul.'),
            ])
            ->modalHeading('Ubah identitas aplikasi')
            ->modalSubmitActionLabel('Simpan')
            ->action(function (array $data): void {
                $oldLogo = SiteSetting::value('site_logo');
                $newLogo = $data['logo'] ?: null;

                SiteSetting::set('site_title', trim($data['title']));
                SiteSetting::set('site_logo', $newLogo);

                if ($oldLogo && $oldLogo !== $newLogo && str_starts_with($oldLogo, 'site-settings/')) {
                    Storage::disk('public')->delete($oldLogo);
                }

                Notification::make()->title('Identitas aplikasi diperbarui')->success()->send();
            });
    }
}
