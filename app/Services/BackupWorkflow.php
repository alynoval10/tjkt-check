<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use SQLite3;

class BackupWorkflow
{
    public function create(): string
    {
        return app(BackupOperationLock::class)->run(fn () => app(BackupService::class)->create());
    }

    public function upload(string $temporaryPath): string
    {
        return app(BackupOperationLock::class)->run(function () use ($temporaryPath) {
            $root = storage_path('app/backups');
            File::ensureDirectoryExists($root, 0700);
            $name = 'tjkt-check-upload-'.now()->format('Y-m-d_H-i-s').'-'.Str::uuid().'.zip';
            $path = $root.'/'.$name;
            // Nama asli unggahan tidak pernah menjadi path di server.
            if (! copy($temporaryPath, $path.'.partial')) { throw new RuntimeException('Upload gagal disalin.'); }
            chmod($path.'.partial', 0600);
            try {
                app(BackupService::class)->restoreTest($path.'.partial');
                if (! rename($path.'.partial', $path)) { throw new RuntimeException('Upload gagal diselesaikan.'); }
                app(BackupCatalog::class)->verify($name);
                return $name;
            } finally {
                if (is_file($path.'.partial')) { unlink($path.'.partial'); }
            }
        });
    }

    public function restore(string $name): string
    {
        return app(BackupOperationLock::class)->run(function () use ($name) {
            if (! extension_loaded('sqlite3') || DB::connection()->getDriverName() !== 'sqlite') {
                throw new RuntimeException('Pemulihan membutuhkan SQLite dan ekstensi sqlite3.');
            }
            // Gunakan path database yang benar-benar dibuka, bukan tebakan lokasi default.
            $databases = DB::select('PRAGMA database_list');
            $database = collect($databases)->firstWhere('name', 'main')->file ?? '';
            if (! $database || ! is_file($database) || is_link($database)) {
                throw new RuntimeException('Pemulihan hanya mendukung file SQLite lokal biasa.');
            }
            $verified = app(BackupService::class)->restoreTest(app(BackupCatalog::class)->path($name));
            $sourceDb = $verified['directory'].'/database/database.sqlite';
            $snapshot = new \PDO('sqlite:'.$sourceDb);
            $migrationNames = $snapshot->query('SELECT migration FROM migrations ORDER BY migration')->fetchAll(\PDO::FETCH_COLUMN);
            $snapshot = null;
            if ($migrationNames !== DB::table('migrations')->orderBy('migration')->pluck('migration')->all()) {
                throw new RuntimeException('Versi database backup berbeda. Gunakan kode dan migrasi yang sesuai.');
            }

            // Simpan jalan kembali sebelum menyentuh unggahan atau database aktif.
            $safetyBackup = app(BackupService::class)->create();
            if (app()->maintenanceMode()->active()) {
                throw new RuntimeException('Aplikasi sudah dalam pemeliharaan. Selesaikan operasi tersebut terlebih dahulu.');
            }
            // Bila proses terputus di tengah pemulihan, aplikasi tetap offline untuk pemeriksaan.
            app()->maintenanceMode()->activate(['status' => 503, 'retry' => 60]);
            $safeToResume = false;
            $swaps = [];
            try {
                foreach (['public' => 'public', 'local' => 'private'] as $disk => $prefix) {
                    $target = config("filesystems.disks.$disk.root");
                    $expected = storage_path('app/'.$prefix);
                    if (config("filesystems.disks.$disk.driver") !== 'local' || is_link($target)
                        || str_replace('\\', '/', $target) !== str_replace('\\', '/', $expected)) {
                        throw new RuntimeException('Pemulihan unggahan memerlukan direktori standar storage/app/public dan private.');
                    }
                    $old = $target.'.before-restore-'.Str::uuid();
                    $new = $target.'.restoring-'.Str::uuid();
                    File::ensureDirectoryExists($new, 0700);
                    $swaps[] = ['target' => $target, 'old' => $old, 'new' => $new, 'moved' => false, 'installed' => false];
                    $index = array_key_last($swaps);
                    $source = $verified['directory'].'/uploads/'.$prefix;
                    if (is_dir($source) && ! File::copyDirectory($source, $new)) {
                        throw new RuntimeException('Gagal menyiapkan unggahan hasil pemulihan.');
                    }
                    chmod($new, $prefix === 'public' ? 0755 : 0700);
                    foreach (File::allFiles($new, true) as $file) { chmod($file->getPathname(), $prefix === 'public' ? 0644 : 0600); }
                    if (is_dir($target)) {
                        if (! rename($target, $old)) { throw new RuntimeException('Gagal mengamankan unggahan lama.'); }
                        $swaps[$index]['moved'] = true;
                    }
                    if (! rename($new, $target)) { throw new RuntimeException('Gagal memasang unggahan.'); }
                    $swaps[$index]['installed'] = true;
                }
                DB::purge();
                // SQLite backup API mengganti isi database dalam transaksi, termasuk penanganan WAL.
                $source = new SQLite3($sourceDb, SQLITE3_OPEN_READONLY);
                $destination = new SQLite3($database, SQLITE3_OPEN_READWRITE);
                try {
                    $destination->busyTimeout(5000);
                    if (! $source->backup($destination)) { throw new RuntimeException('Database terkunci; pemulihan dibatalkan.'); }
                } finally {
                    $source->close();
                    $destination->close();
                }
                $safeToResume = true;
            } catch (\Throwable $exception) {
                // Jika database gagal dipulihkan, kembalikan direktori unggahan semula.
                foreach (array_reverse($swaps) as $swap) {
                    if ($swap['installed'] && ! File::deleteDirectory($swap['target'])) {
                        throw new RuntimeException('Gagal membatalkan unggahan. Backup pengaman: '.$safetyBackup, 0, $exception);
                    }
                    if ($swap['moved'] && ! rename($swap['old'], $swap['target'])) {
                        app()->maintenanceMode()->activate(['status' => 503, 'retry' => 60]);
                        throw new RuntimeException('Gagal mengembalikan unggahan. Backup pengaman: '.$safetyBackup, 0, $exception);
                    }
                    if (is_dir($swap['new'])) { File::deleteDirectory($swap['new']); }
                }
                $safeToResume = true;
                throw $exception;
            } finally {
                if ($safeToResume) { app()->maintenanceMode()->deactivate(); }
            }
            // Direktori lama dipertahankan sebagai pengaman tambahan; .env tidak diterapkan.
            return $safetyBackup;
        });
    }
}
