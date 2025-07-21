<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class GenericSheetExport implements FromCollection, WithHeadings, WithTitle
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
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
}
