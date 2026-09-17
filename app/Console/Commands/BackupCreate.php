<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;

class BackupCreate extends Command
{
    protected $signature = 'backup:create {--include-env : Sertakan konfigurasi rahasia .env}';

    protected $description = 'Membuat backup database SQLite dan file PDF materi';

    public function handle(BackupService $backup): int
    {
        // Hentikan aktivitas web sebelum mengambil database dan unggahan bersama.
        if (! app()->isDownForMaintenance()) {
            $this->error('Jalankan php artisan down terlebih dahulu dan hentikan worker penulis data.');
            return self::FAILURE;
        }
        try {
            $this->info('Backup berhasil: '.$backup->create((bool) $this->option('include-env')));
            $this->line('Simpan salinan di luar server dan jalankan backup:restore-test.');
            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }
    }
}
