<?php

namespace App\Exports;

use App\Models\Member;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class MembersExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'No', 'Nama Warga', 'NIK', 'Nomor KK', 'RT', 'Status Hunian', 
            'Gender', 'Tanggal Lahir', 'Umur', 'Nomor HP', 'Alamat', 
            'Status Dalam Keluarga', 'Tanggal Dibuat'
        ];
    }

    public function map($member): array
    {
        return [
            $member->id,
            $member->name,
            '-', // NIK placeholder
            $member->kk ? "'" . $member->kk->kk_number : '-', // Format as text
            $member->kk ? $member->kk->rt->rt_number : '-',
            'Aktif', // Placeholder for status
            $member->gender === 'L' ? 'Laki-laki' : 'Perempuan',
            $member->birth_date ? $member->birth_date->format('d-m-Y') : '-',
            $member->birth_date ? $member->birth_date->age . ' Tahun' : '-',
            "'" . $member->phone, // Format as text
            $member->address ?? '-',
            $member->relationship ?? '-',
            $member->created_at->format('d-m-Y H:i'),
        ];
    }
}
