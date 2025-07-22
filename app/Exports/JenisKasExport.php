<?php

namespace App\Exports;

use App\Models\JenisKas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle; // <-- TAMBAHKAN INI
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class JenisKasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents, WithTitle // <-- TAMBAHKAN WithTitle
{
    /**
     * Mengatur judul untuk sheet di dalam file Excel.
     * @return string
     */
    public function title(): string // <-- TAMBAHKAN FUNGSI BARU INI
    {
        return 'Data Jenis Kas';
    }

    /**
     * Mengambil semua data JenisKas dari database.
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return JenisKas::all();
    }

    /**
     * Mendefinisikan judul untuk setiap kolom di Excel.
     * @return array
     */
    public function headings(): array
    {
        return [
            'Kode',
            'Nama Jenis Kas',
            'Tipe Iuran',
            'Default Tipe',
            'Nominal Wajib',
            'Target Lunas',
            'Status',
            'Tanggal Dibuat',
        ];
    }

    /**
     * Memetakan data dari collection ke format yang diinginkan untuk setiap baris.
     * @param mixed $jenisKas
     * @return array
     */
    public function map($jenisKas): array
    {
        return [
            $jenisKas->kode_jenis_kas,
            $jenisKas->nama_jenis_kas,
            ucfirst($jenisKas->tipe_iuran),
            ucfirst($jenisKas->default_tipe),
            $jenisKas->nominal_wajib ? 'Rp ' . number_format($jenisKas->nominal_wajib, 0, ',', '.') : '-',
            $jenisKas->target_lunas ? 'Rp ' . number_format($jenisKas->target_lunas, 0, ',', '.') : '-',
            ucfirst($jenisKas->status),
            $jenisKas->created_at->format('d-m-Y H:i:s'),
        ];
    }

    /**
     * Mengatur lebar kolom agar rapi.
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 10, // Kode
            'B' => 35, // Nama Jenis Kas
            'C' => 15, // Tipe Iuran
            'D' => 15, // Default Tipe
            'E' => 20, // Nominal Wajib
            'F' => 20, // Target Lunas
            'G' => 12, // Status
            'H' => 20, // Tanggal Dibuat
        ];
    }

    /**
     * Memberikan style pada sheet, terutama untuk header.
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style untuk baris header (baris pertama)
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'], // Warna teks putih
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4F46E5'], // Warna latar biru (indigo-600)
            ],
        ]);

        // Membuat text di kolom E dan F rata kanan
        $sheet->getStyle('E:F')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    }

    /**
     * Mendaftarkan event untuk styling tambahan, seperti border.
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Mengambil cell range dari data yang ada
                $cellRange = 'A1:' . $event->sheet->getDelegate()->getHighestColumn() . $event->sheet->getDelegate()->getHighestRow();

                // Memberikan border ke semua cell yang berisi data
                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
