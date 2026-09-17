<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use App\Services\BackupCatalog;
use Illuminate\Console\Command;

class BackupRestoreTest extends Command
{
    protected $signature = 'backup:restore-test {archive : Path lengkap ZIP backup}';
    protected $description = 'Uji restore di folder terpisah tanpa menimpa data aktif';

    public function handle(BackupService $backup, BackupCatalog $catalog): int
    {
        try {
            $archive = realpath($this->argument('archive'));
            // Backup lokal memakai laporan yang sama dengan menu admin.
            if ($archive && dirname($archive) === realpath(storage_path('app/backups'))) {
                $result = $catalog->verify(basename($archive));
                if ($result['status'] !== 'passed') {
                    $this->error($result['message']);
                    return self::FAILURE;
                }
            } else {
                $result = $backup->restoreTest($this->argument('archive'));
            }
            $this->info('Uji restore berhasil. Database aktif tidak diganti.');
            $this->line('Hasil: '.$result['directory']);
            $this->line('File terverifikasi: '.$result['files']);
            $this->table(['Tabel', 'Jumlah baris'], collect($result['tables'])->map(fn ($count, $name) => [$name, $count])->values()->all());
            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }
    }
}
