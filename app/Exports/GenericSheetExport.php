<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GenericSheetExport implements FromCollection, WithHeadings, WithTitle, WithColumnFormatting, ShouldAutoSize, WithStyles
{
    protected $collection;
    protected $title;

    public function __construct(array $data, string $title)
    {
        $this->collection = new Collection($data);
        $this->title = $title;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->collection;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        if ($this->collection->isNotEmpty()) {
            return array_keys($this->collection->first());
        }
        return [];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        $formats = [];
        if ($this->collection->isNotEmpty()) {
            $firstRow = $this->collection->first();
            foreach (array_keys($firstRow) as $index => $heading) {
                if (stripos($heading, 'jumlah') !== false || stripos($heading, 'total') !== false || stripos($heading, 'saldo') !== false || stripos($heading, 'bayar') !== false) {
                    $formats[$index + 1] = '_("Rp"* #,##0.00_);_("Rp"* (#,##0.00);_("Rp"* "-"??_);_(@_)';
                } elseif (stripos($heading, 'tanggal') !== false || stripos($heading, 'bulan') !== false) {
                    $formats[$index + 1] = NumberFormat::FORMAT_DATE_DDMMYYYY;
                } elseif (stripos($heading, 'persentase') !== false) {
                    $formats[$index + 1] = NumberFormat::FORMAT_PERCENTAGE_00;
                }
            }
        }
        return $formats;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Styling untuk baris pertama (header)
        ];
    }
}
