<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Illuminate\Support\Facades\Log;

class AnggotaExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    use Exportable;

    private $users;
    private $usiaCategories;
    private $statusCategories;
    private $total;

    public function __construct()
    {
        // DEBUGGING: Cek semua user dulu
        $allUsers = User::all();
        Log::info("=== DEBUGGING EXPORT ===");
        Log::info("Total ALL users: " . $allUsers->count());

        foreach ($allUsers as $user) {
            Log::info("User: ID={$user->id}, Name={$user->name}, Role={$user->role}, Status={$user->status}");
        }

        // SEMENTARA: Ambil SEMUA user tanpa filter untuk testing
        $this->users = User::all();
        Log::info("Users yang akan diexport: " . $this->users->count());

        $this->total = $this->users->count();

        // Initialize age categories
        $this->usiaCategories = [
            '0-20 TAHUN' => 0,
            '21-40 TAHUN' => 0,
            '41-60 TAHUN' => 0,
            'DIATAS 61 TAHUN' => 0,
            'DATA TIDAK LENGKAP' => 0,
        ];

        // Initialize status categories
        $this->statusCategories = [
            'Pending' => 0,
            'Aktif' => 0,
            'Nonaktif' => 0,
            'Wafat' => 0,
            'Mengundurkan diri' => 0,
        ];

        // Count age and status categories
        foreach ($this->users as $user) {
            // Count age categories
            if (!$user->tanggal_lahir) {
                $this->usiaCategories['DATA TIDAK LENGKAP']++;
            } else {
                try {
                    $age = Carbon::parse($user->tanggal_lahir)->age;
                    if ($age <= 20) {
                        $this->usiaCategories['0-20 TAHUN']++;
                    } elseif ($age <= 40) {
                        $this->usiaCategories['21-40 TAHUN']++;
                    } elseif ($age <= 60) {
                        $this->usiaCategories['41-60 TAHUN']++;
                    } else {
                        $this->usiaCategories['DIATAS 61 TAHUN']++;
                    }
                } catch (\Exception $e) {
                    $this->usiaCategories['DATA TIDAK LENGKAP']++;
                }
            }

            // Count status categories
            $userStatus = $user->status ?? 'Pending';
            if (isset($this->statusCategories[$userStatus])) {
                $this->statusCategories[$userStatus]++;
            } else {
                $this->statusCategories['Pending']++; // Default fallback
            }
        }

        Log::info("Kategori usia: " . json_encode($this->usiaCategories));
        Log::info("Kategori status: " . json_encode($this->statusCategories));
    }

    public function collection()
    {
        Log::info("=== COLLECTION METHOD ===");
        Log::info("Users count dalam collection: " . $this->users->count());

        if ($this->users->isEmpty()) {
            Log::warning("USERS KOSONG!");
            return collect([]);
        }

        $result = collect();

        foreach ($this->users as $index => $user) {
            Log::info("Processing user {$index}: {$user->name}");

            // Buat data sederhana dulu
            $rowData = [
                $index + 1,                    // No
                $user->id_anggota ?? 'N/A',    // No Anggota
                $user->name ?? 'N/A',          // Nama
                $user->bin_binti ?? '',        // Bin/Binti
                $user->jenis_kelamin ?? '',    // Jenis Kelamin
                $user->tempat_lahir ?? '',     // Tempat Lahir
                $user->alamat ?? '',           // Alamat
                $user->no_hp ?? '',            // No HP
                $user->tanggal_lahir ?? '',    // Tanggal Lahir (raw)
                $user->status ?? '',           // Status
            ];

            Log::info("Row data: " . json_encode($rowData));
            $result->push($rowData);
        }

        Log::info("Final result count: " . $result->count());
        Log::info("Final result: " . $result->toJson());

        return $result;
    }

    public function headings(): array
    {
        return [
            [
                'DAFTAR ANGGOTA SMDK MASJID AS-SALAM JOGLO',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ],
            [
                'No.',
                'NO. ANGGOTA',
                'NAMA LENGKAP',
                'BIN/BINTI',
                'JENIS KELAMIN',
                'TEMPAT LAHIR',
                'ALAMAT',
                'NO. TELP',
                'TGL LAHIR',
                'STATUS'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Title style - Modern gradient blue
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1F4E79'] // Dark blue
                ]
            ],
            // Header style - Light blue gradient
            2 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'] // Medium blue
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                Log::info("=== REGISTER EVENTS ===");
                $highestRow = $sheet->getHighestRow();
                Log::info("Highest row: " . $highestRow);

                // === TITLE STYLING ===
                $sheet->mergeCells('A1:J1');
                $sheet->getRowDimension(1)->setRowHeight(35);

                // Add border to title
                $sheet->getStyle('A1:J1')->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['rgb' => '1F4E79']
                        ]
                    ]
                ]);

                // === HEADER STYLING ===
                $sheet->getStyle('A2:J2')->getAlignment()->setWrapText(true);
                $sheet->getRowDimension(2)->setRowHeight(45);

                // Header borders
                $sheet->getStyle('A2:J2')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '4472C4']
                        ]
                    ]
                ]);

                // === DATA ROWS STYLING ===
                if ($highestRow >= 3) {
                    // Alternating row colors (zebra striping)
                    for ($row = 3; $row <= $highestRow; $row++) {
                        $fillColor = ($row % 2 == 0) ? 'F2F2F2' : 'FFFFFF';
                        $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $fillColor]
                            ]
                        ]);
                    }

                    // All data borders and font
                    $sheet->getStyle("A2:J{$highestRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CCCCCC']
                            ]
                        ],
                        'font' => ['size' => 10]
                    ]);

                    // Center alignment for No. column
                    $sheet->getStyle("A3:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Bold font for No. Anggota column
                    $sheet->getStyle("B3:B{$highestRow}")->getFont()->setBold(true);
                }

                // === COLUMN WIDTHS ===
                $columnWidths = [
                    'A' => 6,   // No
                    'B' => 15,  // No Anggota
                    'C' => 25,  // Nama
                    'D' => 15,  // Bin/Binti
                    'E' => 12,  // Jenis Kelamin
                    'F' => 20,  // Tempat Lahir
                    'G' => 30,  // Alamat
                    'H' => 15,  // No Telp
                    'I' => 12,  // Tgl Lahir
                    'J' => 12,  // Status
                ];

                foreach ($columnWidths as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }

                // === SUMMARY SECTION ===
                $startRow = 3;
                $summaryCol = 'L'; // Column L for summary

                // === REKAP USIA ===
                $sheet->setCellValue("{$summaryCol}{$startRow}", 'REKAP DATA BERDASARKAN USIA');
                $sheet->setCellValue(chr(ord($summaryCol) + 1) . "{$startRow}", 'JUMLAH');

                // Style summary header
                $sheet->getStyle("{$summaryCol}{$startRow}:" . chr(ord($summaryCol) + 1) . "{$startRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '70AD47'] // Green
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '70AD47']
                        ]
                    ]
                ]);

                // Summary data with colors
                $row = $startRow + 1;
                $colors = [
                    '0-20 TAHUN' => 'E7E6FF',        // Light purple
                    '21-40 TAHUN' => 'D5E8D4',       // Light green
                    '41-60 TAHUN' => 'FFF2CC',       // Light yellow
                    'DIATAS 61 TAHUN' => 'F8CECC',   // Light red
                    'DATA TIDAK LENGKAP' => 'F0F0F0' // Light gray
                ];

                foreach ($this->usiaCategories as $kategori => $jumlah) {
                    $sheet->setCellValue("{$summaryCol}{$row}", $kategori);
                    $sheet->setCellValue(chr(ord($summaryCol) + 1) . "{$row}", $jumlah);

                    // Apply color based on category
                    $bgColor = $colors[$kategori] ?? 'FFFFFF';
                    $sheet->getStyle("{$summaryCol}{$row}:" . chr(ord($summaryCol) + 1) . "{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $bgColor]
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CCCCCC']
                            ]
                        ],
                        'font' => ['size' => 10]
                    ]);

                    // Center align numbers
                    $sheet->getStyle(chr(ord($summaryCol) + 1) . "{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $row++;
                }

                // Total row with special styling
                $sheet->setCellValue("{$summaryCol}{$row}", 'TOTAL BERDASARKAN USIA');
                $sheet->setCellValue(chr(ord($summaryCol) + 1) . "{$row}", $this->total);

                $sheet->getStyle("{$summaryCol}{$row}:" . chr(ord($summaryCol) + 1) . "{$row}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'C55A5A'] // Dark red
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['rgb' => 'C55A5A']
                        ]
                    ]
                ]);

                // === REKAP STATUS ===
                $row += 2; // Skip one row for spacing

                $sheet->setCellValue("{$summaryCol}{$row}", 'REKAP DATA BERDASARKAN STATUS');
                $sheet->setCellValue(chr(ord($summaryCol) + 1) . "{$row}", 'JUMLAH');

                // Style status header
                $sheet->getStyle("{$summaryCol}{$row}:" . chr(ord($summaryCol) + 1) . "{$row}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '5B9BD5'] // Blue
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '5B9BD5']
                        ]
                    ]
                ]);

                // Status colors
                $statusColors = [
                    'Pending' => 'FFE699',           // Light orange
                    'Aktif' => 'C6EFCE',            // Light green
                    'Nonaktif' => 'FFC7CE',         // Light red
                    'Wafat' => 'D9D9D9',            // Light gray
                    'Mengundurkan diri' => 'E1D5E7' // Light purple
                ];

                $row++;
                foreach ($this->statusCategories as $status => $jumlah) {
                    $sheet->setCellValue("{$summaryCol}{$row}", $status);
                    $sheet->setCellValue(chr(ord($summaryCol) + 1) . "{$row}", $jumlah);

                    // Apply color based on status
                    $bgColor = $statusColors[$status] ?? 'FFFFFF';
                    $sheet->getStyle("{$summaryCol}{$row}:" . chr(ord($summaryCol) + 1) . "{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $bgColor]
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CCCCCC']
                            ]
                        ],
                        'font' => ['size' => 10]
                    ]);

                    // Center align numbers
                    $sheet->getStyle(chr(ord($summaryCol) + 1) . "{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $row++;
                }

                // Total status row
                $sheet->setCellValue("{$summaryCol}{$row}", 'TOTAL BERDASARKAN STATUS');
                $sheet->setCellValue(chr(ord($summaryCol) + 1) . "{$row}", $this->total);

                $sheet->getStyle("{$summaryCol}{$row}:" . chr(ord($summaryCol) + 1) . "{$row}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '5B9BD5'] // Blue
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['rgb' => '5B9BD5']
                        ]
                    ]
                ]);

                // === TAMBAHAN: TOTAL KESELURUHAN ANGGOTA ===
                $row += 2; // Beri jarak 2 baris

                $sheet->setCellValue("{$summaryCol}{$row}", 'TOTAL KESELURUHAN ANGGOTA');
                $sheet->setCellValue(chr(ord($summaryCol) + 1) . "{$row}", $this->total);

                // Style untuk total keseluruhan (warna ungu)
                $sheet->getStyle("{$summaryCol}{$row}:" . chr(ord($summaryCol) + 1) . "{$row}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '7030A0'] // Purple
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['rgb' => '7030A0']
                        ]
                    ]
                ]);

                // Set width for summary columns
                $sheet->getColumnDimension($summaryCol)->setWidth(25);
                $sheet->getColumnDimension(chr(ord($summaryCol) + 1))->setWidth(12);

                // === FREEZE PANES ===
                $sheet->freezePane('A3'); // Freeze header rows

                Log::info("Selesai setup events");
            },
        ];
    }

    private function getKategoriUmur($tanggalLahir)
    {
        if (!$tanggalLahir) return 'DATA TIDAK LENGKAP';

        try {
            $age = Carbon::parse($tanggalLahir)->age;
            if ($age <= 20) return '0-20 TAHUN';
            if ($age <= 40) return '21-40 TAHUN';
            if ($age <= 60) return '41-60 TAHUN';
            return 'DIATAS 61 TAHUN';
        } catch (\Exception $e) {
            return 'DATA TIDAK LENGKAP';
        }
    }
}
