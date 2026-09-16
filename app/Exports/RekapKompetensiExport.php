<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapKompetensiExport extends DefaultValueBinder implements FromArray, WithHeadings, WithStyles, WithCustomValueBinder
{
    public function __construct(private string $kelas, private array $materis, private array $siswas) {}

    // Kolom materi mengikuti filter dan urutan yang terlihat di Dashboard.
    public function headings(): array
    {
        return array_merge(['Kelas', 'NIS', 'Nama Siswa'], array_column($this->materis, 'nama'), ['Ketuntasan (%)', 'Materi Remedial']);
    }

    public function array(): array
    {
        return array_map(function ($siswa) {
            $row = [$this->kelas, (string) $siswa['nis'], $siswa['nama']];
            foreach ($this->materis as $materi) {
                $cell = $siswa['matrix'][$materi['id']];
                $row[] = match ($cell['status']) {
                    'lulus' => $cell['nilai'].' - Lulus',
                    'belum_lulus' => $cell['nilai'].' - Remedial',
                    default => 'Belum Diuji',
                };
            }

            return array_merge($row, [$siswa['progres'], implode(', ', $siswa['remedial'])]);
        }, $this->siswas);
    }

    // NIS tetap menyimpan nol awal; teks seperti =Nama tidak menjadi rumus Excel.
    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('D2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        $sheet->getDefaultColumnDimension()->setWidth(24);
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setWrapText(true);
        $sheet->getRowDimension(1)->setRowHeight(45);

        return [1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '166534']]]];
    }
}
