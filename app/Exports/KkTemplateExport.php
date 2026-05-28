<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KkTemplateExport implements WithMultipleSheets
{
    protected bool $includeExamples;

    public function __construct(bool $includeExamples = false)
    {
        $this->includeExamples = $includeExamples;
    }

    public function sheets(): array
    {
        return [
            new KkDataImportSheet($this->includeExamples),
            new KkInstructionsSheet(),
        ];
    }
}

class KkDataImportSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected bool $includeExamples;

    public function __construct(bool $includeExamples)
    {
        $this->includeExamples = $includeExamples;
    }

    public function title(): string
    {
        return 'Data Import';
    }

    public function headings(): array
    {
        return [
            '* RT Number (rt_number)',
            'KK Number (kk_number) [16 Digits]',
            '* Family Head (family_head)',
            'Address (address)',
            'Phone (phone)',
            '* Status (status: active/kontrak/pindah/kosong)',
            'Jumlah Anggota (jumlah_anggota)'
        ];
    }

    public function array(): array
    {
        if ($this->includeExamples) {
            return [
                ['001', '3201234567890001', 'Ahmad Subarjo', 'Jl. Clean No. 12', '08123456789', 'active', '4'],
                ['002', '3201234567890002', 'Siti Aminah', 'Jl. Hijau No. 8', '08133333333', 'kontrak', '3'],
                ['001', '', 'Dedi Hermawan', 'Jl. Asri No. 1', '08124444444', 'active', '2'],
            ];
        }
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '059669'] // Emerald Green
                ]
            ]
        ];
    }
}

class KkInstructionsSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'Petunjuk Pengisian';
    }

    public function array(): array
    {
        return [
            ['PETUNJUK PENGISIAN IMPORT KARTU KELUARGA (KK)'],
            [''],
            ['1. Kolom dengan tanda bintang (*) wajib diisi.'],
            ['2. rt_number: Masukkan nomor RT, contoh: "001", "002". Jika RT belum ada di sistem, sistem akan otomatis membuatnya.'],
            ['3. kk_number: Boleh dikosongkan (nullable). Jika diisi, wajib berupa 16 digit angka unik.'],
            ['4. family_head: Nama Kepala Keluarga (wajib diisi).'],
            ['5. address & phone: Alamat dan No HP (boleh dikosongkan).'],
            ['6. status: Status tempat tinggal. Wajib diisi dengan salah satu nilai berikut:'],
            ['   - active  (Aktif / Tinggal tetap)'],
            ['   - kontrak (Mengontrak / Sewa)'],
            ['   - pindah  (Sudah pindah domisili)'],
            ['   - kosong  (Rumah kosong / tidak berpenghuni)'],
            ['7. jumlah_anggota: Jumlah anggota keluarga. Boleh dikosongkan, jika diisi harus angka >= 0.'],
            ['8. Maksimal data yang diizinkan dalam sekali unggah adalah 1000 baris.'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => '059669']
                ]
            ]
        ];
    }
}
