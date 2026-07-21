<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiswaTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'nis',
            'nama',
            'kelas',
        ];
    }


    public function array(): array
    {
        return [
            [
                '001',
                'Contoh Nama Siswa',
                'XI TJKT 1'
            ],
        ];
    }
}