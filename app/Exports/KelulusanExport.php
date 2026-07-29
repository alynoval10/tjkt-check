<?php

namespace App\Exports;

use App\Models\Kelulusan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KelulusanExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Kelulusan::with(['siswa.rombel', 'materi', 'user'])
            ->get()
            ->map(fn ($item) => [
                $item->siswa?->nama ?? '',
                $item->siswa?->nis ?? '',
                $item->siswa?->rombel?->tingkat ?? '',
                $item->siswa?->rombel?->nama ?? '',
                $item->materi?->nama ?? '',
                $item->user?->name ?? '',
                $item->tanggal_uji,
                $item->nilai,
                $item->catatan,
            ]);
    }

    public function headings(): array
    {
        return ['Nama Siswa', 'NIS', 'Tingkat', 'Kelas', 'Materi', 'Penguji', 'Tanggal Uji', 'Nilai', 'Catatan'];
    }
}
