<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\WasteCategory;
use App\Models\WasteCustomer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\MaterialAlias;
use App\Models\WasteCategoryGroup;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DepositImportService
{
    protected MaterialMappingService $mappingService;

    public function __construct(?MaterialMappingService $mappingService = null)
    {
        $this->mappingService = $mappingService ?? app(MaterialMappingService::class);
    }
    /**
     * List all sheet names from an uploaded spreadsheet file.
     */
    public function listSheets(string $filePath): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        return $reader->listWorksheetNames($filePath);
    }

    /**
     * Guess a transaction date (YYYY-MM-DD) from sheet name.
     */
    public function guessDateFromSheetName(string $sheetName): ?string
    {
        $clean = trim($sheetName);

        // Pattern DD-MM-YYYY, DD/MM/YYYY, DD-MM-YY, DD/MM/YY
        if (preg_match('/^(\d{1,2})[-_\/.](\d{1,2})[-_\/.](\d{2,4})$/', $clean, $m)) {
            $day = (int) $m[1];
            $month = (int) $m[2];
            $year = (int) $m[3];
            if ($year < 100) {
                $year += 2000;
            }
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        // Pattern YYYY-MM-DD
        if (preg_match('/^(\d{4})[-_\/.](\d{1,2})[-_\/.](\d{1,2})$/', $clean, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
            $day = (int) $m[3];
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        return null;
    }

    /**
     * Calculate deterministic fingerprint for re-import protection.
     */
    public function calculateFingerprint(string $filePath, string $sheetName, string $confirmedDate): string
    {
        $contentHash = md5_file($filePath);
        return md5($contentHash . '|' . trim($sheetName) . '|' . trim($confirmedDate));
    }

    /**
     * Check if exact same file/sheet/date has already been successfully imported.
     */
    public function isFingerprintImported(string $fingerprint): bool
    {
        if (Cache::has("deposit_import_fp_{$fingerprint}")) {
            return true;
        }

        return Deposit::where('notes', 'like', "%[FP:{$fingerprint}]%")->exists();
    }

    /**
     * Parse the selected worksheet into raw data rows.
     */
    public function parseSheet(string $filePath, string $sheetName): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly([$sheetName]);

        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        
        // toArray: nullValue=null, calculateFormulas=true, formatData=false, returnByColumnAndRow=false
        $allRows = $worksheet->toArray(null, true, false, false);

        if (empty($allRows)) {
            return [];
        }

        // Detect header row
        $headerRowIndex = null;
        $colCustomer = null;
        $colMaterial = null;
        $colWeight = null;
        $colPrice = null;
        $colTotal = null;

        $maxScan = min(10, count($allRows));
        for ($r = 0; $r < $maxScan; $r++) {
            $row = $allRows[$r];
            $matches = 0;
            $tempCustomer = null;
            $tempMaterial = null;
            $tempWeight = null;
            $tempPrice = null;
            $tempTotal = null;

            foreach ($row as $colIdx => $cell) {
                if ($cell === null) continue;
                $val = strtolower(trim((string) $cell));
                
                if (preg_match('/^(nasabah|nama|nama nasabah|warga|customer|nama warga)$/i', $val)) {
                    $tempCustomer = $colIdx;
                    $matches++;
                } elseif (preg_match('/^(material|jenis sampah|kategori|sampah|jenis material|kategori sampah)$/i', $val)) {
                    $tempMaterial = $colIdx;
                    $matches++;
                } elseif (preg_match('/^(berat|berat \(kg\)|berat\(kg\)|kg|bobot|qty|jumlah berat)$/i', $val)) {
                    $tempWeight = $colIdx;
                    $matches++;
                } elseif (preg_match('/^(harga|harga\/kg|harga \(rp\)|harga satuan|harga per kg|tarif|rp\/kg)$/i', $val)) {
                    $tempPrice = $colIdx;
                    $matches++;
                } elseif (preg_match('/^(total|total \(rp\)|jumlah \(rp\)|subtotal|total harga|nilai)$/i', $val)) {
                    $tempTotal = $colIdx;
                    $matches++;
                }
            }

            // If at least 3 essential headers matched
            if ($matches >= 3 && $tempCustomer !== null && $tempMaterial !== null && $tempWeight !== null) {
                $headerRowIndex = $r;
                $colCustomer = $tempCustomer;
                $colMaterial = $tempMaterial;
                $colWeight = $tempWeight;
                $colPrice = $tempPrice;
                $colTotal = $tempTotal;
                break;
            }
        }

        // Default fallbacks if header detection fails
        if ($headerRowIndex === null) {
            $headerRowIndex = 0;
            $colCustomer = 0;
            $colMaterial = 1;
            $colWeight = 2;
            $colPrice = 3;
            $colTotal = 4;
        }

        $rawRows = [];
        $totalRows = count($allRows);

        for ($r = $headerRowIndex + 1; $r < $totalRows; $r++) {
            $row = $allRows[$r];

            // Skip completely empty rows
            $nonEmpty = array_filter($row, fn($c) => $c !== null && trim((string)$c) !== '');
            if (empty($nonEmpty)) {
                continue;
            }

            $rawCustomer = isset($row[$colCustomer]) ? trim((string)$row[$colCustomer]) : '';
            $rawMaterial = isset($row[$colMaterial]) ? trim((string)$row[$colMaterial]) : '';
            
            $weightVal = $colWeight !== null && isset($row[$colWeight]) ? $this->parseNumber($row[$colWeight]) : null;
            $priceVal = $colPrice !== null && isset($row[$colPrice]) ? $this->parseNumber($row[$colPrice]) : null;
            $totalVal = $colTotal !== null && isset($row[$colTotal]) ? $this->parseNumber($row[$colTotal]) : null;

            $rawRows[] = [
                'excel_row_num' => $r + 1,
                'raw_customer'  => $rawCustomer,
                'raw_material'  => $rawMaterial,
                'weight'        => $weightVal,
                'excel_price'   => $priceVal,
                'excel_total'   => $totalVal,
            ];
        }

        return $rawRows;
    }

    /**
     * Process parsed rows with matching, price logic, total recalculation, and duplicate detection.
     */
    public function processRows(
        array $rawRows,
        string $confirmedDate,
        array $customerMappings = [],
        array $materialMappings = [],
        array $priceModes = [],
        array $duplicatePolicies = []
    ): array {
        // Load master data lookup
        $activeCustomers = WasteCustomer::where('status', 'active')->get();
        $categories = WasteCategory::all();

        // In-memory customer index by ID to avoid N+1 queries during mapping resolution
        $customerById = $activeCustomers->keyBy('id');
        $mappedCustomerIds = array_filter(array_values($customerMappings));
        $missingCustIds = array_diff($mappedCustomerIds, $customerById->keys()->toArray());
        if (!empty($missingCustIds)) {
            $extraCustomers = WasteCustomer::whereIn('id', $missingCustIds)->get();
            foreach ($extraCustomers as $ec) {
                $customerById[$ec->id] = $ec;
            }
        }

        // In-memory category index by ID to avoid N+1 queries during mapping resolution
        $categoryById = $categories->keyBy('id');
        $mappedCategoryIds = array_filter(array_values($materialMappings));
        $missingCatIds = array_diff($mappedCategoryIds, $categoryById->keys()->toArray());
        if (!empty($missingCatIds)) {
            $extraCategories = WasteCategory::whereIn('id', $missingCatIds)->get();
            foreach ($extraCategories as $ecat) {
                $categoryById[$ecat->id] = $ecat;
            }
        }

        // Build normalized customer and material mapping lookups for robust propagation
        $customerMappingsNorm = [];
        foreach ($customerMappings as $raw => $cid) {
            if (!empty($cid)) {
                $customerMappingsNorm[$this->normalizeString($raw)] = (int) $cid;
            }
        }

        $materialMappingsNorm = [];
        foreach ($materialMappings as $raw => $mid) {
            if (!empty($mid)) {
                $materialMappingsNorm[$this->normalizeString($raw)] = (int) $mid;
            }
        }

        // Memoization cache for material alias resolution to execute at most once per unique material
        $materialResolutionCache = [];

        // Build customer lookup maps
        $customerByCode = [];
        $customerByNameNorm = [];

        foreach ($activeCustomers as $c) {
            $codeKey = strtolower(trim($c->customer_code));
            $customerByCode[$codeKey] = $c;

            $nameKey = $this->normalizeString($c->name);
            if (!isset($customerByNameNorm[$nameKey])) {
                $customerByNameNorm[$nameKey] = [];
            }
            $customerByNameNorm[$nameKey][] = $c;
        }

        // Build category lookup maps
        $categoryByCode = [];
        $categoryByNameNorm = [];

        foreach ($categories as $cat) {
            if ($cat->code) {
                $catCodeKey = strtolower(trim($cat->code));
                $categoryByCode[$catCodeKey] = $cat;
            }
            $catNameKey = $this->normalizeString($cat->name);
            $categoryByNameNorm[$catNameKey] = $cat;
        }

        // Check customer existing deposits on confirmed date
        $existingDepositCustomerIds = Deposit::whereDate('date', $confirmedDate)
            ->pluck('waste_customer_id')
            ->unique()
            ->flip()
            ->toArray();

        $processedRows = [];

        foreach ($rawRows as $idx => $raw) {
            $rawCust = $raw['raw_customer'];
            $rawMat = $raw['raw_material'];
            $weight = $raw['weight'];
            $excelPrice = $raw['excel_price'];
            $excelTotal = $raw['excel_total'];

            $status = 'VALID';
            $reasons = [];
            $matchedCustomer = null;
            $matchedCategory = null;
            $customerStatus = 'MATCHED';
            $materialStatus = 'MATCHED';

            // 1. Validate mandatory fields
            if ($rawCust === '') {
                $status = 'ERROR';
                $reasons[] = 'Nama nasabah kosong';
                $customerStatus = 'ERROR';
            }

            if ($rawMat === '') {
                $status = 'ERROR';
                $reasons[] = 'Nama material kosong';
                $materialStatus = 'ERROR';
            }

            if ($weight === null || $weight <= 0) {
                $status = 'ERROR';
                $reasons[] = 'Berat harus berupa angka > 0';
            }

            if ($excelPrice === null || $excelPrice < 0) {
                $status = 'ERROR';
                $reasons[] = 'Harga tidak valid';
            }

            // 2. Customer matching
            if ($rawCust !== '') {
                // Check manual mapping first (exact or normalized)
                if (isset($customerMappings[$rawCust])) {
                    $matchedCustomer = $customerById[$customerMappings[$rawCust]] ?? null;
                } elseif (isset($customerMappingsNorm[$this->normalizeString($rawCust)])) {
                    $matchedCustomer = $customerById[$customerMappingsNorm[$this->normalizeString($rawCust)]] ?? null;
                }

                // If not mapped, check exact code or normalized name
                if (!$matchedCustomer) {
                    $normCust = $this->normalizeString($rawCust);
                    if (isset($customerByCode[$normCust])) {
                        $matchedCustomer = $customerByCode[$normCust];
                    } elseif (isset($customerByNameNorm[$normCust])) {
                        if (count($customerByNameNorm[$normCust]) === 1) {
                            $matchedCustomer = $customerByNameNorm[$normCust][0];
                        } else {
                            $customerStatus = 'AMBIGUOUS';
                            $reasons[] = "Terdapat lebih dari satu nasabah dengan nama '{$rawCust}'";
                        }
                    } else {
                        $customerStatus = 'NEEDS MAPPING';
                        $reasons[] = "Nasabah '{$rawCust}' belum terdaftar";
                    }
                }
            }

            // 3. Material matching
            if ($rawMat !== '') {
                // Check temporary session mapping first (exact or normalized)
                if (isset($materialMappings[$rawMat])) {
                    $matchedCategory = $categoryById[$materialMappings[$rawMat]] ?? null;
                    if ($matchedCategory) {
                        $materialStatus = 'MAPPED';
                    }
                } elseif (isset($materialMappingsNorm[$this->normalizeString($rawMat)])) {
                    $matchedCategory = $categoryById[$materialMappingsNorm[$this->normalizeString($rawMat)]] ?? null;
                    if ($matchedCategory) {
                        $materialStatus = 'MAPPED';
                    }
                }

                // If not mapped in session, resolve via MaterialMappingService pipeline (memoized per unique material)
                if (!$matchedCategory) {
                    $normMatKey = $this->normalizeString($rawMat);
                    if (!isset($materialResolutionCache[$normMatKey])) {
                        $materialResolutionCache[$normMatKey] = $this->mappingService->resolve(
                            $rawMat,
                            MaterialAlias::SOURCE_BANK_SAMPAH,
                            null
                        );
                    }
                    $resolution = $materialResolutionCache[$normMatKey];

                    if ($resolution['status'] === 'VALID') {
                        $matchedCategory = $resolution['category'];
                        $materialStatus = 'VALID';
                    } elseif ($resolution['status'] === 'ALIAS') {
                        $matchedCategory = $resolution['category'];
                        $materialStatus = 'ALIAS';
                        $reasons[] = $resolution['reason'];
                    } else {
                        $materialStatus = 'NEEDS MAPPING';
                        $reasons[] = "Material '{$rawMat}' belum terdaftar di master maupun alias";
                    }
                }
            }

            // Determine status from matching
            if ($status !== 'ERROR') {
                if ($customerStatus === 'AMBIGUOUS') {
                    $status = 'AMBIGUOUS';
                } elseif ($customerStatus === 'NEEDS MAPPING' || $materialStatus === 'NEEDS MAPPING') {
                    $status = 'NEEDS MAPPING';
                } elseif ($materialStatus === 'ALIAS') {
                    $status = 'ALIAS';
                }
            }

            // 4. Price & Total calculation (source-of-truth rule)
            $masterPrice = $matchedCategory ? (float) $matchedCategory->member_price : null;
            $priceMode = $priceModes[$rawMat] ?? 'excel'; // 'excel' or 'master'
            $usedPrice = ($priceMode === 'master' && $masterPrice !== null) ? $masterPrice : ($excelPrice ?? $masterPrice ?? 0);

            // Transaction total honors the Excel source total when present (e.g. Sirsak export
            // with its own rounding). Only compute Weight x Price when the source has no Total.
            if ($excelTotal !== null) {
                $systemTotal = $excelTotal;
            } else {
                $systemTotal = ($weight !== null && $usedPrice !== null) ? round($weight * $usedPrice) : null;
            }
            $delta = 0;

            $hasPriceWarning = false;
            $hasTotalWarning = false;

            if ($matchedCategory && $excelPrice !== null && $masterPrice !== null) {
                if (abs($excelPrice - $masterPrice) > 0.01) {
                    $hasPriceWarning = true;
                    $reasons[] = "Harga Excel (Rp " . number_format($excelPrice, 0, ',', '.') . ") berbeda dari master (Rp " . number_format($masterPrice, 0, ',', '.') . ")";
                }
            }

            // Check duplicate deposit on confirmed date
            $hasExistingDeposit = false;
            if ($matchedCustomer && isset($existingDepositCustomerIds[$matchedCustomer->id])) {
                $hasExistingDeposit = true;
                $policy = $duplicatePolicies[$matchedCustomer->id] ?? 'append';
                if ($policy === 'append') {
                    $reasons[] = "Nasabah sudah punya setoran pada tanggal ini (akan ditambahkan sebagai setoran baru)";
                } else {
                    $reasons[] = "Nasabah sudah punya setoran pada tanggal ini (akan dilewati/skip)";
                }
            }

            // Demote VALID or ALIAS to WARNING if warnings exist
            if (($status === 'VALID' || $status === 'ALIAS') && ($hasPriceWarning || $hasTotalWarning || $hasExistingDeposit)) {
                $status = 'WARNING';
            }

            $processedRows[] = [
                'index'                 => $idx,
                'excel_row_num'         => $raw['excel_row_num'],
                'raw_customer'          => $rawCust,
                'raw_material'          => $rawMat,
                'weight'                => $weight,
                'excel_price'           => $excelPrice,
                'excel_total'           => $excelTotal,
                'customer_id'           => $matchedCustomer ? $matchedCustomer->id : null,
                'customer_name'         => $matchedCustomer ? $matchedCustomer->name : null,
                'customer_code'         => $matchedCustomer ? $matchedCustomer->customer_code : null,
                'customer_status'       => $customerStatus,
                'category_id'           => $matchedCategory ? $matchedCategory->id : null,
                'category_name'         => $matchedCategory ? $matchedCategory->name : null,
                'category_unit'         => $matchedCategory ? $matchedCategory->unit : 'kg',
                'material_status'       => $materialStatus,
                'master_price'          => $masterPrice,
                'price_mode'            => $priceMode,
                'used_price'            => $usedPrice,
                'system_total'          => $systemTotal,
                'delta'                 => $delta,
                'has_price_warning'     => $hasPriceWarning,
                'has_total_warning'     => $hasTotalWarning,
                'has_existing_deposit'  => $hasExistingDeposit,
                'status'                => $status,
                'reasons'               => $reasons,
            ];
        }

        return $processedRows;
    }

    /**
     * Build pre-commit recaps (Material + Nasabah) from the SAME staged dataset
     * that commit() will persist. Only fully-resolved, non-skipped rows are
     * included: rows with customer_id + category_id, minus rows skipped by the
     * duplicate policy. Materials stay distinct by canonical category_id
     * ("Kardus Bekas" !== "Kardus Remuk"); unit comes from the master material.
     *
     * @return array{materials: array, customers: array, grand_weight: float, grand_amount: float}
     */
    public function buildRecap(array $rows, array $duplicatePolicies = []): array
    {
        $materials = [];
        $customers = [];
        $grandWeight = 0.0;
        $grandAmount = 0.0;

        foreach ($rows as $r) {
            if (empty($r['customer_id']) || empty($r['category_id'])) {
                continue;
            }
            $policy = $duplicatePolicies[$r['customer_id']] ?? 'append';
            if (!empty($r['has_existing_deposit']) && $policy === 'skip') {
                continue;
            }

            $weight = (float) ($r['weight'] ?? 0);
            $subtotal = (float) ($r['system_total'] ?? 0);

            $catId = $r['category_id'];
            if (!isset($materials[$catId])) {
                $materials[$catId] = [
                    'category_id' => $catId,
                    'name' => $r['category_name'],
                    'unit' => $r['category_unit'] ?? 'kg',
                    'total_weight' => 0.0,
                ];
            }
            $materials[$catId]['total_weight'] += $weight;

            $custId = $r['customer_id'];
            if (!isset($customers[$custId])) {
                $customers[$custId] = [
                    'customer_id' => $custId,
                    'name' => $r['customer_name'],
                    'code' => $r['customer_code'],
                    'total_weight' => 0.0,
                    'total_amount' => 0.0,
                ];
            }
            $customers[$custId]['total_weight'] += $weight;
            $customers[$custId]['total_amount'] += $subtotal;

            $grandWeight += $weight;
            $grandAmount += $subtotal;
        }

        // Attach category group (label) to each unique material
        if (!empty($materials)) {
            $categoriesWithGroups = WasteCategory::with('wasteCategoryGroup')
                ->findMany(array_keys($materials))
                ->keyBy('id');

            foreach ($materials as $catId => &$m) {
                $wc = $categoriesWithGroups->get($catId);
                $m['group_id'] = $wc?->waste_category_group_id;
                $m['group_name'] = $wc?->wasteCategoryGroup?->name ?? 'Belum dikategorikan';
            }
            unset($m);
        }

        usort($materials, fn($a, $b) => strcmp($a['name'], $b['name']));
        usort($customers, fn($a, $b) => strcmp($a['name'], $b['name']));

        return [
            'materials' => array_values($materials),
            'customers' => array_values($customers),
            'grand_weight' => $grandWeight,
            'grand_amount' => $grandAmount,
        ];
    }

    /**
     * Quick Add a single new customer and return the instance.
     */
    public function quickAddCustomer(string $name, ?string $phone = null, ?string $address = null): WasteCustomer
    {
        return DB::transaction(function () use ($name, $phone, $address) {
            $customer = WasteCustomer::create([
                'customer_code' => WasteCustomer::generateNextCustomerCode(),
                'name'          => trim($name),
                'phone'         => $phone ? trim($phone) : null,
                'address'       => $address ? trim($address) : null,
                'status'        => 'active',
                'joined_at'     => now(),
            ]);

            app(ActivityLogService::class)->logInfo(
                'waste_customer.create',
                "Membuat profil nasabah baru {$customer->name} ({$customer->customer_code}) via Import Setoran.",
                ['after' => $customer->toArray()]
            );

            return $customer;
        });
    }

    /**
     * Bulk Add multiple customers atomically.
     */
    public function bulkAddCustomers(array $customersList): array
    {
        return DB::transaction(function () use ($customersList) {
            $created = [];
            foreach ($customersList as $item) {
                $name = trim($item['name'] ?? '');
                if ($name === '') continue;

                $customer = WasteCustomer::create([
                    'customer_code' => WasteCustomer::generateNextCustomerCode(),
                    'name'          => $name,
                    'phone'         => !empty($item['phone']) ? trim($item['phone']) : null,
                    'address'       => !empty($item['address']) ? trim($item['address']) : null,
                    'status'        => 'active',
                    'joined_at'     => now(),
                ]);

                $customer->raw_customer = $item['raw_customer'] ?? $name;

                app(ActivityLogService::class)->logInfo(
                    'waste_customer.create',
                    "Membuat profil nasabah baru {$customer->name} ({$customer->customer_code}) via Bulk Add Import.",
                    ['after' => $customer->toArray()]
                );

                $created[] = $customer;
            }
            return $created;
        });
    }

    /**
     * Quick Add a new canonical WasteCategory and optionally register a persistent alias.
     */
    public function quickAddCategory(
        string $name,
        ?int $groupId = null,
        string $unit = 'kg',
        float $memberPrice = 0,
        ?string $rawMaterialToAlias = null
    ): WasteCategory {
        return DB::transaction(function () use ($name, $groupId, $unit, $memberPrice, $rawMaterialToAlias) {
            $group = $groupId ? WasteCategoryGroup::find($groupId) : null;
            $code = WasteCategory::generateCode($group);

            $category = WasteCategory::create([
                'code'                    => $code,
                'name'                    => trim($name),
                'waste_category_group_id' => $group ? $group->id : null,
                'unit'                    => trim($unit),
                'member_price'            => $memberPrice,
            ]);

            if ($rawMaterialToAlias) {
                $this->mappingService->registerAlias(
                    $rawMaterialToAlias,
                    $category->id,
                    MaterialAlias::SOURCE_BANK_SAMPAH,
                    null,
                    true
                );
            }

            app(ActivityLogService::class)->logInfo(
                'waste_category.create',
                "Membuat kategori sampah baru {$category->name} ({$category->code}) via Import Setoran.",
                ['after' => $category->toArray()]
            );

            return $category;
        });
    }

    /**
     * Commit the staged import atomically.
     */
    public function commit(array $stagingData): array
    {
        $fingerprint = $stagingData['fingerprint'];
        if ($this->isFingerprintImported($fingerprint)) {
            throw new \RuntimeException("Impor ini sudah pernah berhasil diproses sebelumnya. Impor ulang file/sheet yang sama diblokir.");
        }

        $rows = $stagingData['rows'];
        $confirmedDate = $stagingData['confirmed_date'];
        $batchCode = $stagingData['batch_code'];
        $filename = $stagingData['filename'];
        $sheetName = $stagingData['sheet_name'];
        $duplicatePolicies = $stagingData['duplicate_policies'] ?? [];

        // Verify no unresolved errors
        foreach ($rows as $r) {
            if ($r['status'] === 'ERROR' || $r['status'] === 'NEEDS MAPPING' || $r['status'] === 'AMBIGUOUS') {
                throw new \RuntimeException("Masih terdapat baris yang bermasalah atau belum terpetakan. Selesaikan seluruh error/mapping sebelum mengimpor.");
            }
        }

        // Group rows by customer_id
        $grouped = [];
        $totalWeight = 0;
        $totalAmount = 0;
        $skippedCount = 0;
        $processedCount = 0;

        foreach ($rows as $row) {
            $customerId = $row['customer_id'];
            $policy = $duplicatePolicies[$customerId] ?? 'append';

            if (!empty($row['has_existing_deposit']) && $policy === 'skip') {
                $skippedCount++;
                continue;
            }

            $processedCount++;
            if (!isset($grouped[$customerId])) {
                $grouped[$customerId] = [
                    'waste_customer_id' => $customerId,
                    'date'              => $confirmedDate,
                    'notes'             => "Import Excel: {$filename} (Sheet: {$sheetName}) [Batch: {$batchCode}] [FP: {$fingerprint}]",
                    'details'           => [],
                ];
            }

            $weight = (float) $row['weight'];
            $price = (float) $row['used_price'];
            $subtotal = (float) $row['system_total'];

            $totalWeight += $weight;
            $totalAmount += $subtotal;

            $grouped[$customerId]['details'][] = [
                'waste_category_id' => $row['category_id'],
                'weight'            => $weight,
                'price_per_unit'    => $price,
                'subtotal'          => $subtotal,
            ];
        }

        if (empty($grouped)) {
            throw new \RuntimeException("Tidak ada transaksi yang dapat diimpor (semua baris dilewati karena duplikasi atau tidak ada data).");
        }

        // Cross-check: material recap, customer recap, and grouped totals must agree.
        // Never commit silently on internal mismatch.
        $recap = $this->buildRecap($rows, $duplicatePolicies);
        $recapMaterialWeight = array_sum(array_column($recap['materials'], 'total_weight'));
        $recapCustomerWeight = array_sum(array_column($recap['customers'], 'total_weight'));
        $recapCustomerAmount = array_sum(array_column($recap['customers'], 'total_amount'));
        if (abs($recapMaterialWeight - $totalWeight) > 0.01
            || abs($recapCustomerWeight - $totalWeight) > 0.01
            || abs($recapCustomerAmount - $totalAmount) > 0.01) {
            throw new \RuntimeException("Validasi rekap gagal: total rekap material/nasabah tidak konsisten dengan total transaksi. Impor dibatalkan.");
        }

        // Execute batch insertion via BankSampahService atomically
        $bankSampahService = app(BankSampahService::class);
        $createdDeposits = [];

        DB::transaction(function () use ($grouped, $bankSampahService, &$createdDeposits) {
            foreach ($grouped as $group) {
                $createdDeposits[] = $bankSampahService->recordDeposit($group);
            }
        });

        // Record fingerprint permanently
        Cache::forever("deposit_import_fp_{$fingerprint}", true);

        return [
            'batch_code'      => $batchCode,
            'filename'        => $filename,
            'sheet_name'      => $sheetName,
            'date'            => $confirmedDate,
            'total_rows'      => count($rows),
            'processed_rows'  => $processedCount,
            'skipped_rows'    => $skippedCount,
            'deposits_count'  => count($createdDeposits),
            'customers_count' => count($grouped),
            'total_weight'    => $totalWeight,
            'total_amount'    => $totalAmount,
        ];
    }

    /**
     * Clean normalize string for comparisons.
     */
    private function normalizeString(string $str): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $str)));
    }

    /**
     * Parse local number formatting safely.
     */
    private function parseNumber($val): ?float
    {
        if ($val === null || $val === '') return null;
        if (is_numeric($val)) return (float) $val;

        // Strip currency symbols, spaces, and periods used as thousand separators
        $str = str_replace([' ', 'Rp', 'rp', 'RP'], '', (string) $val);
        // If has comma as decimal
        if (str_contains($str, ',') && !str_contains($str, '.')) {
            $str = str_replace(',', '.', $str);
        } else {
            // e.g. "1.000,50" -> strip '.' then replace ',' with '.'
            if (str_contains($str, '.') && str_contains($str, ',')) {
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            }
        }

        return is_numeric($str) ? (float) $str : null;
    }
}
