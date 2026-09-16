<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

class BackupCreate extends Command
{
    protected $signature = 'backup:create';

    protected $description = 'Membuat backup database SQLite dan file PDF materi';

    public function handle(): int
    {
        $backupDirectory = storage_path('app/backups');

        if (! File::exists($backupDirectory)) {
            File::makeDirectory($backupDirectory, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');

        $zipPath = $backupDirectory . '/tjkt-check-backup-' . $timestamp . '.zip';

        $databasePath = database_path('database.sqlite');
        $materiPath = storage_path('app/public/materi');

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error('Gagal membuat file backup.');
            return self::FAILURE;
        }

        // Backup database
        if (File::exists($databasePath)) {
            $zip->addFile($databasePath, 'database/database.sqlite');
        } else {
            $this->warn('Database SQLite tidak ditemukan.');
        }

        // Backup PDF materi
        if (File::isDirectory($materiPath)) {
            $files = File::allFiles($materiPath);

            foreach ($files as $file) {
                $relativePath = $file->getRelativePathname();

                $zip->addFile(
                    $file->getRealPath(),
                    'materi/' . $relativePath
                );
            }

            $this->info('PDF materi: ' . count($files) . ' file');
        } else {
            $this->warn('Folder materi tidak ditemukan.');
        }

        $zip->close();

        $this->info('Backup berhasil dibuat.');
        $this->info('File: ' . $zipPath);

        return self::SUCCESS;
    }
}