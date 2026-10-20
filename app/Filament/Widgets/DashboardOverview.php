<?php

namespace App\Filament\Widgets;

use App\Support\DashboardKompetensi;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

class DashboardOverview extends Widget
{
    use InteractsWithPageFilters;

    protected string $view = 'filament.widgets.dashboard-overview';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected function getViewData(): array
    {
        $kelasFilter = $this->pageFilters['kelas_id'] ?? null;

        return app(DashboardKompetensi::class)->ringkasan(
            is_numeric($kelasFilter) ? (int) $kelasFilter : null,
        );
    }
}
