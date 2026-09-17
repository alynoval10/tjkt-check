<?php

namespace App\Http\Middleware;

use App\Services\BackupOperationLock;
use Closure;
use Illuminate\Http\Request;

class BackupRequestLock
{
    public function handle(Request $request, Closure $next)
    {
        // Cakup seluruh request, termasuk penyimpanan sesi setelah controller selesai.
        $lock = app(BackupOperationLock::class);
        $lock->beginRequest();
        try { return $next($request); }
        finally { $lock->release(); }
    }
}
