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
        return app(DashboardKompetensi::class)->ringkasan(
            isset($this->pageFilters['kelas_id']) && $this->pageFilters['kelas_id'] !== ''
                ? (int) $this->pageFilters['kelas_id']
                : null,
        );
    }
}
