<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;

class BackupOperationLock
{
    private $handle = null;
    private bool $exclusive = false;

    public function beginRequest(): void
    {
        $this->open();
        if (! flock($this->handle, LOCK_SH | LOCK_NB)) {
            $this->release();
            abort(503, 'Backup atau pemulihan sedang berjalan. Coba beberapa saat lagi.');
        }
    }

    public function run(callable $callback): mixed
    {
        if ($this->exclusive) {
            return $callback();
        }
        $requestHeld = is_resource($this->handle);
        $this->open();
        if (! flock($this->handle, LOCK_EX | LOCK_NB)) {
            if ($requestHeld) { flock($this->handle, LOCK_SH); }
            else { $this->release(); }
            throw new RuntimeException('Masih ada aktivitas aplikasi. Tunggu sebentar lalu ulangi.');
        }
        $this->exclusive = true;
        try {
            return $callback();
        } finally {
            // Request tetap dikunci sampai middleware selesai menulis sesi.
            if (! $requestHeld) { $this->release(); }
        }
    }

    public function release(): void
    {
        if (is_resource($this->handle)) {
            flock($this->handle, LOCK_UN);
            fclose($this->handle);
        }
        $this->handle = null;
        $this->exclusive = false;
    }

    private function open(): void
    {
        if (! is_resource($this->handle)) {
            File::ensureDirectoryExists(storage_path('framework'));
            $this->handle = fopen(storage_path('framework/backup-operation.lock'), 'c');
            if (! $this->handle) { throw new RuntimeException('Tidak dapat mengunci operasi database.'); }
        }
    }
}
