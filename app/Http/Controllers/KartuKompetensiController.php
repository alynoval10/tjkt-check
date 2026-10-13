<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\Siswa;

class KartuKompetensiController extends Controller
{
    public function __invoke(Siswa $siswa)
    {
        // Kartu mengikuti tingkat kelas saat ini, sama seperti matriks Dashboard.
        $siswa->load('rombel');
        $materis = Materi::query()
            ->when(
                $siswa->status !== 'alumni',
                fn ($query) => $query->where('tingkat', $siswa->rombel?->tingkat),
            )
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();
        $penilaian = $siswa->kelulusans()
            ->with('user')
            ->whereIn('materi_id', $materis->pluck('id'))
            ->get()
            ->sortByDesc(fn ($record) => [$record->tanggal_uji?->format('Ymd') ?? '', $record->id])
            ->unique('materi_id')
            ->keyBy('materi_id');

        return response()->view('kartu-kompetensi', compact('siswa', 'materis', 'penilaian'))
            ->header('Cache-Control', 'private, no-store');
    }
}
