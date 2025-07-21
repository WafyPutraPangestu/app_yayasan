<?php

namespace App\Exports;

use App\Models\Kas;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllDashboardDataExport implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    /**
     * Transform transaction data into array format for Excel export
     *
     * @return array
     */
    public function array(): array
    {
        $data = Kas::with(['user', 'jenisKas'])
            ->orderBy('tanggal', 'asc')
            ->get()
            ->groupBy(function ($kas) {
                return Carbon::parse($kas->tanggal)->format('Y-m');
            });

        $result = [];

        foreach ($data as $tahunBulan => $items) {
            $tanggalContoh = Carbon::createFromFormat('Y-m', $tahunBulan);
            $namaBulan = $tanggalContoh->translatedFormat('F Y');

            // Add month header
            $result[] = ["LAPORAN TRANSAKSI BULAN $namaBulan"];
            $result[] = []; // Empty row
            $result[] = $this->getHeaderRow();

            foreach ($items as $kas) {
                $result[] = $this->formatKasRow($kas);
            }

            // Add empty rows between months
            $result[] = [];
            $result[] = [];
        }

        return $result;
    }

    /**
     * Get title for Excel sheet
     *
     * @return string
     */
    public function title(): string
    {
        return 'Data Transaksi';
    }

    /**
     * Get table header row
     *
     * @return array
     */
    protected function getHeaderRow(): array
    {
        return [
            'ID Transaksi',
            'ID Anggota',
            'Nama Member',
            'Jenis Transaksi',
            'Tipe',
            'Nominal',
            'Keterangan',
            'Tanggal Transaksi'
        ];
    }

    /**
     * Format Kas row for export
     *
     * @param Kas $kas
     * @return array
     */
    protected function formatKasRow(Kas $kas): array
    {
        return [
            $kas->id,
            $kas->user->id_anggota ?? 'Umum',
            $kas->user->name ?? 'Umum',
            $kas->jenisKas->nama_jenis_kas ?? '-',
            ucfirst($kas->tipe),
            'Rp ' . number_format($kas->jumlah, 0, ',', '.'),
            $kas->keterangan,
            Carbon::parse($kas->tanggal)->format('d-m-Y'),
        ];
    }

    /**
     * Apply styles to the worksheet
     *
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // Month header style
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '2F75B5']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ]
        ]);

        // Merge month header cell
        $sheet->mergeCells('A1:H1');

        // Table header style
        $sheet->getStyle('A3:H3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '5B9BD5']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'D9D9D9']
                ]
            ]
        ]);

        // Data rows style
        $sheet->getStyle('A4:H' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'D9D9D9']
                ]
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP
            ]
        ]);

        // Alternate row coloring
        for ($i = 4; $i <= $lastRow; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle('A' . $i . ':H' . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => 'F2F2F2']
                    ]
                ]);
            }
        }

        // Number formatting for amount column
        $sheet->getStyle('F4:F' . $lastRow)->getNumberFormat()->setFormatCode('"Rp"#,##0;[Red]"Rp"-#,##0');

        // Date formatting
        $sheet->getStyle('H4:H' . $lastRow)->getNumberFormat()->setFormatCode('dd-mm-yyyy');

        return [];
    }

    /**
     * Set column widths
     *
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 12,  // ID Transaksi
            'B' => 12,  // ID Anggota
            'C' => 20,  // Nama Member
            'D' => 20,  // Jenis Transaksi
            'E' => 12,  // Tipe
            'F' => 18,  // Nominal
            'G' => 30,  // Keterangan
            'H' => 18   // Tanggal Transaksi
        ];
    }
}
