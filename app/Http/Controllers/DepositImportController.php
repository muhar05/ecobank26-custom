<?php

namespace App\Http\Controllers;

use App\Exports\DepositTemplateExport;
use App\Models\MaterialAlias;
use App\Models\WasteCategory;
use App\Models\WasteCategoryGroup;
use App\Models\WasteCustomer;
use App\Services\DepositImportService;
use App\Services\MaterialMappingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class DepositImportController extends Controller
{
    protected DepositImportService $importService;
    protected MaterialMappingService $mappingService;

    public function __construct(DepositImportService $importService, MaterialMappingService $mappingService)
    {
        $this->importService = $importService;
        $this->mappingService = $mappingService;
    }

    /**
     * Download the official Excel import template for deposits.
     */
    public function downloadTemplate()
    {
        return Excel::download(new DepositTemplateExport(), 'template-import-setoran.xlsx');
    }

    /**
     * Show the wizard step:
     * - Step 1: Upload / Sheet Selection
     * - Step 2: Preview & Mapping
     * - Step 3: Success Result
     */
    public function showForm(Request $request)
    {
        // If result is requested from flash
        if ($request->query('result') === 'success' && session()->has('import_result')) {
            $result = session('import_result');
            return view('bank-sampah.deposits.import', [
                'step' => 'result',
                'result' => $result,
            ]);
        }

        $token = $request->query('token');

        if ($token && Cache::has("deposit_import_{$token}")) {
            $staging = Cache::get("deposit_import_{$token}");

            // If only sheets listed (step 1.5)
            if (empty($staging['rows'])) {
                return view('bank-sampah.deposits.import', [
                    'step' => 'select_sheet',
                    'token' => $token,
                    'filename' => $staging['filename'],
                    'sheets' => $staging['sheets'],
                    'defaultDate' => $staging['default_date'] ?? date('Y-m-d'),
                ]);
            }

            // Step 2: Preview & Mapping
            $rows = $staging['rows'];
            $categories = WasteCategory::orderBy('name')->get();
            $customers = WasteCustomer::where('status', 'active')->orderBy('name')->get();

            // Analyze statuses
            $counts = [
                'total' => count($rows),
                'valid' => 0,
                'alias' => 0,
                'warning' => 0,
                'needs_mapping' => 0,
                'error' => 0,
                'ambiguous' => 0,
            ];

            $unmatchedCustomers = [];
            $unmatchedMaterials = [];
            $priceDiscrepancyMaterials = [];
            $duplicateCustomers = [];

            foreach ($rows as $r) {
                // Row statuses use spaces ('NEEDS MAPPING') while count keys use
                // underscores ('needs_mapping') — normalize so blockers are counted.
                $st = strtolower(str_replace(' ', '_', $r['status']));
                if (isset($counts[$st])) {
                    $counts[$st]++;
                }

                if ($r['customer_status'] === 'NEEDS MAPPING' || $r['customer_status'] === 'AMBIGUOUS') {
                    $raw = $r['raw_customer'];
                    if (!isset($unmatchedCustomers[$raw])) {
                        $unmatchedCustomers[$raw] = [
                            'raw' => $raw,
                            'status' => $r['customer_status'],
                            'count' => 0,
                        ];
                    }
                    $unmatchedCustomers[$raw]['count']++;
                }

                if ($r['material_status'] === 'NEEDS MAPPING') {
                    $raw = $r['raw_material'];
                    if (!isset($unmatchedMaterials[$raw])) {
                        $unmatchedMaterials[$raw] = [
                            'raw' => $raw,
                            'count' => 0,
                            'candidates' => $this->mappingService->getCandidates($raw),
                        ];
                    }
                    $unmatchedMaterials[$raw]['count']++;
                }

                if ($r['has_price_warning']) {
                    $raw = $r['raw_material'];
                    if (!isset($priceDiscrepancyMaterials[$raw])) {
                        $priceDiscrepancyMaterials[$raw] = [
                            'raw' => $raw,
                            'category_name' => $r['category_name'],
                            'excel_price' => $r['excel_price'],
                            'master_price' => $r['master_price'],
                            'current_mode' => $r['price_mode'],
                            'count' => 0,
                        ];
                    }
                    $priceDiscrepancyMaterials[$raw]['count']++;
                }

                if ($r['has_existing_deposit']) {
                    $cid = $r['customer_id'];
                    if (!isset($duplicateCustomers[$cid])) {
                        $duplicateCustomers[$cid] = [
                            'id' => $cid,
                            'name' => $r['customer_name'],
                            'policy' => $staging['duplicate_policies'][$cid] ?? 'append',
                            'count' => 0,
                        ];
                    }
                    $duplicateCustomers[$cid]['count']++;
                }
            }

            // Calculate pre-commit metrics
            $canCommit = ($counts['error'] === 0 && $counts['needs_mapping'] === 0 && $counts['ambiguous'] === 0);

            $groupedCustomers = [];
            $totalWeight = 0;
            $totalAmount = 0;

            foreach ($rows as $r) {
                if ($r['customer_id']) {
                    $policy = $staging['duplicate_policies'][$r['customer_id']] ?? 'append';
                    if (!empty($r['has_existing_deposit']) && $policy === 'skip') {
                        continue;
                    }
                    $groupedCustomers[$r['customer_id']] = true;
                    $totalWeight += (float) $r['weight'];
                    $totalAmount += (float) $r['system_total'];
                }
            }

            // Build pre-commit recaps from the same staged dataset commit() will persist
            $recap = $this->importService->buildRecap($rows, $staging['duplicate_policies'] ?? []);

            // Step 2 sub-workspace: entity resolution ('resolve') vs dedicated
            // recap review ('recap'). Pure view switch, no business logic change.
            $subView = $request->query('view', 'resolve');
            if (!in_array($subView, ['resolve', 'recap'], true)) {
                $subView = 'resolve';
            }

            // Calculate unique entity metrics distinguishing row count from unique entity count
            $uniqueRawCustomers = collect($rows)->pluck('raw_customer')->map(fn($v) => trim($v))->filter()->unique();
            $uniqueRawMaterials = collect($rows)->pluck('raw_material')->map(fn($v) => trim($v))->filter()->unique();

            $uniqueCounts = [
                'total_rows'           => count($rows),
                'unique_customers'     => $uniqueRawCustomers->count(),
                'unique_materials'     => $uniqueRawMaterials->count(),
                'unresolved_customers' => count($unmatchedCustomers),
                'unresolved_materials' => count($unmatchedMaterials),
            ];

            return view('bank-sampah.deposits.import', [
                'step' => 'preview',
                'subView' => $subView,
                'token' => $token,
                'staging' => $staging,
                'rows' => $rows,
                'counts' => $counts,
                'uniqueCounts' => $uniqueCounts,
                'materialRecap' => $recap['materials'],
                'customerRecap' => $recap['customers'],
                'recapGrandWeight' => $recap['grand_weight'],
                'recapGrandAmount' => $recap['grand_amount'],
                'canCommit' => $canCommit,
                'unmatchedCustomers' => $unmatchedCustomers,
                'unmatchedMaterials' => $unmatchedMaterials,
                'priceDiscrepancyMaterials' => $priceDiscrepancyMaterials,
                'duplicateCustomers' => $duplicateCustomers,
                'categories' => $categories,
                'availableGroups' => WasteCategoryGroup::active()->orderBy('name')->get(),
                'customers' => $customers,
                'summary' => [
                    'deposits_count' => count($groupedCustomers),
                    'customers_count' => count($groupedCustomers),
                    'total_weight' => $totalWeight,
                    'total_amount' => $totalAmount,
                ],
            ]);
        }

        // Default: Step 1 (Upload File)
        return view('bank-sampah.deposits.import', [
            'step' => 'upload',
        ]);
    }

    /**
     * Upload file and inspect sheet names.
     */
    public function parseFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $file = $request->file('file');
        $token = Str::random(32);
        $originalName = $file->getClientOriginalName();
        $ext = $file->getClientOriginalExtension();

        $storageDir = storage_path('app/temp_imports');
        if (!File::isDirectory($storageDir)) {
            File::makeDirectory($storageDir, 0755, true, true);
        }

        $destPath = $storageDir . '/' . $token . '.' . $ext;
        $file->move($storageDir, $token . '.' . $ext);

        try {
            $sheets = $this->importService->listSheets($destPath);
        } catch (\Throwable $e) {
            @unlink($destPath);
            return back()->withErrors(['file' => 'Gagal membaca berkas Excel. Pastikan berkas tidak korup dan berformat .xlsx / .xls valid.']);
        }

        if (empty($sheets)) {
            @unlink($destPath);
            return back()->withErrors(['file' => 'Berkas Excel tidak memiliki sheet yang dapat dibaca.']);
        }

        // Try to guess date from the first sheet
        $defaultDate = $this->importService->guessDateFromSheetName($sheets[0]) ?? date('Y-m-d');

        // Store initial staging in Cache
        $staging = [
            'token' => $token,
            'file_path' => $destPath,
            'filename' => $originalName,
            'sheets' => $sheets,
            'default_date' => $defaultDate,
            'sheet_name' => null,
            'confirmed_date' => null,
            'raw_rows' => [],
            'rows' => [],
            'customer_mappings' => [],
            'material_mappings' => [],
            'price_modes' => [],
            'duplicate_policies' => [],
        ];

        Cache::put("deposit_import_{$token}", $staging, now()->addHours(3));

        return redirect()->route('bank-sampah.deposits.import', ['token' => $token]);
    }

    /**
     * Select sheet and confirmed date, then parse data rows.
     */
    public function previewSheet(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'sheet_name' => 'required|string',
            'confirmed_date' => 'required|date',
        ]);

        $token = $request->input('token');
        if (!Cache::has("deposit_import_{$token}")) {
            return redirect()->route('bank-sampah.deposits.import')
                ->withErrors(['file' => 'Sesi impor telah kedaluwarsa. Silakan unggah berkas kembali.']);
        }

        $staging = Cache::get("deposit_import_{$token}");
        $filePath = $staging['file_path'];
        $sheetName = $request->input('sheet_name');
        $confirmedDate = $request->input('confirmed_date');

        if (!File::exists($filePath)) {
            Cache::forget("deposit_import_{$token}");
            return redirect()->route('bank-sampah.deposits.import')
                ->withErrors(['file' => 'Berkas fisik impor tidak ditemukan di server. Silakan unggah ulang.']);
        }

        // Re-import fingerprint check (protection stays active: no double credit)
        $fingerprint = $this->importService->calculateFingerprint($filePath, $sheetName, $confirmedDate);
        if ($this->importService->isFingerprintImported($fingerprint)) {
            return back()->withErrors([
                'sheet_name' => "⚠️ File ini sebelumnya sudah pernah dicoba diimpor. Silakan cek kembali hasil import sebelumnya sebelum melanjutkan. (Sheet '{$sheetName}', tanggal {$confirmedDate} — impor ulang diblokir untuk mencegah duplikasi saldo.)",
            ]);
        }

        // Parse sheet rows
        try {
            $rawRows = $this->importService->parseSheet($filePath, $sheetName);
        } catch (\Throwable $e) {
            return back()->withErrors(['sheet_name' => 'Gagal membaca isi sheet: ' . $e->getMessage()]);
        }

        if (empty($rawRows)) {
            return back()->withErrors(['sheet_name' => "Sheet '{$sheetName}' tidak memuat baris data timbangan."]);
        }

        if (count($rawRows) > 2000) {
            return back()->withErrors(['sheet_name' => 'Jumlah baris melebihi batas maksimum 2.000 baris per impor. Pisahkan berkas menjadi bagian lebih kecil.']);
        }

        // Process rows through matching
        $batchCode = 'IMP-SETORAN-' . Carbon::parse($confirmedDate)->format('Ymd') . '-' . strtoupper(Str::random(4));
        $processedRows = $this->importService->processRows($rawRows, $confirmedDate, [], [], [], []);

        // Update staging
        $staging['sheet_name'] = $sheetName;
        $staging['confirmed_date'] = $confirmedDate;
        $staging['batch_code'] = $batchCode;
        $staging['fingerprint'] = $fingerprint;
        $staging['raw_rows'] = $rawRows;
        $staging['rows'] = $processedRows;

        Cache::put("deposit_import_{$token}", $staging, now()->addHours(3));

        return redirect()->route('bank-sampah.deposits.import', ['token' => $token]);
    }

    /**
     * Resolve mappings for material, customer, price mode, or duplicate policies.
     */
    public function resolveMapping(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'customer_mappings' => 'nullable|array',
            'material_mappings' => 'nullable|array',
            'price_modes' => 'nullable|array',
            'duplicate_policies' => 'nullable|array',
        ]);

        $token = $request->input('token');
        if (!Cache::has("deposit_import_{$token}")) {
            return redirect()->route('bank-sampah.deposits.import')
                ->withErrors(['file' => 'Sesi impor telah kedaluwarsa. Silakan unggah kembali berkas.']);
        }

        $staging = Cache::get("deposit_import_{$token}");

        // Merge mappings
        if ($request->filled('customer_mappings')) {
            foreach ($request->input('customer_mappings') as $raw => $id) {
                if (!empty($id)) {
                    $staging['customer_mappings'][$raw] = (int) $id;
                }
            }
        }

        if ($request->filled('material_mappings')) {
            $savePermanent = $request->input('save_permanent_aliases', []);
            foreach ($request->input('material_mappings') as $raw => $id) {
                if (!empty($id)) {
                    $catId = (int) $id;
                    $staging['material_mappings'][$raw] = $catId;

                    if (!empty($savePermanent[$raw])) {
                        $this->mappingService->registerAlias(
                            $raw,
                            $catId,
                            MaterialAlias::SOURCE_BANK_SAMPAH,
                            null,
                            true
                        );
                    }
                }
            }
        }

        if ($request->filled('price_modes')) {
            foreach ($request->input('price_modes') as $raw => $mode) {
                if (in_array($mode, ['excel', 'master'], true)) {
                    $staging['price_modes'][$raw] = $mode;
                }
            }
        }

        if ($request->filled('duplicate_policies')) {
            foreach ($request->input('duplicate_policies') as $cid => $policy) {
                if (in_array($policy, ['append', 'skip'], true)) {
                    $staging['duplicate_policies'][$cid] = $policy;
                }
            }
        }

        // Re-process rows
        $staging['rows'] = $this->importService->processRows(
            $staging['raw_rows'],
            $staging['confirmed_date'],
            $staging['customer_mappings'],
            $staging['material_mappings'],
            $staging['price_modes'],
            $staging['duplicate_policies']
        );

        Cache::put("deposit_import_{$token}", $staging, now()->addHours(3));

        $hasBlockers = collect($staging['rows'])->contains(function ($r) {
            return in_array($r['status'], ['ERROR', 'NEEDS MAPPING', 'AMBIGUOUS'], true);
        });

        if (!$hasBlockers) {
            return redirect()->route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'recap'])
                ->with('success', 'Pemetaan material berhasil disimpan. Seluruh data siap diimpor.');
        }

        return redirect()->route('bank-sampah.deposits.import', ['token' => $token])
            ->with('success', 'Pemetaan berhasil diperbarui.');
    }

    /**
     * Quick Add a single customer and auto-map to all identical raw rows.
     */
    public function quickAddCustomer(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'raw_customer' => 'required|string',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        $token = $request->input('token');
        if (!Cache::has("deposit_import_{$token}")) {
            return redirect()->route('bank-sampah.deposits.import')
                ->withErrors(['file' => 'Sesi impor telah kedaluwarsa.']);
        }

        $staging = Cache::get("deposit_import_{$token}");
        $rawCust = $request->input('raw_customer');

        $customer = $this->importService->quickAddCustomer(
            $request->input('name'),
            $request->input('phone'),
            $request->input('address')
        );

        // Auto-map this customer
        $staging['customer_mappings'][$rawCust] = $customer->id;

        // Re-process rows
        $staging['rows'] = $this->importService->processRows(
            $staging['raw_rows'],
            $staging['confirmed_date'],
            $staging['customer_mappings'],
            $staging['material_mappings'],
            $staging['price_modes'],
            $staging['duplicate_policies']
        );

        Cache::put("deposit_import_{$token}", $staging, now()->addHours(3));

        return redirect()->route('bank-sampah.deposits.import', ['token' => $token])
            ->with('success', "Nasabah baru '{$customer->name}' ({$customer->customer_code}) berhasil didaftarkan dan seluruh baris terkait terpetakan otomatis.");
    }

    /**
     * Bulk Add multiple customers simultaneously.
     */
    public function bulkAddCustomers(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'customers' => 'required|array|min:1',
            'customers.*.name' => 'required|string|max:255',
            'customers.*.raw_customer' => 'required|string',
            'customers.*.phone' => 'nullable|string|max:50',
            'customers.*.address' => 'nullable|string|max:255',
        ]);

        $token = $request->input('token');
        if (!Cache::has("deposit_import_{$token}")) {
            return redirect()->route('bank-sampah.deposits.import')
                ->withErrors(['file' => 'Sesi impor telah kedaluwarsa.']);
        }

        $staging = Cache::get("deposit_import_{$token}");
        $customersInput = $request->input('customers');

        $created = $this->importService->bulkAddCustomers($customersInput);

        // Map each newly created customer
        foreach ($created as $idx => $c) {
            $rawName = $customersInput[$idx]['raw_customer'] ?? $c->name;
            $staging['customer_mappings'][$rawName] = $c->id;
        }

        // Re-process rows
        $staging['rows'] = $this->importService->processRows(
            $staging['raw_rows'],
            $staging['confirmed_date'],
            $staging['customer_mappings'],
            $staging['material_mappings'],
            $staging['price_modes'],
            $staging['duplicate_policies']
        );

        Cache::put("deposit_import_{$token}", $staging, now()->addHours(3));

        $count = count($created);
        return redirect()->route('bank-sampah.deposits.import', ['token' => $token])
            ->with('success', "{$count} nasabah baru berhasil didaftarkan sekaligus dan seluruh baris terkait terpetakan otomatis.");
    }

    /**
     * Quick Add a new canonical WasteCategory and optionally register a persistent alias.
     */
    public function quickAddCategory(Request $request)
    {
        $request->validate([
            'token'                   => 'required|string',
            'raw_material'            => 'required|string',
            'name'                    => 'required|string|max:255',
            'waste_category_group_id' => 'nullable|exists:waste_category_groups,id',
            'unit'                    => 'required|string|max:20',
            'member_price'            => 'required|numeric|min:0',
            'save_alias'              => 'nullable|boolean',
        ]);

        $token = $request->input('token');
        if (!Cache::has("deposit_import_{$token}")) {
            return redirect()->route('bank-sampah.deposits.import')
                ->withErrors(['file' => 'Sesi impor telah kedaluwarsa.']);
        }

        $staging = Cache::get("deposit_import_{$token}");
        $rawMat = $request->input('raw_material');

        $groupId = $request->filled('waste_category_group_id') ? (int) $request->input('waste_category_group_id') : null;

        $category = $this->importService->quickAddCategory(
            $request->input('name'),
            $groupId,
            $request->input('unit'),
            (float) $request->input('member_price'),
            $request->boolean('save_alias', true) ? $rawMat : null
        );

        $staging['material_mappings'][$rawMat] = $category->id;

        $staging['rows'] = $this->importService->processRows(
            $staging['raw_rows'],
            $staging['confirmed_date'],
            $staging['customer_mappings'],
            $staging['material_mappings'],
            $staging['price_modes'],
            $staging['duplicate_policies']
        );

        Cache::put("deposit_import_{$token}", $staging, now()->addHours(3));

        return redirect()->route('bank-sampah.deposits.import', ['token' => $token])
            ->with('success', "Kategori sampah baru '{$category->name}' ({$category->code}) berhasil ditambahkan dan seluruh baris terkait terpetakan otomatis.");
    }

    /**
     * Batch assign administrative category (group) to selected Master Materials from Rekap Material view.
     */
    public function assignMaterialCategory(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'material_ids' => 'required|array|min:1',
            'material_ids.*' => 'required|exists:waste_categories,id',
            'waste_category_group_id' => 'nullable|exists:waste_category_groups,id',
            'new_category_name' => 'nullable|string|max:100',
        ]);

        $token = $request->input('token');
        if (!Cache::has("deposit_import_{$token}")) {
            return redirect()->route('bank-sampah.deposits.import')
                ->withErrors(['file' => 'Sesi impor telah kedaluwarsa.']);
        }

        $materialIds = $request->input('material_ids');

        $group = null;
        if ($request->filled('new_category_name')) {
            $name = trim($request->input('new_category_name'));
            $group = WasteCategoryGroup::where('name', $name)->first();
            if (!$group) {
                $baseCode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
                if (strlen($baseCode) < 3) {
                    $baseCode = 'KAT';
                }
                $code = $baseCode;
                $idx = 1;
                while (WasteCategoryGroup::where('code', $code)->exists()) {
                    $code = $baseCode . $idx;
                    $idx++;
                }
                $group = WasteCategoryGroup::create([
                    'name' => $name,
                    'code' => $code,
                    'is_active' => true,
                ]);
            }
        } elseif ($request->filled('waste_category_group_id')) {
            $group = WasteCategoryGroup::findOrFail($request->input('waste_category_group_id'));
        }

        if ($group) {
            WasteCategory::whereIn('id', $materialIds)->update([
                'waste_category_group_id' => $group->id,
                'category_group' => $group->name,
            ]);
            $count = count($materialIds);
            $message = "Kategori '{$group->name}' berhasil diterapkan ke {$count} material.";
        } else {
            WasteCategory::whereIn('id', $materialIds)->update([
                'waste_category_group_id' => null,
                'category_group' => null,
            ]);
            $count = count($materialIds);
            $message = "{$count} material diatur menjadi belum dikategorikan.";
        }

        return redirect()->route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'recap'])
            ->with('success', $message);
    }

    /**
     * Atomic Commit: inserts grouped deposits and savings ledger records.
     */
    public function commitImport(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $token = $request->input('token');
        if (!Cache::has("deposit_import_{$token}")) {
            return redirect()->route('bank-sampah.deposits.import')
                ->withErrors(['file' => 'Sesi impor telah kedaluwarsa. Silakan ulangi proses impor.']);
        }

        $staging = Cache::get("deposit_import_{$token}");

        try {
            $result = $this->importService->commit($staging);
        } catch (\Throwable $e) {
            return back()->withErrors(['commit' => 'Gagal memproses impor transaksi: ' . $e->getMessage()]);
        }

        // Cleanup temporary file and cache
        if (File::exists($staging['file_path'])) {
            @unlink($staging['file_path']);
        }
        Cache::forget("deposit_import_{$token}");

        return redirect()->route('bank-sampah.deposits.import', ['result' => 'success'])
            ->with('import_result', $result);
    }

    /**
     * Cancel and reset active staging session.
     */
    public function resetImport(Request $request)
    {
        $token = $request->input('token');
        if ($token && Cache::has("deposit_import_{$token}")) {
            $staging = Cache::get("deposit_import_{$token}");
            if (!empty($staging['file_path']) && File::exists($staging['file_path'])) {
                @unlink($staging['file_path']);
            }
            Cache::forget("deposit_import_{$token}");
        }

        return redirect()->route('bank-sampah.deposits.import')
            ->with('success', 'Sesi impor telah dibatalkan.');
    }
}
