<?php

namespace App\Filament\Widgets;

use App\Models\Kelas;
use App\Models\Kelulusan;
use App\Models\Materi;
use App\Models\Siswa;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

class DetailSiswaMateri extends Widget
{
    use InteractsWithPageFilters;

    protected string $view = 'filament.widgets.detail-siswa-materi';

    protected int|string|array $columnSpan = 'full';

    public ?int $materiId = null;

    public function mount(): void
    {
        $this->setMateriAwal();
    }

    protected function getKelas(): ?Kelas
    {
        $kelasId = $this->pageFilters['kelas_id'] ?? null;

        if ($kelasId) {
            return Kelas::find($kelasId);
        }

        return Kelas::query()
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->first();
    }

    protected function setMateriAwal(): void
    {
        $kelas = $this->getKelas();

        if (! $kelas) {
            $this->materiId = null;

            return;
        }

        $this->materiId = Materi::query()
            ->where('tingkat', $kelas->tingkat)
            ->orderBy('nama')
            ->value('id');
    }

    public function updatedPageFilters(
        mixed $value = null,
        ?string $key = null
    ): void {
        $this->setMateriAwal();
    }

    protected function getViewData(): array
    {
        $kelas = $this->getKelas();

        if (! $kelas) {
            return [
                'kelas' => null,
                'materis' => collect(),
                'materi' => null,
                'lulus' => collect(),
                'belumLulus' => collect(),
                'belumDiuji' => collect(),
            ];
        }

        $materis = Materi::query()
            ->where('tingkat', $kelas->tingkat)
            ->orderBy('nama')
            ->get();

        $materi = $materis->firstWhere(
            'id',
            (int) $this->materiId
        );

        if (! $materi && $materis->isNotEmpty()) {
            $materi = $materis->first();
            $this->materiId = $materi->id;
        }

        if (! $materi) {
            return [
                'kelas' => $kelas,
                'materis' => $materis,
                'materi' => null,
                'lulus' => collect(),
                'belumLulus' => collect(),
                'belumDiuji' => collect(),
            ];
        }

        $siswas = Siswa::query()
            ->where('kelas_id', $kelas->id)
            ->orderBy('nama')
            ->get();

        $penilaian = Kelulusan::query()
            ->where('materi_id', $materi->id)
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->get()
            ->keyBy('siswa_id');

        $lulus = collect();
        $belumLulus = collect();
        $belumDiuji = collect();

        foreach ($siswas as $siswa) {
            $kelulusan = $penilaian->get($siswa->id);

            if (
                ! $kelulusan
                || is_null($kelulusan->nilai)
            ) {
                $belumDiuji->push([
                    'nama' => $siswa->nama,
                    'nilai' => null,
                ]);

                continue;
            }

            if ($kelulusan->nilai >= 75) {
                $lulus->push([
                    'nama' => $siswa->nama,
                    'nilai' => $kelulusan->nilai,
                ]);

                continue;
            }

            $belumLulus->push([
                'nama' => $siswa->nama,
                'nilai' => $kelulusan->nilai,
            ]);
        }

        return [
            'kelas' => $kelas,
            'materis' => $materis,
            'materi' => $materi,
            'lulus' => $lulus,
            'belumLulus' => $belumLulus,
            'belumDiuji' => $belumDiuji,
        ];
    }
}