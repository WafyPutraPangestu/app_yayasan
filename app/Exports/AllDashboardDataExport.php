<?php

namespace App\Exports;

use App\Models\Kas;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Facades\Log;

class AllDashboardDataExport implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    private const CURRENCY_FORMAT = '"Rp "#,##0;[Red]"-Rp "#,##0';
    private const DATE_FORMAT = 'dd-mm-yyyy';
    private const PRIMARY_COLOR = '2F75B5';
    private const SECONDARY_COLOR = '5B9BD5';
    private const ALTERNATE_ROW_COLOR = 'F2F2F2';

    /**
     * Mengubah data transaksi menjadi format array untuk export Excel
     */
    public function array(): array
    {
        $dataKas = $this->ambilDataKas();
        $ringkasan = $this->hitungRingkasan($dataKas);
        $dataPerBulan = $this->kelompokkanPerBulan($dataKas);

        $hasil = [];

        // Baris 1: DATA TRANSAKSI UTAMA
        $hasil[] = ['DATA TRANSAKSI UTAMA'];
        $hasil[] = ['LAPORAN TRANSAKSI PERBULAN']; // Baris 2

        // Tambahkan data per bulan
        foreach ($dataPerBulan as $tahunBulan => $items) {
            $hasil = array_merge($hasil, $this->buatDataBulanan($tahunBulan, $items));
        }

        // Baris 7: RINGKASAN KESELURUHAN
        $hasil[] = ['', '', '', '', '', '', '', '', '', '', '', 'RINGKASAN KESELURUHAN', '', ''];
        // Baris 8, 9, 10: Isi Ringkasan Keseluruhan (Kolom L, M, N)
        $hasil[] = ['', '', '', '', '', '', '', '', '', '', '', 'Total Pemasukan', $this->formatRupiah($ringkasan['pemasukan']), ''];
        $hasil[] = ['', '', '', '', '', '', '', '', '', '', '', 'Total Pengeluaran', $this->formatRupiah($ringkasan['pengeluaran']), ''];
        $hasil[] = ['', '', '', '', '', '', '', '', '', '', '', 'Saldo Akhir', $this->formatRupiah($ringkasan['saldo']), ''];


        return $hasil;
    }

    /**
     * Mengambil semua data kas dengan relasi
     */
    private function ambilDataKas()
    {
        return Kas::with(['user', 'jenisKas'])->get();
    }

    /**
     * Menghitung ringkasan total pemasukan, pengeluaran, dan saldo
     */
    private function hitungRingkasan($dataKas): array
    {
        $totalPemasukan = $dataKas->where('tipe', 'pemasukan')->sum('jumlah');
        $totalPengeluaran = $dataKas->where('tipe', 'pengeluaran')->sum('jumlah');
        $saldoAkhir = $totalPemasukan - $totalPengeluaran;

        return [
            'pemasukan' => $totalPemasukan,
            'pengeluaran' => $totalPengeluaran,
            'saldo' => $saldoAkhir
        ];
    }

    /**
     * Mengelompokkan data kas berdasarkan bulan dan tahun
     */
    private function kelompokkanPerBulan($dataKas)
    {
        return $dataKas->sortBy('tanggal')
            ->groupBy(function ($kas) {
                return Carbon::parse($kas->tanggal)->format('Y-m');
            });
    }

    /**
     * Membuat data untuk satu bulan
     */
    private function buatDataBulanan(string $tahunBulan, $items): array
    {
        $namaBulan = Carbon::createFromFormat('Y-m', $tahunBulan)
            ->translatedFormat('F Y');

        $hasil = [];
        $hasil[] = []; // Baris kosong sebelum setiap laporan bulanan
        $hasil[] = ["LAPORAN TRANSAKSI BULAN " . strtoupper($namaBulan)];
        $hasil[] = [];
        $hasil[] = $this->getHeaderKolom();

        // Hitung total per bulan yang benar
        $totalPemasukan = $items->where('tipe', 'pemasukan')->sum('jumlah');
        $totalPengeluaran = $items->where('tipe', 'pengeluaran')->sum('jumlah');
        $saldoBulan = $totalPemasukan - $totalPengeluaran;

        foreach ($items as $kas) {
            $hasil[] = $this->formatBarisKas($kas);
        }

        // Tambahkan total bulanan yang benar
        $hasil[] = [];
        $hasil[] = $this->buatRingkasanBulanan($totalPemasukan, $totalPengeluaran, $saldoBulan);
        $hasil[] = [];
        $hasil[] = [];

        return $hasil;
    }

    /**
     * Mendapatkan header kolom tabel
     */
    private function getHeaderKolom(): array
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
     * Format baris data kas
     */
    private function formatBarisKas(Kas $kas): array
    {
        return [
            $kas->id,
            $kas->user->id_anggota ?? 'UMUM',
            $kas->user->name ?? 'UMUM',
            $kas->jenisKas->nama_jenis_kas ?? '-',
            strtoupper($kas->tipe),
            $this->formatRupiah($kas->jumlah),
            $kas->keterangan ?? '-',
            Carbon::parse($kas->tanggal)->format('d-m-Y'),
        ];
    }



    /**
     * Format angka menjadi format rupiah
     */
    private function formatRupiah(float $angka): string
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }

    /**
     * Membuat ringkasan bulanan yang benar
     */
    private function buatRingkasanBulanan(float $pemasukan, float $pengeluaran, float $saldo): array
    {
        return [
            ['', '', '', 'RINGKASAN BULAN INI:', '', '', '', ''],
            ['', '', '', 'Total Pemasukan:', $this->formatRupiah($pemasukan), '', '', ''],
            ['', '', '', 'Total Pengeluaran:', $this->formatRupiah($pengeluaran), '', '', ''],
            ['', '', '', 'Saldo Bulan Ini:', $this->formatRupiah($saldo), '', '', '']
        ];
    }

    /**
     * Mendapatkan judul sheet Excel
     */
    public function title(): string
    {
        return 'Laporan Keuangan';
    }

    /**
     * Mengatur styling untuk worksheet
     */
    public function styles(Worksheet $sheet)
    {
        $this->stylingSummarySection($sheet);
        $this->stylingMonthHeaders($sheet);
        $this->stylingTableHeaders($sheet);
        $this->stylingDataRows($sheet);
        $this->stylingMonthlySummary($sheet);

        return [];
    }

    /**
     * Styling untuk bagian ringkasan
     */
    private function stylingSummarySection(Worksheet $sheet)
    {
        // Header "DATA TRANSAKSI UTAMA" di A1
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '000000']
            ]
        ]);

        // Header "RINGKASAN KESELURUHAN" di L7
        $sheet->getStyle('L7')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => self::PRIMARY_COLOR]
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => self::SECONDARY_COLOR]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFFFFF']
                ]
            ]
        ]);

        // Data ringkasan di baris 8-10, kolom L:M:N
        $sheet->getStyle('L8:M10')->applyFromArray([
            'font' => ['bold' => true],
            'borders' => [
                'left' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
                'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
            ]
        ]);
        $sheet->getStyle('L8:L10')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        $sheet->getStyle('M8:M10')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);
        $sheet->getStyle('N8:N10')->applyFromArray([
            'borders' => [
                'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
            ]
        ]);


        // Format currency untuk ringkasan di kolom M, baris 8-10
        $sheet->getStyle('M8')->getNumberFormat()
            ->setFormatCode(self::CURRENCY_FORMAT);
        $sheet->getStyle('M9')->getNumberFormat()
            ->setFormatCode(self::CURRENCY_FORMAT);
        $sheet->getStyle('M10')->getNumberFormat()
            ->setFormatCode(self::CURRENCY_FORMAT);
    }

    /**
     * Styling untuk header bulan
     */
    private function stylingMonthHeaders(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        for ($i = 1; $i <= $lastRow; $i++) {
            $cellValue = $sheet->getCell('A' . $i)->getValue();

            if (strpos($cellValue, 'LAPORAN TRANSAKSI BULAN') !== false) {
                $range = 'A' . $i . ':H' . $i;

                $sheet->getStyle($range)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => self::PRIMARY_COLOR]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);

                $sheet->mergeCells($range);
            }
        }
    }

    /**
     * Styling untuk header tabel
     */
    private function stylingTableHeaders(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        for ($i = 1; $i <= $lastRow; $i++) {
            $rowData = $sheet->rangeToArray('A' . $i . ':H' . $i, null, true, false)[0];

            if (in_array('ID Transaksi', $rowData)) {
                $sheet->getStyle('A' . $i . ':H' . $i)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => self::SECONDARY_COLOR]
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'FFFFFF']
                        ]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);
            }
        }
    }

    /**
     * Styling untuk baris data
     */
    private function stylingDataRows(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        for ($i = 1; $i <= $lastRow; $i++) {
            $cellValue = $sheet->getCell('A' . $i)->getValue();

            if (is_numeric($cellValue)) {
                $range = 'A' . $i . ':H' . $i;

                $baseStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E0E0E0']
                        ]
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ];

                // Baris berganti warna
                if ($i % 2 == 0) {
                    $baseStyle['fill'] = [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => self::ALTERNATE_ROW_COLOR]
                    ];
                }

                $sheet->getStyle($range)->applyFromArray($baseStyle);

                // Format currency untuk kolom nominal
                $sheet->getStyle('F' . $i)->getNumberFormat()
                    ->setFormatCode(self::CURRENCY_FORMAT);

                // Format tanggal
                $sheet->getStyle('H' . $i)->getNumberFormat()
                    ->setFormatCode(self::DATE_FORMAT);
            }
        }
    }

    /**
     * Styling untuk ringkasan bulanan
     */
    private function stylingMonthlySummary(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        for ($i = 1; $i <= $lastRow; $i++) {
            $cellValue = $sheet->getCell('D' . $i)->getValue();

            // Header ringkasan bulan
            if ($cellValue === 'RINGKASAN BULAN INI:') {
                $sheet->getStyle('D' . $i)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => self::PRIMARY_COLOR]
                    ]
                ]);
            }

            // Data ringkasan bulanan
            if (in_array($cellValue, ['Total Pemasukan:', 'Total Pengeluaran:', 'Saldo Bulan Ini:'])) {
                $sheet->getStyle('D' . $i . ':E' . $i)->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC']
                        ]
                    ]
                ]);

                // Format currency untuk nilai
                $sheet->getStyle('E' . $i)->getNumberFormat()
                    ->setFormatCode(self::CURRENCY_FORMAT);

                // Warna khusus untuk saldo
                if ($cellValue === 'Saldo Bulan Ini:') {
                    $saldoValue = $sheet->getCell('E' . $i)->getCalculatedValue();
                    if (is_numeric(str_replace(['Rp ', '.', ','], ['', '', '.'], $saldoValue))) {
                        $nilai = (float) str_replace(['Rp ', '.', ','], ['', '', '.'], $saldoValue);
                        if ($nilai < 0) {
                            $sheet->getStyle('E' . $i)->applyFromArray([
                                'font' => ['color' => ['rgb' => 'FF0000']]
                            ]);
                        } else {
                            $sheet->getStyle('E' . $i)->applyFromArray([
                                'font' => ['color' => ['rgb' => '008000']]
                            ]);
                        }
                    }
                }
            }
        }
    }



    /**
     * Mengatur lebar kolom
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,  // ID Transaksi
            'B' => 15,  // ID Anggota
            'C' => 25,  // Nama Member
            'D' => 25,  // Jenis Transaksi
            'E' => 15,  // Tipe
            'F' => 20,  // Nominal
            'G' => 35,  // Keterangan
            'H' => 20,  // Tanggal Transaksi
            'I' => 5,   // Spacer
            'J' => 5,   // Spacer
            'K' => 5,   // Spacer
            'L' => 20,  // Ringkasan Label
            'M' => 25,  // Ringkasan Value
            'N' => 25    // Spacer untuk saldo akhir
        ];
    }
}
