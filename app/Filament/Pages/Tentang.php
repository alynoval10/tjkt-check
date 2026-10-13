<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Tentang extends Page
{
    protected string $view = 'filament.pages.tentang';

    protected static ?string $title = 'Tentang Aplikasi';

    protected static ?string $navigationLabel = 'Tentang';

    protected static string|UnitEnum|null $navigationGroup = 'Pemeliharaan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInformationCircle;

    protected static ?int $navigationSort = 99;

    protected function getViewData(): array
    {
        return [
            'appTitle' => SiteSetting::value('site_title', 'TJKT CHECK'),
            'appLogo' => SiteSetting::value('site_logo'),
            'appVersion' => config('app.version', '1.0.0'),
        ];
    }
}
