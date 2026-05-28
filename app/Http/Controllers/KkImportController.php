<?php

namespace App\Http\Controllers;

use App\Exports\KkTemplateExport;
use App\Exports\KkFailedRowsExport;
use App\Models\Kk;
use App\Models\Rt;
use App\Models\ImportHistory;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class KkImportController extends Controller
{
    public function showForm()
    {
        $history = ImportHistory::with('user')
            ->where('import_type', 'kk')
            ->latest()
            ->take(10)
            ->get();

        return view('kks.import', compact('history'));
    }

    public function downloadTemplate()
    {
        return Excel::download(new KkTemplateExport(true), 'template-kk.xlsx');
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

        // Mapping dictionary from localized human headers to DB columns
        $headerMap = [
            'nomor_rt' => 'rt_number',
            'nomor_kk' => 'kk_number',
            'nama_kepala_keluarga' => 'family_head',
            'alamat_rumah' => 'address',
            'nomor_hp_aktif' => 'phone',
            'status_rumah' => 'status',
            'jumlah_anggota_keluarga' => 'jumlah_anggota',
        ];

        // Clean & normalize keys
        $cleanedRows = [];
        foreach ($rows as $row) {
            $cleanedRow = [];
            foreach ($row as $key => $value) {
                // Normalize key: remove stars, brackets, lowercase, replace spaces
                $cleanKey = preg_replace('/^\*\s*/', '', $key);
                $cleanKey = preg_replace('/\s*\[.*\]/', '', $cleanKey);
                $cleanKey = preg_replace('/\s*\(.*\)/', '', $cleanKey);
                $cleanKey = strtolower(str_replace(' ', '_', trim($cleanKey)));
                
                // Map human key to database key
                $mappedKey = $cleanKey;
                foreach ($headerMap as $humanKey => $dbKey) {
                    if (strpos($cleanKey, $humanKey) !== false) {
                        $mappedKey = $dbKey;
                        break;
                    }
                }
                $cleanedRow[$mappedKey] = $value;
            }
            $cleanedRows[] = $cleanedRow;
        }

        // Verify headers (ensure critical keys mapped correctly)
        $firstRow = $cleanedRows[0] ?? [];
        if (!array_key_exists('rt_number', $firstRow) || !array_key_exists('family_head', $firstRow) || !array_key_exists('status', $firstRow)) {
            return back()->withErrors(['file' => 'Format template salah atau kolom utama tidak ditemukan. Pastikan header sesuai template (Nomor RT, Nama Kepala Keluarga, Status Rumah).']);
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

            $familyHead = isset($row['family_head']) ? trim($row['family_head']) : null;
            $address = isset($row['address']) ? trim($row['address']) : null;
            $phone = isset($row['phone']) ? trim($row['phone']) : null;

            // Handle status normalization (human label -> lowercase DB status)
            $statusRaw = isset($row['status']) ? strtolower(trim($row['status'])) : null;
            $status = $statusRaw;
            if ($statusRaw === 'aktif') {
                $status = 'active';
            }

            $jumlahAnggota = isset($row['jumlah_anggota']) && trim($row['jumlah_anggota']) !== '' ? (int)trim($row['jumlah_anggota']) : null;

            $validator = Validator::make([
                'rt_number' => $rtNumber,
                'kk_number' => $kkNumber,
                'family_head' => $familyHead,
                'status' => $status,
                'jumlah_anggota' => $jumlahAnggota,
            ], [
                'rt_number' => 'required|string',
                'kk_number' => 'nullable|numeric|digits:16',
                'family_head' => 'required|string|max:255',
                'status' => 'required|in:active,kontrak,pindah,kosong',
                'jumlah_anggota' => 'nullable|integer|min:0',
            ], [
                'rt_number.required' => 'Nomor RT wajib diisi.',
                'kk_number.digits' => 'Nomor KK harus tepat 16 digit.',
                'kk_number.numeric' => 'Nomor KK harus berupa angka.',
                'family_head.required' => 'Nama Kepala Keluarga wajib diisi.',
                'status.required' => 'Status Rumah wajib diisi.',
                'status.in' => 'Status Rumah harus: Aktif, Kontrak, Pindah, atau Kosong.',
                'jumlah_anggota.integer' => 'Jumlah Anggota harus berupa angka.',
                'jumlah_anggota.min' => 'Jumlah Anggota minimal 0.',
            ]);

            if ($validator->fails()) {
                $stats['failed']++;
                $errMsgs = implode(' ', $validator->errors()->all());
                $errors[] = "Baris {$rowNum}: {$errMsgs}";
                $failedRows[] = [
                    $row['rt_number'] ?? '',
                    $row['kk_number'] ?? '',
                    $row['family_head'] ?? '',
                    $row['address'] ?? '',
                    $row['phone'] ?? '',
                    $row['status'] ?? '',
                    $row['jumlah_anggota'] ?? '',
                    $errMsgs
                ];
                continue;
            }

            // Database unique validation for kk_number
            if ($kkNumber) {
                $exists = Kk::where('kk_number', $kkNumber)->exists();
                if ($exists) {
                    $stats['duplicates']++;
                    $stats['failed']++;
                    $err = 'Nomor KK sudah terdaftar di database.';
                    $errors[] = "Baris {$rowNum}: {$err}";
                    $failedRows[] = [
                        $row['rt_number'] ?? '',
                        $row['kk_number'] ?? '',
                        $row['family_head'] ?? '',
                        $row['address'] ?? '',
                        $row['phone'] ?? '',
                        $row['status'] ?? '',
                        $row['jumlah_anggota'] ?? '',
                        $err
                    ];
                    continue;
                }

                // Check in current validRows to prevent duplicates inside the uploaded file itself
                foreach ($validRows as $vr) {
                    if ($vr['kk_number'] === $kkNumber) {
                        $stats['duplicates']++;
                        $stats['failed']++;
                        $err = 'Nomor KK duplikat di dalam file import.';
                        $errors[] = "Baris {$rowNum}: {$err}";
                        $failedRows[] = [
                            $row['rt_number'] ?? '',
                            $row['kk_number'] ?? '',
                            $row['family_head'] ?? '',
                            $row['address'] ?? '',
                            $row['phone'] ?? '',
                            $row['status'] ?? '',
                            $row['jumlah_anggota'] ?? '',
                            $err
                        ];
                        continue 2;
                    }
                }
            }

            $validRows[] = [
                'rt_number' => $rtNumber,
                'kk_number' => $kkNumber,
                'family_head' => $familyHead,
                'address' => $address,
                'phone' => $phone,
                'status' => $status,
                'jumlah_anggota' => $jumlahAnggota,
            ];
        }

        if (!empty($errors)) {
            session(['kk_import_failed_rows' => $failedRows]);

            // Save failed execution stats
            ImportHistory::create([
                'filename' => $originalFilename,
                'import_type' => 'kk',
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

        // Commit all in transaction since they are 100% valid
        DB::beginTransaction();
        try {
            $created = 0;
            foreach ($validRows as $row) {
                // Find or create RT
                $rt = Rt::firstOrCreate(
                    ['rt_number' => $row['rt_number']],
                    ['description' => 'RT ' . $row['rt_number']]
                );

                Kk::create([
                    'rt_id' => $rt->id,
                    'kk_number' => $row['kk_number'],
                    'family_head' => $row['family_head'],
                    'address' => $row['address'],
                    'phone' => $row['phone'],
                    'status' => $row['status'],
                ]);
                $created++;
            }

            $stats['success'] = $created;

            ImportHistory::create([
                'filename' => $originalFilename,
                'import_type' => 'kk',
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
                $logger->logInfo("Import Massal Kartu Keluarga berhasil: {$created} data ditambahkan", [
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
        $failedRows = session('kk_import_failed_rows');
        if (empty($failedRows)) {
            return back()->withErrors(['file' => 'Tidak ada data error yang tersedia untuk didownload.']);
        }

        return Excel::download(new KkFailedRowsExport($failedRows), 'failed-rows-kk.xlsx');
    }
}
