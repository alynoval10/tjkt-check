<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\PeriodeAkademik;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KartuKompetensiController extends Controller
{
    public function __invoke(Request $request, Siswa $siswa)
    {
        $request->validate([
            'periode_id' => ['nullable', 'integer', Rule::exists('periode_akademiks', 'id')],
        ]);

        $periodeId = $request->has('periode_id')
            ? ($request->filled('periode_id') ? (int) $request->input('periode_id') : null)
            : PeriodeAkademik::aktif()?->id;
        $periode = $periodeId ? PeriodeAkademik::query()->find($periodeId) : null;
        $periodes = PeriodeAkademik::query()
            ->orderByDesc('tahun_ajaran')
            ->orderBy('semester')
            ->get();

        $siswa->load('rombel');
        $penilaianQuery = $siswa->kelulusans()->with('user');

        if ($periodeId) {
            $penilaianQuery->where('periode_akademik_id', $periodeId);
        }

        $semuaPenilaian = $penilaianQuery->get();
        $tingkatSnapshot = $semuaPenilaian->first(fn ($record) => filled($record->tingkat_snapshot))?->tingkat_snapshot;
        $tingkatLaporan = $siswa->status === 'alumni'
            ? null
            : ($periodeId ? ($tingkatSnapshot ?? $siswa->rombel?->tingkat) : $siswa->rombel?->tingkat);

        // Materi mengikuti tingkat siswa pada periode laporan jika ada snapshot nilai.
        $materis = Materi::query()
            ->when(
                $tingkatLaporan !== null,
                fn ($query) => $query->where('tingkat', $tingkatLaporan),
            )
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        $penilaian = $semuaPenilaian
            ->whereIn('materi_id', $materis->pluck('id'))
            ->sortByDesc(fn ($record) => [$record->tanggal_uji?->format('Ymd') ?? '', $record->id])
            ->unique('materi_id')
            ->keyBy('materi_id');

        $kelasSnapshot = $semuaPenilaian->first(fn ($record) => filled($record->kelas_nama_snapshot))?->kelas_nama_snapshot;
        $kelasLaporan = $siswa->status === 'alumni'
            ? 'Alumni'
            : ($periodeId ? ($kelasSnapshot ?? $siswa->rombel?->nama ?? '-') : ($siswa->rombel?->nama ?? '-'));

        return response()->view('kartu-kompetensi', compact(
            'siswa', 'materis', 'penilaian', 'periodes', 'periode', 'periodeId', 'kelasLaporan'
        ))
            ->header('Cache-Control', 'private, no-store');
    }
}
