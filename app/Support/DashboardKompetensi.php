<?php

namespace App\Support;

use App\Models\Kelas;
use App\Models\Kelulusan;
use App\Models\Materi;
use Illuminate\Support\Collection;

class DashboardKompetensi
{
    /**
     * Build dashboard metrics from the latest score for every student/material pair.
     *
     * @return array<string, mixed>
     */
    public function ringkasan(?int $kelasId = null): array
    {
        $kelas = Kelas::query()
            ->where('is_active', true)
            ->when($kelasId, fn ($query) => $query->whereKey($kelasId))
            ->withCount(['siswas as siswa_aktif_count' => fn ($query) => $query->where('status', 'aktif')])
            ->with(['siswas' => fn ($query) => $query
                ->where('status', 'aktif')
                ->select(['id', 'kelas_id', 'nama', 'nis'])])
            ->orderByRaw("CASE tingkat WHEN 'X' THEN 0 WHEN 'XI' THEN 1 WHEN 'XII' THEN 2 ELSE 3 END")
            ->orderBy('nama')
            ->get();

        $tingkatAktif = $kelas->pluck('tingkat')->unique()->values();
        $materiPerTingkat = Materi::query()
            ->whereIn('tingkat', $tingkatAktif)
            ->orderBy('kode')
            ->get(['id', 'kode', 'nama', 'tingkat'])
            ->groupBy('tingkat');

        $siswas = $kelas->flatMap(fn (Kelas $rombel) => $rombel->siswas);
        $siswaIds = $siswas->pluck('id')->all();
        $materiIds = $materiPerTingkat->flatten(1)->pluck('id')->all();

        $nilaiTerakhir = $siswaIds === [] || $materiIds === []
            ? collect()
            : Kelulusan::query()
                ->whereIn('siswa_id', $siswaIds)
                ->whereIn('materi_id', $materiIds)
                ->orderByDesc('id')
                ->get(['id', 'siswa_id', 'materi_id', 'nilai'])
                ->unique(fn (Kelulusan $nilai): string => "{$nilai->siswa_id}:{$nilai->materi_id}")
                ->groupBy('siswa_id');

        $ringkasanKelas = collect();
        $perhatianSiswa = collect();
        $insightBySiswa = [];

        foreach ($kelas as $rombel) {
            $materiIdsTingkat = $materiPerTingkat->get($rombel->tingkat, collect())->pluck('id');
            $jumlahMateri = $materiIdsTingkat->count();
            $progresSiswa = [];

            foreach ($rombel->siswas as $siswa) {
                $nilaiSiswa = $nilaiTerakhir->get($siswa->id, collect())
                    ->whereIn('materi_id', $materiIdsTingkat);
                $nilaiTerisi = $nilaiSiswa->filter(fn (Kelulusan $nilai): bool => $nilai->nilai !== null);
                $jumlahKompeten = $nilaiTerisi->filter(fn (Kelulusan $nilai): bool => $nilai->nilai >= 75)->count();
                $jumlahRemedial = $nilaiTerisi->filter(fn (Kelulusan $nilai): bool => $nilai->nilai < 75)->count();
                $progres = $jumlahMateri > 0 ? (int) round(($jumlahKompeten / $jumlahMateri) * 100) : 0;

                $progresSiswa[] = $progres;
                $insight = [
                    'nama' => $siswa->nama,
                    'nis' => $siswa->nis,
                    'kelas' => $rombel->nama,
                    'progres' => $progres,
                    'remedial' => $jumlahRemedial,
                    'status' => $jumlahRemedial > 0 ? 'Perlu remedial' : 'Progres rendah',
                ];
                $insightBySiswa[$siswa->id] = $insight;

                if ($jumlahRemedial > 0 || $progres < 50) {
                    $perhatianSiswa->push($insight);
                }
            }

            $ringkasanKelas->push([
                'nama' => $rombel->nama,
                'tingkat' => $rombel->tingkat,
                'jumlah_siswa' => $rombel->siswa_aktif_count,
                'progres' => $progresSiswa === [] ? 0 : (int) round(array_sum($progresSiswa) / count($progresSiswa)),
            ]);
        }

        $jumlahSiswaPerTingkat = $kelas
            ->groupBy('tingkat')
            ->map(fn (Collection $rombels): int => $rombels->sum('siswa_aktif_count'));

        $progresMateri = $materiPerTingkat
            ->flatMap(function (Collection $materiPadaTingkat, string $tingkat) use ($jumlahSiswaPerTingkat, $nilaiTerakhir, $kelas): Collection {
                $siswaTingkat = $kelas
                    ->where('tingkat', $tingkat)
                    ->flatMap(fn (Kelas $rombel) => $rombel->siswas);
                $jumlahSiswa = $jumlahSiswaPerTingkat->get($tingkat, 0);

                return $materiPadaTingkat->map(function (Materi $materi) use ($siswaTingkat, $jumlahSiswa, $nilaiTerakhir, $tingkat): array {
                    $lulus = 0;
                    $remedial = 0;

                    foreach ($siswaTingkat as $siswa) {
                        $nilai = $nilaiTerakhir->get($siswa->id, collect())->firstWhere('materi_id', $materi->id);

                        if ($nilai?->nilai === null) {
                            continue;
                        }

                        if ($nilai->nilai >= 75) {
                            $lulus++;
                        } else {
                            $remedial++;
                        }
                    }

                    $sudahDiuji = $lulus + $remedial;

                    return [
                        'label' => "{$tingkat} · {$materi->nama}",
                        'lulus' => $lulus,
                        'remedial' => $remedial,
                        'belum_diuji' => max(0, $jumlahSiswa - $sudahDiuji),
                        'total_siswa' => $jumlahSiswa,
                    ];
                });
            })
            ->values();

        $perhatianSiswa = $perhatianSiswa
            ->sortBy(fn (array $siswa): array => [$siswa['remedial'] > 0 ? 0 : 1, $siswa['progres'], $siswa['nama']])
            ->take(5)
            ->values();

        return [
            'jumlah_siswa' => $siswas->count(),
            'jumlah_rombel' => $kelas->count(),
            'jumlah_materi' => $materiPerTingkat->flatten(1)->count(),
            'jumlah_remedial' => collect($insightBySiswa)->where('remedial', '>', 0)->count(),
            'kelas' => $ringkasanKelas,
            'materi' => $progresMateri,
            'perhatian' => $perhatianSiswa,
        ];
    }
}
