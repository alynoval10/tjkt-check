<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\BackupOperationLock::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ikuti izin panel admin; saat ini aplikasi belum memisahkan peran akun.
        Gate::define('manage-backups', fn (User $user): bool => ! ($user instanceof FilamentUser)
            || $user->canAccessPanel(Filament::getPanel('admin')));
    }
}
