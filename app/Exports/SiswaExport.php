<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiswaExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Siswa::with('rombel')
            ->orderBy('nama')
            ->get()
            ->map(fn ($siswa) => [
                $siswa->nis,
                $siswa->nama,
                $siswa->rombel?->tingkat ?? '',
                $siswa->rombel?->nama ?? '',
            ]);
    }

    public function headings(): array
    {
        return ['NIS', 'Nama Siswa', 'Tingkat', 'Kelas'];
    }
}
