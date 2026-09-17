<?php

namespace App\Http\Controllers;

use App\Services\BackupCatalog;
use Illuminate\Support\Facades\Gate;

class BackupDownloadController extends Controller
{
    public function __invoke(string $name, BackupCatalog $catalog)
    {
        Gate::authorize('manage-backups');
        // Unduh langsung lewat HTTP, bukan menaruh arsip rahasia di public/storage.
        return response()->download($catalog->path($name), $name, ['Cache-Control' => 'private, no-store']);
    }
}
