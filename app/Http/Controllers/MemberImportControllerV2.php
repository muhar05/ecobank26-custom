<?php

namespace App\Http\Controllers;

use App\Exports\MemberTemplateExport;
use App\Exports\MemberFailedRowsExport;
use App\Models\Kk;
use App\Models\Member;
use App\Models\Rt;
use App\Models\ImportHistory;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class MemberImportControllerV2 extends Controller
{
    public function showForm()
    {
        $history = ImportHistory::with('user')
            ->where('import_type', 'warga')
            ->latest()
            ->take(10)
            ->get();

        return view('members.import_v2', compact('history'));
    }

    public function downloadTemplate()
    {
        return Excel::download(new MemberTemplateExport(true), 'template-warga.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $originalFilename = $file->getClientOriginalName();

        $data = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\WithHeadingRow {}, $file);

        if (empty($data) || empty($data[0])) {
            return back()->withErrors(['file' => 'File kosong atau tidak memiliki baris data.']);
        }

        $rows = $data[0];
        $totalRowsCount = count($rows);

        if ($totalRowsCount > 1000) {
            return back()->withErrors(['file' => 'Jumlah baris maksimal 1000 row per upload untuk keamanan server.']);
        }

        $cleanedRows = [];
        foreach ($rows as $row) {
            $cleanedRow = [];
            foreach ($row as $key => $value) {
                $cleanKey = preg_replace('/^\*\s*/', '', $key);
                $cleanKey = preg_replace('/\s*\[.*\]/', '', $cleanKey);
                $cleanKey = preg_replace('/\s*\(.*\)/', '', $cleanKey);
                $cleanKey = strtolower(str_replace(' ', '_', trim($cleanKey)));
                $cleanedRow[$cleanKey] = $value;
            }
            $cleanedRows[] = $cleanedRow;
        }

        // Validate headings - make sure critical columns are present
        $firstRow = $cleanedRows[0] ?? [];
        if (!array_key_exists('rt_number', $firstRow) || !array_key_exists('name', $firstRow) || !array_key_exists('relationship', $firstRow)) {
            return back()->withErrors(['file' => 'Format template salah atau kolom utama tidak ditemukan. Pastikan header sesuai template (rt_number, name, relationship).']);
        }

        $validRows = [];
        $failedRows = [];
        $errors = [];
        $rowNum = 1;

        $stats = [
            'total' => $totalRowsCount,
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'duplicates' => 0
        ];

        foreach ($cleanedRows as $row) {
            $rowNum++;

            $rtNumberRaw = isset($row['rt_number']) ? trim($row['rt_number']) : '';
            $rtNumber = $rtNumberRaw !== '' ? str_pad($rtNumberRaw, 3, '0', STR_PAD_LEFT) : null;
            
            // Clean KK Number
            $kkNumberRaw = isset($row['kk_number']) ? trim($row['kk_number']) : '';
            if (is_numeric($kkNumberRaw) && (strpos(strtolower($kkNumberRaw), 'e') !== false || strlen($kkNumberRaw) > 14)) {
                $kkNumber = number_format((float)$kkNumberRaw, 0, '', '');
            } else {
                $kkNumber = $kkNumberRaw !== '' ? $kkNumberRaw : null;
            }

            $familyHead = isset($row['family_head_fallback']) ? trim($row['family_head_fallback']) : (isset($row['family_head']) ? trim($row['family_head']) : null);
            $name = isset($row['name']) ? trim($row['name']) : null;
            $relationship = isset($row['relationship']) ? trim($row['relationship']) : null;
            $phone = isset($row['phone']) ? trim($row['phone']) : null;
            $address = isset($row['address']) ? trim($row['address']) : null;

            // Handle gender normalization
            $genderRaw = isset($row['gender']) ? trim($row['gender']) : null;
            $gender = null;
            if ($genderRaw !== null && $genderRaw !== '') {
                $genderLower = strtolower($genderRaw);
                if (in_array($genderLower, ['l', 'laki-laki', 'laki laki'])) {
                    $gender = 'Laki-laki';
                } elseif (in_array($genderLower, ['p', 'perempuan'])) {
                    $gender = 'Perempuan';
                } else {
                    $gender = $genderRaw; // Let validator handle it
                }
            }

            // Handle Excel date number conversion or standard string parse
            $birthDateRaw = isset($row['birth_date']) ? trim($row['birth_date']) : null;
            $birthDate = null;
            if ($birthDateRaw !== null && $birthDateRaw !== '') {
                if (is_numeric($birthDateRaw)) {
                    // Excel timestamp to Y-m-d conversion
                    $unixDate = ($birthDateRaw - 25569) * 86400;
                    $birthDate = date('Y-m-d', $unixDate);
                } else {
                    // Try standard parsing
                    try {
                        $parsedDate = new \DateTime($birthDateRaw);
                        $birthDate = $parsedDate->format('Y-m-d');
                    } catch (\Exception $e) {
                        $birthDate = $birthDateRaw; // Let validator catch parsing errors
                    }
                }
            }

            $validator = Validator::make([
                'rt_number' => $rtNumber,
                'kk_number' => $kkNumber,
                'family_head' => $familyHead,
                'name' => $name,
                'relationship' => $relationship,
                'gender' => $gender,
                'birth_date' => $birthDate,
            ], [
                'rt_number' => 'required|string',
                'kk_number' => 'nullable|numeric|digits:16',
                'family_head' => 'required_without:kk_number|nullable|string|max:255',
                'name' => 'required|string|max:255',
                'relationship' => 'required|string|max:255',
                'gender' => 'nullable|in:Laki-laki,Perempuan',
                'birth_date' => 'nullable|date|before_or_equal:today',
            ], [
                'rt_number.required' => 'RT Number wajib diisi.',
                'kk_number.digits' => 'KK Number harus tepat 16 digit.',
                'kk_number.numeric' => 'KK Number harus berupa angka.',
                'family_head.required_without' => 'Family Head Fallback wajib diisi jika KK Number tidak ada.',
                'name.required' => 'Name wajib diisi.',
                'relationship.required' => 'Relationship wajib diisi.',
                'gender.in' => 'Gender harus bernilai: Laki-laki, Perempuan, L, atau P.',
                'birth_date.date' => 'Format tanggal lahir tidak valid.',
                'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            ]);

            if ($validator->fails()) {
                $stats['failed']++;
                $errMsgs = implode(' ', $validator->errors()->all());
                $errors[] = "Baris {$rowNum}: {$errMsgs}";
                $failedRows[] = [
                    $row['rt_number'] ?? '',
                    $row['kk_number'] ?? '',
                    $row['family_head_fallback'] ?? ($row['family_head'] ?? ''),
                    $row['name'] ?? '',
                    $row['relationship'] ?? '',
                    $row['gender'] ?? '',
                    $row['birth_date'] ?? '',
                    $row['phone'] ?? '',
                    $row['address'] ?? '',
                    $errMsgs
                ];
                continue;
            }

            // Find Kartu Keluarga (KK) reference
            $kk = null;
            if ($kkNumber) {
                $kk = Kk::where('kk_number', $kkNumber)->first();
                if (!$kk) {
                    $stats['failed']++;
                    $err = 'Nomor KK tidak ditemukan di database. Import Kartu Keluarga terlebih dahulu.';
                    $errors[] = "Baris {$rowNum}: {$err}";
                    $failedRows[] = [
                        $row['rt_number'] ?? '',
                        $row['kk_number'] ?? '',
                        $row['family_head_fallback'] ?? ($row['family_head'] ?? ''),
                        $row['name'] ?? '',
                        $row['relationship'] ?? '',
                        $row['gender'] ?? '',
                        $row['birth_date'] ?? '',
                        $row['phone'] ?? '',
                        $row['address'] ?? '',
                        $err
                    ];
                    continue;
                }
            } else {
                // Try mapping using RT + Family Head Name
                $rt = Rt::where('rt_number', $rtNumber)->first();
                if (!$rt) {
                    $stats['failed']++;
                    $err = 'Nomor RT tidak ditemukan di database.';
                    $errors[] = "Baris {$rowNum}: {$err}";
                    $failedRows[] = [
                        $row['rt_number'] ?? '',
                        $row['kk_number'] ?? '',
                        $row['family_head_fallback'] ?? ($row['family_head'] ?? ''),
                        $row['name'] ?? '',
                        $row['relationship'] ?? '',
                        $row['gender'] ?? '',
                        $row['birth_date'] ?? '',
                        $row['phone'] ?? '',
                        $row['address'] ?? '',
                        $err
                    ];
                    continue;
                }

                $kk = Kk::where('rt_id', $rt->id)
                    ->where('family_head', 'like', "%{$familyHead}%")
                    ->first();

                if (!$kk) {
                    $stats['failed']++;
                    $err = 'Kartu Keluarga dengan Kepala Keluarga ' . $familyHead . ' di RT ' . $rtNumber . ' tidak ditemukan.';
                    $errors[] = "Baris {$rowNum}: {$err}";
                    $failedRows[] = [
                        $row['rt_number'] ?? '',
                        $row['kk_number'] ?? '',
                        $row['family_head_fallback'] ?? ($row['family_head'] ?? ''),
                        $row['name'] ?? '',
                        $row['relationship'] ?? '',
                        $row['gender'] ?? '',
                        $row['birth_date'] ?? '',
                        $row['phone'] ?? '',
                        $row['address'] ?? '',
                        $err
                    ];
                    continue;
                }
            }

            // Duplicate Member Name check inside the database for the same KK
            $duplicateDb = Member::where('kk_id', $kk->id)->where('name', $name)->exists();
            if ($duplicateDb) {
                $stats['duplicates']++;
                $stats['failed']++;
                $err = 'Nama warga ini sudah terdaftar di KK yang sama.';
                $errors[] = "Baris {$rowNum}: {$err}";
                $failedRows[] = [
                    $row['rt_number'] ?? '',
                    $row['kk_number'] ?? '',
                    $row['family_head_fallback'] ?? ($row['family_head'] ?? ''),
                    $row['name'] ?? '',
                    $row['relationship'] ?? '',
                    $row['gender'] ?? '',
                    $row['birth_date'] ?? '',
                    $row['phone'] ?? '',
                    $row['address'] ?? '',
                    $err
                ];
                continue;
            }

            // Duplicate Member Name check inside the current file upload
            foreach ($validRows as $vr) {
                if ($vr['kk_id'] === $kk->id && $vr['name'] === $name) {
                    $stats['duplicates']++;
                    $stats['failed']++;
                    $err = 'Nama warga duplikat untuk KK yang sama di dalam file import.';
                    $errors[] = "Baris {$rowNum}: {$err}";
                    $failedRows[] = [
                        $row['rt_number'] ?? '',
                        $row['kk_number'] ?? '',
                        $row['family_head_fallback'] ?? ($row['family_head'] ?? ''),
                        $row['name'] ?? '',
                        $row['relationship'] ?? '',
                        $row['gender'] ?? '',
                        $row['birth_date'] ?? '',
                        $row['phone'] ?? '',
                        $row['address'] ?? '',
                        $err
                    ];
                    continue 2;
                }
            }

            $validRows[] = [
                'kk_id' => $kk->id,
                'name' => $name,
                'relationship' => $relationship,
                'gender' => $gender,
                'birth_date' => $birthDate,
                'phone' => $phone,
                'address' => $address,
            ];
        }

        if (!empty($errors)) {
            session(['member_import_failed_rows' => $failedRows]);

            // Save failed execution stats
            ImportHistory::create([
                'filename' => $originalFilename,
                'import_type' => 'warga',
                'user_id' => auth()->id(),
                'total_rows' => $stats['total'],
                'total_success' => 0,
                'total_failed' => $stats['failed'],
                'total_skipped' => $stats['skipped'],
                'total_duplicates' => $stats['duplicates'],
            ]);

            return back()->with('import_result', [
                'success' => false,
                'created' => 0,
                'errors' => $errors,
                'has_failed_download' => true,
                'stats' => $stats,
            ]);
        }

        // Commit all valid members
        DB::beginTransaction();
        try {
            $created = 0;
            foreach ($validRows as $row) {
                Member::create([
                    'kk_id' => $row['kk_id'],
                    'member_code' => Member::generateNextCode(),
                    'name' => $row['name'],
                    'relationship' => $row['relationship'],
                    'gender' => $row['gender'],
                    'birth_date' => $row['birth_date'],
                    'phone' => $row['phone'],
                    'address' => $row['address'],
                ]);
                $created++;
            }

            $stats['success'] = $created;

            ImportHistory::create([
                'filename' => $originalFilename,
                'import_type' => 'warga',
                'user_id' => auth()->id(),
                'total_rows' => $stats['total'],
                'total_success' => $stats['success'],
                'total_failed' => 0,
                'total_skipped' => 0,
                'total_duplicates' => 0,
            ]);

            DB::commit();

            // Log activity log
            try {
                $logger = app(ActivityLogService::class);
                $logger->logInfo("Import Massal Anggota Warga berhasil: {$created} data ditambahkan", [
                    'count' => $created
                ]);
            } catch (\Throwable $e) {}

            return back()->with('import_result', [
                'success' => true,
                'created' => $created,
                'errors' => [],
                'has_failed_download' => false,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['file' => 'Terjadi kesalahan database: ' . $e->getMessage()]);
        }
    }

    public function downloadFailed()
    {
        $failedRows = session('member_import_failed_rows');
        if (empty($failedRows)) {
            return back()->withErrors(['file' => 'Tidak ada data error yang tersedia untuk didownload.']);
        }

        return Excel::download(new MemberFailedRowsExport($failedRows), 'failed-rows-warga.xlsx');
    }
}
