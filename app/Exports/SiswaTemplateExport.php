<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiswaTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['nis', 'nama', 'kelas'];
    }

    public function array(): array
    {
        return [
            ['001', 'Contoh Siswa Kelas X', 'X TKJ 1'],
            ['002', 'Contoh Siswa Kelas XI', 'XI TKJ 2'],
            ['003', 'Contoh Siswa Kelas XII', 'XII TKJ 3'],
        ];
    }
}
