<?php

namespace App\Exports;

use App\Models\Kas;
use Maatwebsite\Excel\Concerns\FromCollection;

class AllDashboardDataExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Kas::with(['user', 'jenisKas'])
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    public function map($kas): array
    {
        return [
            $kas->id,
            $kas->user->id_anggota ?? 'Umum',
            $kas->user->name ?? 'Umum',
            $kas->jenisKas->nama_jenis_kas ?? '-',
            ucfirst($kas->tipe),
            number_format($kas->jumlah, 2),
            $kas->keterangan,
            $kas->tanggal->format('Y-m-d'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'ID Anggota',
            'Nama User',
            'Jenis Kas',
            'Tipe',
            'Jumlah',
            'Keterangan',
            'Tanggal',
        ];
    }
}
