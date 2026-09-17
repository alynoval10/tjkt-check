<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;

class BackupCatalog
{
    public function path(string $name): string
    {
        // Hanya file ZIP biasa di direktori cadangan, bukan path dari browser.
        abort_unless(preg_match('/\Atjkt-check-[a-zA-Z0-9_-]+\.zip\z/', $name), 404);
        $root = realpath(storage_path('app/backups'));
        $path = $root ? $root.DIRECTORY_SEPARATOR.$name : '';
        abort_unless($root && is_file($path) && ! is_link($path) && dirname(realpath($path)) === $root, 404);

        return $path;
    }

    public function all(): array
    {
        $root = storage_path('app/backups');
        if (! is_dir($root)) {
            return [];
        }
        return collect(File::files($root))->filter(fn ($file) => ! $file->isLink()
            && preg_match('/\Atjkt-check-[a-zA-Z0-9_-]+\.zip\z/', $file->getFilename()))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(function ($file) {
                $report = $this->report($file->getFilename());
                return ['name' => $file->getFilename(), 'date' => date('d/m/Y H:i', $file->getMTime()),
                    'bytes' => $file->getSize(), 'report' => $report];
            })->values()->all();
    }

    public function report(string $name): ?array
    {
        $path = $this->path($name);
        $reportPath = $path.'.report.json';
        if (! is_file($reportPath) || is_link($reportPath)) {
            return null;
        }
        $report = json_decode(File::get($reportPath), true);
        // Laporan lama tidak dianggap berlaku jika isi ZIP telah berubah.
        return is_array($report) && ($report['sha256'] ?? null) === hash_file('sha256', $path) ? $report : null;
    }

    public function verify(string $name): array
    {
        return app(BackupOperationLock::class)->run(fn () => $this->verifyArchive($name));
    }

    private function verifyArchive(string $name): array
    {
        $path = $this->path($name);
        $hash = hash_file('sha256', $path);
        try {
            $result = app(BackupService::class)->restoreTest($path);
            if ($hash !== hash_file('sha256', $path)) {
                throw new RuntimeException('Backup berubah selama pemeriksaan. Ulangi pengujian.');
            }
            $report = ['status' => 'passed', 'checked_at' => now()->toIso8601String(),
                'sha256' => $hash, 'tables' => $result['tables'], 'files' => $result['files'],
                'directory' => $result['directory']];
        } catch (\Throwable $exception) {
            $report = ['status' => 'failed', 'checked_at' => now()->toIso8601String(),
                'sha256' => $hash, 'message' => 'Pemeriksaan gagal. Backup rusak atau formatnya tidak didukung.'];
            report($exception);
        }
        // Simpan status di luar database agar laporan tersedia ketika database bermasalah.
        File::replace($path.'.report.json', json_encode($report, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT), 0600);

        return $report;
    }

    public function delete(string $name): void
    {
        app(BackupOperationLock::class)->run(function () use ($name): void {
            $path = $this->path($name);
            $report = $path.'.report.json';
            if (is_link($report)) {
                throw new RuntimeException('Laporan berupa tautan; penghapusan ditolak.');
            }
            // Hanya ZIP terpilih dan laporan pendamping; hasil uji dibersihkan terpisah.
            if (is_file($report) && ! unlink($report)) {
                throw new RuntimeException('Laporan backup gagal dihapus.');
            }
            if (! unlink($path)) {
                throw new RuntimeException('File backup gagal dihapus.');
            }
        });
    }

    public function cleanRestoreTests(): int
    {
        return app(BackupOperationLock::class)->run(function (): int {
            $base = storage_path('app/restore-tests');
            if (! is_dir($base)) { return 0; }
            if (is_link($base)) { throw new RuntimeException('Direktori hasil uji berupa tautan.'); }
            $root = realpath($base);
            $targets = [];
            foreach (File::directories($base) as $directory) {
                // Hanya folder UUID hasil uji selesai; folder lain dan pengaman restore tidak disentuh.
                if (! preg_match('/\A[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}\z/i', basename($directory))
                    || ! is_file($directory.'/restore-report.json')) { continue; }
                if (is_link($directory) || dirname(realpath($directory)) !== $root) {
                    throw new RuntimeException('Lokasi hasil uji tidak aman.');
                }
                // Periksa semua anak sebelum menghapus, termasuk symlink/junction ke luar folder.
                $prefix = realpath($directory).DIRECTORY_SEPARATOR;
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
                foreach ($iterator as $entry) {
                    if ($entry->isLink() || ! str_starts_with($entry->getRealPath() ?: '', $prefix)) {
                        throw new RuntimeException('Tautan atau path di luar hasil uji ditemukan.');
                    }
                }
                $targets[] = $directory;
            }
            foreach ($targets as $directory) {
                if (! File::deleteDirectory($directory)) {
                    throw new RuntimeException('Sebagian hasil uji gagal dibersihkan. Muat ulang dan coba kembali.');
                }
            }
            return count($targets);
        });
    }
}
