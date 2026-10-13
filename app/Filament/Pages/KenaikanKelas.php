<?php

namespace App\Filament\Pages;

use App\Models\Kelas;
use App\Models\PeriodeAkademik;
use App\Models\RiwayatKelas;
use App\Models\Siswa;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class KenaikanKelas extends Page
{
    protected string $view = 'filament.pages.kenaikan-kelas';
    protected static ?string $title = 'Kenaikan Kelas & Kelulusan';
    protected static ?string $navigationLabel = 'Kenaikan & Kelulusan';
    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Akademik';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpCircle;
    protected static ?int $navigationSort = 1;

    public ?int $periodeAkademikId = null;

    /** @var array<int, string> */
    public array $namaRombelBaru = [];

    public function mount(): void
    {
        $this->periodeAkademikId = PeriodeAkademik::aktif()?->id;

        foreach (Kelas::query()->where('is_active', true)->whereIn('tingkat', ['X', 'XI'])->get() as $kelas) {
            $this->namaRombelBaru[$kelas->id] = $this->namaRombelTujuanDefault($kelas) ?? $kelas->nama;
        }
    }

    public function getPeriodeOptionsProperty(): array
    {
        return PeriodeAkademik::query()
            ->orderByDesc('tahun_ajaran')
            ->orderBy('semester')
            ->get()
            ->mapWithKeys(fn (PeriodeAkademik $periode): array => [
                $periode->id => "{$periode->tahun_ajaran} — Semester {$periode->semester}",
            ])
            ->all();
    }

    public function getKelasProperty()
    {
        return Kelas::query()
            ->where('is_active', true)
            ->whereIn('tingkat', ['X', 'XI', 'XII'])
            ->withCount(['siswas as siswa_aktif_count' => fn ($query) => $query->where('status', 'aktif')])
            ->orderByRaw("CASE tingkat WHEN 'XII' THEN 0 WHEN 'XI' THEN 1 ELSE 2 END")
            ->orderBy('nama')
            ->get();
    }

    public function namaRombelTujuanDefault(Kelas $kelas): ?string
    {
        $tingkatTujuan = match ($kelas->tingkat) {
            'X' => 'XI',
            'XI' => 'XII',
            default => null,
        };

        return $tingkatTujuan
            ? preg_replace('/^(XII|XI|X)(?=\s|$)/', $tingkatTujuan, $kelas->nama)
            : null;
    }

    public function prosesKenaikan(int $kelasId): void
    {
        $kelas = Kelas::query()->where('is_active', true)->findOrFail($kelasId);
        $periode = $this->periodeAkademikId
            ? PeriodeAkademik::query()->find($this->periodeAkademikId)
            : null;
        $tingkatTujuan = match ($kelas->tingkat) {
            'X' => 'XI',
            'XI' => 'XII',
            default => null,
        };
        $namaBaru = trim($this->namaRombelBaru[$kelasId] ?? $this->namaRombelTujuanDefault($kelas) ?? '');

        if (! $periode || ! $tingkatTujuan || ! preg_match('/^' . preg_quote($tingkatTujuan, '/') . '(?:\s|$)/u', $namaBaru)) {
            Notification::make()
                ->title('Periksa periode dan nama rombel')
                ->body('Pilih periode dan gunakan nama rombel yang dimulai dengan tingkat tujuan (' . ($tingkatTujuan ?? '-') . ').')
                ->danger()
                ->send();
            return;
        }

        if (mb_strlen($namaBaru) > 255) {
            Notification::make()->title('Nama rombel terlalu panjang')->danger()->send();
            return;
        }

        if (Kelas::query()->where('nama', $namaBaru)->whereKeyNot($kelas->id)->exists()) {
            Notification::make()
                ->title('Nama rombel sudah digunakan')
                ->body('Proses kelas tujuan terlebih dahulu agar namanya tersedia, lalu coba lagi.')
                ->danger()
                ->send();
            return;
        }

        $jumlahSiswa = Siswa::query()
            ->where('kelas_id', $kelas->id)
            ->where('status', 'aktif')
            ->count();

        if ($jumlahSiswa === 0) {
            Notification::make()->title('Tidak ada siswa aktif di rombel ini')->warning()->send();
            return;
        }

        try {
            DB::transaction(function () use ($kelas, $periode, $namaBaru, $tingkatTujuan): void {
                $sudahDiproses = RiwayatKelas::query()
                    ->where('periode_akademik_id', $periode->id)
                    ->where('jenis_perubahan', 'kenaikan')
                    ->where('kelas_asal_id', $kelas->id)
                    ->where('kelas_tujuan_id', $kelas->id)
                    ->exists();

                if ($sudahDiproses) {
                    throw new \RuntimeException('Rombel ini sudah dinaikkan pada periode yang dipilih. Pilih periode berikutnya untuk kenaikan selanjutnya.');
                }

                $siswas = Siswa::query()
                    ->where('kelas_id', $kelas->id)
                    ->where('status', 'aktif')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                foreach ($siswas as $siswa) {
                    RiwayatKelas::query()->create([
                        'siswa_id' => $siswa->id,
                        'kelas_asal_id' => $kelas->id,
                        'kelas_tujuan_id' => $kelas->id,
                        'periode_akademik_id' => $periode->id,
                        'jenis_perubahan' => 'kenaikan',
                        'kelas_asal_nama' => $kelas->nama,
                        'kelas_asal_tingkat' => $kelas->tingkat,
                        'kelas_tujuan_nama' => $namaBaru,
                        'kelas_tujuan_tingkat' => $tingkatTujuan,
                    ]);
                }

                // Pertahankan kelas_id siswa: satu rombel tetap menjadi satu cohort.
                $kelas->update(['nama' => $namaBaru, 'tingkat' => $tingkatTujuan]);
            });
        } catch (\RuntimeException $exception) {
            Notification::make()->title('Kenaikan kelas tidak diproses')->body($exception->getMessage())->danger()->send();
            return;
        }

        $tingkatBerikutnya = $tingkatTujuan === 'XI' ? 'XII' : null;
        if ($tingkatBerikutnya) {
            $this->namaRombelBaru[$kelasId] = preg_replace('/^(XII|XI|X)(?=\s|$)/', $tingkatBerikutnya, $namaBaru);
        } else {
            unset($this->namaRombelBaru[$kelasId]);
        }
        Notification::make()
            ->title('Rombel berhasil dinaikkan')
            ->body("{$jumlahSiswa} siswa tetap di rombel yang sama, sekarang bernama {$namaBaru}.")
            ->success()
            ->send();
    }

    public function luluskanKelas(int $kelasId): void
    {
        $kelas = Kelas::query()->where('is_active', true)->findOrFail($kelasId);
        $periode = $this->periodeAkademikId
            ? PeriodeAkademik::query()->find($this->periodeAkademikId)
            : null;

        if ($kelas->tingkat !== 'XII' || ! $periode) {
            Notification::make()->title('Pilih periode kelulusan terlebih dahulu')->danger()->send();
            return;
        }

        $jumlahSiswa = Siswa::query()
            ->where('kelas_id', $kelas->id)
            ->where('status', 'aktif')
            ->count();

        if ($jumlahSiswa === 0) {
            Notification::make()->title('Tidak ada siswa aktif di rombel ini')->warning()->send();
            return;
        }

        $namaArsip = "Alumni {$periode->tahun_ajaran} - {$kelas->nama}";

        if (Kelas::query()->where('nama', $namaArsip)->exists()) {
            $namaArsip .= " ({$kelas->id})";
        }

        try {
            DB::transaction(function () use ($kelas, $periode, $namaArsip): void {
                $sudahDiproses = RiwayatKelas::query()
                    ->where('periode_akademik_id', $periode->id)
                    ->where('jenis_perubahan', 'lulus')
                    ->where('kelas_asal_id', $kelas->id)
                    ->exists();

                if ($sudahDiproses) {
                    throw new \RuntimeException('Rombel ini sudah diluluskan pada periode yang dipilih.');
                }

                $siswas = Siswa::query()
                    ->where('kelas_id', $kelas->id)
                    ->where('status', 'aktif')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                foreach ($siswas as $siswa) {
                    RiwayatKelas::query()->create([
                        'siswa_id' => $siswa->id,
                        'kelas_asal_id' => $kelas->id,
                        'kelas_tujuan_id' => $kelas->id,
                        'periode_akademik_id' => $periode->id,
                        'jenis_perubahan' => 'lulus',
                        'kelas_asal_nama' => $kelas->nama,
                        'kelas_asal_tingkat' => $kelas->tingkat,
                        'kelas_tujuan_nama' => $namaArsip,
                        'kelas_tujuan_tingkat' => $kelas->tingkat,
                    ]);

                    $siswa->update([
                        'status' => 'alumni',
                        'tanggal_lulus' => now()->toDateString(),
                        'periode_lulus_id' => $periode->id,
                    ]);
                }

                $kelas->update(['nama' => $namaArsip, 'is_active' => false]);
            });
        } catch (\RuntimeException $exception) {
            Notification::make()->title('Kelulusan tidak diproses')->body($exception->getMessage())->danger()->send();
            return;
        }

        Notification::make()
            ->title('Rombel XII diluluskan')
            ->body("{$jumlahSiswa} siswa kini berstatus alumni. Rombel lama diarsipkan dan riwayat nilainya tetap tersimpan.")
            ->success()
            ->send();
    }
}
