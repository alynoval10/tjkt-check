<?php

namespace App\Exports;

use App\Models\Kelulusan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KelulusanExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Kelulusan::with([
            'siswa',
            'materi',
            'user',
        ])->get()->map(function ($item) {

            return [
                $item->siswa->nama ?? '',
                $item->siswa->nis ?? '',
                $item->siswa->kelas ?? '',
                $item->materi->nama ?? '',
                $item->user->name ?? '',
                $item->tanggal_uji,
                $item->nilai,
                $item->catatan,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama Siswa',
            'NIS',
            'Kelas',
            'Materi',
            'Penguji',
            'Tanggal Uji',
            'Nilai',
            'Catatan',
        ];
    }
}