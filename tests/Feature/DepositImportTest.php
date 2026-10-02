<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\MaterialAlias;
use App\Models\SavingsLedger;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteCustomer;
use App\Services\DepositImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DepositImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected WasteCategory $catAsoy;
    protected WasteCategory $catEmberan;
    protected WasteCategory $catGabrukan;
    protected WasteCustomer $custSarti;
    protected WasteCustomer $custBudi;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup permissions & roles
        $perm = Permission::firstOrCreate(['name' => 'manage_deposits', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'admin_bank_sampah', 'guard_name' => 'web']);
        $role->givePermissionTo($perm);

        $this->adminUser = User::factory()->create([
            'phone' => '081234567890',
        ]);
        $this->adminUser->assignRole($role);

        $this->regularUser = User::factory()->create([
            'phone' => '081234567891',
        ]);

        // Setup master data
        $this->catAsoy = WasteCategory::create([
            'name' => 'Asoy',
            'code' => 'ASY',
            'unit' => 'kg',
            'member_price' => 3000,
        ]);

        $this->catEmberan = WasteCategory::create([
            'name' => 'Emberan',
            'code' => 'EMB',
            'unit' => 'kg',
            'member_price' => 2500,
        ]);

        $this->catGabrukan = WasteCategory::create([
            'name' => 'Gabrukan',
            'code' => 'GBR',
            'unit' => 'kg',
            'member_price' => 4000,
        ]);

        $this->custSarti = WasteCustomer::create([
            'customer_code' => WasteCustomer::generateNextCustomerCode(),
            'name' => 'Bu Sarti',
            'phone' => '0811111111',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->custBudi = WasteCustomer::create([
            'customer_code' => WasteCustomer::generateNextCustomerCode(),
            'name' => 'Pak Budi',
            'phone' => '0822222222',
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        $dir = storage_path('app/temp_imports');
        if (File::isDirectory($dir)) {
            File::cleanDirectory($dir);
        }
        parent::tearDown();
    }

    /**
     * Helper to create real test Excel files.
     */
    protected function createExcelFile(array $sheetsData): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $isFirst = true;

        foreach ($sheetsData as $sheetTitle => $rows) {
            if ($isFirst) {
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle((string) $sheetTitle);
                $isFirst = false;
            } else {
                $sheet = $spreadsheet->createSheet();
                $sheet->setTitle((string) $sheetTitle);
            }
            $sheet->fromArray($rows, null, 'A1');
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'test_ecobank_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return new UploadedFile(
            $tempPath,
            'catatan_timbangan.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    protected function extractTokenFromRedirect($response): string
    {
        $location = $response->headers->get('Location');
        parse_str(parse_url($location, PHP_URL_QUERY), $query);
        return $query['token'] ?? '';
    }

    public function test_unauthorized_user_cannot_access_import_routes(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('bank-sampah.deposits.import'));
        $response->assertStatus(403);
    }

    public function test_upload_file_detects_sheets_and_guesses_date(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 5, 3000, 15000],
            ],
            '12-10-26' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Pak Budi', 'Emberan', 3, 2500, 7500],
            ],
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $token = $this->extractTokenFromRedirect($response);
        $this->assertNotEmpty($token);

        // Follow redirect to see sheet selection view
        $viewResponse = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('28-09-2026');
        $viewResponse->assertSee('12-10-26');
    }

    public function test_invalid_file_rejected_by_mime_and_extension(): void
    {
        $invalidFile = UploadedFile::fake()->create('hacked.php', 100, 'application/x-php');

        $response = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), [
            'file' => $invalidFile,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_parsing_exact_customer_and_material_auto_matches(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 5, 3000, 15000],
                ['Pak Budi', 'Gabrukan', 8, 4000, 32000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $previewResponse = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $previewResponse->assertRedirect(route('bank-sampah.deposits.import', ['token' => $token]));

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertNotNull($staging);
        $this->assertCount(2, $staging['rows']);
        $this->assertEquals('VALID', $staging['rows'][0]['status']);
        $this->assertEquals($this->custSarti->id, $staging['rows'][0]['customer_id']);
        $this->assertEquals($this->catAsoy->id, $staging['rows'][0]['category_id']);
    }

    public function test_unmatched_customer_triggers_needs_mapping(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti Baru', 'Asoy', 5, 3000, 15000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertEquals('NEEDS MAPPING', $staging['rows'][0]['status']);
        $this->assertEquals('NEEDS MAPPING', $staging['rows'][0]['customer_status']);
    }

    public function test_quick_add_customer_resolves_all_identical_rows(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Warga Baru', 'Asoy', 2, 3000, 6000],
                ['Warga Baru', 'Emberan', 3, 2500, 7500],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // Quick add the new customer
        $response = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.quick-add-customer'), [
            'token' => $token,
            'raw_customer' => 'Warga Baru',
            'name' => 'Warga Baru',
            'phone' => '0899999999',
            'address' => 'RT 05',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('waste_customers', [
            'name' => 'Warga Baru',
            'phone' => '0899999999',
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        // Both rows must now be resolved to VALID!
        $this->assertEquals('VALID', $staging['rows'][0]['status']);
        $this->assertEquals('VALID', $staging['rows'][1]['status']);
        $this->assertEquals('Warga Baru', $staging['rows'][0]['customer_name']);
        $this->assertEquals('Warga Baru', $staging['rows'][1]['customer_name']);
    }

    public function test_bulk_add_customers_resolves_multiple_unmatched(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Nasabah X', 'Asoy', 2, 3000, 6000],
                ['Nasabah Y', 'Emberan', 3, 2500, 7500],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // Bulk Add both customers
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.bulk-add-customers'), [
            'token' => $token,
            'customers' => [
                ['raw_customer' => 'Nasabah X', 'name' => 'Nasabah X', 'phone' => '081211', 'address' => 'RW 01'],
                ['raw_customer' => 'Nasabah Y', 'name' => 'Nasabah Y', 'phone' => '081212', 'address' => 'RW 02'],
            ],
        ]);

        $this->assertDatabaseHas('waste_customers', ['name' => 'Nasabah X']);
        $this->assertDatabaseHas('waste_customers', ['name' => 'Nasabah Y']);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertEquals('VALID', $staging['rows'][0]['status']);
        $this->assertEquals('VALID', $staging['rows'][1]['status']);
    }

    public function test_unmatched_material_mapped_once_resolves_all_rows(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Plastik Kresek Asoy', 4, 3000, 12000],
                ['Pak Budi', 'Plastik Kresek Asoy', 2, 3000, 6000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // Map "Plastik Kresek Asoy" to $this->catAsoy
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.resolve'), [
            'token' => $token,
            'material_mappings' => [
                'Plastik Kresek Asoy' => $this->catAsoy->id,
            ],
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertEquals('VALID', $staging['rows'][0]['status']);
        $this->assertEquals('VALID', $staging['rows'][1]['status']);
        $this->assertEquals($this->catAsoy->id, $staging['rows'][0]['category_id']);
        $this->assertEquals($this->catAsoy->id, $staging['rows'][1]['category_id']);
    }

    public function test_excel_price_used_as_snapshot_does_not_modify_master_member_price(): void
    {
        // Category Asoy has member_price = 3000 in DB, but Excel has 3500
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 10, 3500, 35000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // Commit import
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), [
            'token' => $token,
        ]);

        // Master category member_price must remain 3000!
        $this->catAsoy->refresh();
        $this->assertEquals(3000, (float) $this->catAsoy->member_price);

        // Transaction detail must store 3500 as snapshot
        $detail = DepositDetail::latest('id')->first();
        $this->assertEquals(3500, (float) $detail->price_per_unit);
        $this->assertEquals(35000, (float) $detail->subtotal);
    }

    public function test_deposit_grouping_multiple_materials_same_customer_creates_one_deposit_and_one_savings_ledger(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 5, 3000, 15000],
                ['Bu Sarti', 'Emberan', 2, 2500, 5000],
                ['Bu Sarti', 'Gabrukan', 1, 4000, 4000],
                ['Pak Budi', 'Gabrukan', 10, 4000, 40000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $commitResponse = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), [
            'token' => $token,
        ]);

        $commitResponse->assertRedirect(route('bank-sampah.deposits.import', ['result' => 'success']));

        // Exactly 2 Deposits must be created: 1 for Bu Sarti, 1 for Pak Budi!
        $this->assertEquals(2, Deposit::whereDate('date', '2026-09-28')->count());

        $sartiDeposit = Deposit::where('waste_customer_id', $this->custSarti->id)->first();
        $this->assertNotNull($sartiDeposit);
        $this->assertEquals(3, $sartiDeposit->details()->count());
        $this->assertEquals(24000, (float) $sartiDeposit->total_amount);

        // Exactly 1 SavingsLedger for Bu Sarti with amount 24,000
        $ledger = SavingsLedger::where('waste_customer_id', $this->custSarti->id)
            ->where('reference_id', $sartiDeposit->id)
            ->first();
        $this->assertNotNull($ledger);
        $this->assertEquals(24000, (float) $ledger->amount);
        $this->assertEquals('credit', $ledger->type);
    }

    public function test_exact_reimport_fingerprint_blocked(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 5, 3000, 15000],
            ],
        ]);

        // First import
        $res1 = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token1 = $this->extractTokenFromRedirect($res1);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token1,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), ['token' => $token1]);

        // Second import of the EXACT same file, sheet, and date
        // Create identical file content
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('28-09-2026');
        $sheet->fromArray([
            ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
            ['Bu Sarti', 'Asoy', 5, 3000, 15000],
        ], null, 'A1');
        $tempPath2 = tempnam(sys_get_temp_dir(), 'test_ecobank_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath2);

        $file2 = new UploadedFile($tempPath2, 'catatan_timbangan.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $res2 = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file2]);
        $token2 = $this->extractTokenFromRedirect($res2);

        $preview2 = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token2,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $preview2->assertSessionHasErrors('sheet_name');
    }

    public function test_commit_blocked_if_errors_or_unmapped_rows_remain(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Unregistered Customer', 'Asoy', 5, 3000, 15000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // Attempt to commit with unmapped customer
        $commitResponse = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), [
            'token' => $token,
        ]);

        $commitResponse->assertSessionHasErrors('commit');
        $this->assertEquals(0, Deposit::count());
    }

    public function test_total_calculation_and_rounding_validation(): void
    {
        // Source-of-truth rule: Excel Total is honored as the transaction value,
        // even when Weight x Price recomputation differs (rounding tolerance).
        // 0.33 kg @ 3000 = 990 recomputed, but source says 991 -> transaction = 991, VALID.
        // 2 kg @ 2500 = 5000 recomputed, but source says 6000 -> transaction = 6000, VALID.
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 0.33, 3000, 991],
                ['Bu Sarti', 'Emberan', 2, 2500, 6000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertEquals('VALID', $staging['rows'][0]['status']);
        $this->assertFalse($staging['rows'][0]['has_total_warning']);
        $this->assertEquals(991, (float) $staging['rows'][0]['system_total']);

        $this->assertEquals('VALID', $staging['rows'][1]['status']);
        $this->assertFalse($staging['rows'][1]['has_total_warning']);
        $this->assertEquals(6000, (float) $staging['rows'][1]['system_total']);
    }

    public function test_source_total_preserved_as_transaction_value(): void
    {
        // Excel: Budi | Kardus | 0.55 | 150 | 83 (recomputed = 82.5, source = 83)
        $cat = WasteCategory::create([
            'name' => 'Kardus',
            'code' => 'KRD',
            'unit' => 'kg',
            'member_price' => 150,
        ]);

        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Kardus', 0.55, 150, 83],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        // Source total honored, not recomputed 82.5
        $this->assertEquals(83, (float) $staging['rows'][0]['system_total']);
        $this->assertEquals('VALID', $staging['rows'][0]['status']);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), [
            'token' => $token,
        ]);

        // Deposit total, ledger credit, and saldo all reflect Rp83 (no double credit)
        $deposit = Deposit::where('waste_customer_id', $this->custSarti->id)->first();
        $this->assertNotNull($deposit);
        $this->assertEquals(83, (float) $deposit->total_amount);

        $ledgers = SavingsLedger::where('waste_customer_id', $this->custSarti->id)
            ->where('reference_id', $deposit->id)->get();
        $this->assertCount(1, $ledgers);
        $this->assertEquals(83, (float) $ledgers->first()->amount);

        $balance = app(\App\Services\BankSampahService::class)->getCustomerBalance($this->custSarti->id);
        $this->assertEquals(83, $balance);

        // Master price change afterwards must not alter the historical transaction
        $cat->update(['member_price' => 9999]);
        $this->assertEquals(83, (float) $deposit->fresh()->total_amount);
        $this->assertEquals(83, (float) $deposit->details()->first()->subtotal);
    }

    public function test_missing_total_falls_back_to_weight_times_price(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 4, 3000, null],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertEquals(12000, (float) $staging['rows'][0]['system_total']);
    }

    public function test_material_and_customer_recap_with_grand_totals(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 10, 3000, 30000],
                ['Bu Sarti', 'Emberan', 4, 2500, 10000],
                ['Pak Budi', 'Asoy', 5, 3000, 15000],
                ['Pak Budi', 'Kardus Box Coklat', 2, 3000, 6000],
            ],
        ]);

        // Kardus Box Coklat is unknown -> map it to Asoy once
        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.resolve'), [
            'token' => $token,
            'material_mappings' => ['Kardus Box Coklat' => $this->catAsoy->id],
        ]);

        $preview = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $preview->assertStatus(200);
        // Resolve workspace: tabbed entity UI, no giant stacked recap
        $preview->assertSee('Nasabah (', false);
        $preview->assertSee('Material (', false);
        $preview->assertSee('Preview Data', false);
        $preview->assertSee('Search nasabah', false);
        $preview->assertSee('Lanjut ke Rekap', false);
        $preview->assertDontSee('Rekap Material', false);

        // Step 3 recap workspace renders recap tables with grand totals
        $recapView = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'recap']));
        $recapView->assertStatus(200);
        $recapView->assertSee('Rekap Material', false);
        $recapView->assertSee('Rekap Nasabah', false);
        $recapView->assertSee('Grand Total', false);
        $recapView->assertSee('Kembali ke Resolusi', false);
        $recapView->assertSee('Simpan & Import 4 Data', false);

        // Recap search + pagination controls for both tables
        $recapView->assertSee('Cari material...', false);
        $recapView->assertSee('Cari nasabah...', false);

        // Sticky grand total summary (Berat + Nilai side by side)
        $recapView->assertSee('Grand Total Berat', false);
        $recapView->assertSee('Grand Total Nilai', false);

        // Cancel session action with confirmation copy
        $recapView->assertSee('Batalkan Sesi Import', false);
        $recapView->assertSee('Batalkan Sesi Import?', false);

        $staging = Cache::get("deposit_import_{$token}");
        $service = app(\App\Services\DepositImportService::class);
        $recap = $service->buildRecap($staging['rows'], $staging['duplicate_policies'] ?? []);

        // Distinct materials stay separate (Asoy vs Emberan), mapped label joins Asoy
        $byName = collect($recap['materials'])->keyBy('name');
        $this->assertArrayHasKey('Asoy', $byName);
        $this->assertArrayHasKey('Emberan', $byName);
        $this->assertEquals(10 + 5 + 2, (float) $byName['Asoy']['total_weight']);
        $this->assertEquals('kg', $byName['Asoy']['unit']);
        $this->assertEquals(4, (float) $byName['Emberan']['total_weight']);

        // Customer recap per nasabah
        $byCust = collect($recap['customers'])->keyBy('name');
        $this->assertEquals(14, (float) $byCust['Bu Sarti']['total_weight']);
        $this->assertEquals(40000, (float) $byCust['Bu Sarti']['total_amount']);
        $this->assertEquals(7, (float) $byCust['Pak Budi']['total_weight']);
        $this->assertEquals(21000, (float) $byCust['Pak Budi']['total_amount']);

        // Grand totals consistent across both recaps
        $this->assertEquals(21, (float) $recap['grand_weight']);
        $this->assertEquals(61000, (float) $recap['grand_amount']);
        $this->assertEquals($recap['grand_weight'], (float) array_sum(array_column($recap['materials'], 'total_weight')));
        $this->assertEquals($recap['grand_weight'], (float) array_sum(array_column($recap['customers'], 'total_weight')));
        $this->assertEquals($recap['grand_amount'], (float) array_sum(array_column($recap['customers'], 'total_amount')));
    }

    public function test_withdrawal_requires_two_deposits_and_ignores_details(): void
    {
        $service = app(\App\Services\BankSampahService::class);

        // 1 Deposit with 10 materials still counts as 1 Deposit -> not eligible
        $details = [];
        for ($i = 0; $i < 10; $i++) {
            $details[] = ['waste_category_id' => $this->catAsoy->id, 'weight' => 1, 'price_per_unit' => 3000, 'subtotal' => 3000];
        }
        $service->recordDeposit([
            'waste_customer_id' => $this->custSarti->id,
            'date' => '2026-10-01',
            'details' => $details,
        ]);

        $blocked = false;
        try {
            $service->recordWithdrawal([
                'waste_customer_id' => $this->custSarti->id,
                'amount' => 1000,
                'date' => '2026-10-01',
            ]);
        } catch (\App\Exceptions\MinimumDepositException $e) {
            $blocked = true;
        }
        $this->assertTrue($blocked, 'Withdrawal should be blocked after only 1 deposit');

        {
            // 2nd Deposit on another date -> eligible
            $service->recordDeposit([
                'waste_customer_id' => $this->custSarti->id,
                'date' => '2026-10-02',
                'details' => [['waste_category_id' => $this->catAsoy->id, 'weight' => 1, 'price_per_unit' => 3000, 'subtotal' => 3000]],
            ]);

            $withdrawal = $service->recordWithdrawal([
                'waste_customer_id' => $this->custSarti->id,
                'amount' => 1000,
                'date' => '2026-10-02',
            ]);
            $this->assertNotNull($withdrawal->id);
            $this->assertEquals(2, Deposit::where('waste_customer_id', $this->custSarti->id)->count());
        }
    }

    public function test_reimport_shows_informative_warning_and_stays_blocked(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 5, 3000, 15000],
            ],
        ]);

        $res1 = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token1 = $this->extractTokenFromRedirect($res1);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token1,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), ['token' => $token1]);

        $ledgersBefore = SavingsLedger::where('waste_customer_id', $this->custSarti->id)->count();

        // Re-upload identical file/sheet/date
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('28-09-2026');
        $sheet->fromArray([
            ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
            ['Bu Sarti', 'Asoy', 5, 3000, 15000],
        ], null, 'A1');
        $tempPath2 = tempnam(sys_get_temp_dir(), 'test_ecobank_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath2);
        $file2 = new UploadedFile($tempPath2, 'catatan_timbangan.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $res2 = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file2]);
        $token2 = $this->extractTokenFromRedirect($res2);

        $preview2 = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token2,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // Informative warning shown AND still blocked (no double credit possible)
        $preview2->assertSessionHasErrors('sheet_name');
        $errors = session('errors');
        $this->assertStringContainsString('sudah pernah dicoba diimpor', $errors->get('sheet_name')[0]);
        $this->assertEquals($ledgersBefore, SavingsLedger::where('waste_customer_id', $this->custSarti->id)->count());
    }

    public function test_duplicate_same_customer_and_date_append_or_skip(): void
    {
        // Existing deposit for Bu Sarti on 2026-09-28
        $existing = Deposit::create([
            'waste_customer_id' => $this->custSarti->id,
            'date' => '2026-09-28',
            'total_amount' => 10000,
        ]);

        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 5, 3000, 15000],
                ['Pak Budi', 'Emberan', 4, 2500, 10000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // Bu Sarti flagged as having existing deposit
        $staging = Cache::get("deposit_import_{$token}");
        $this->assertTrue($staging['rows'][0]['has_existing_deposit']);

        // Set policy for Bu Sarti to 'skip'
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.resolve'), [
            'token' => $token,
            'duplicate_policies' => [
                $this->custSarti->id => 'skip',
            ],
        ]);

        // Commit import
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), [
            'token' => $token,
        ]);

        // Pak Budi gets imported, Bu Sarti is skipped (so only 1 new deposit is created, total deposits on this date = 2 including existing)
        $this->assertEquals(2, Deposit::whereDate('date', '2026-09-28')->count());
        $this->assertEquals(1, Deposit::where('waste_customer_id', $this->custBudi->id)->count());
        $this->assertEquals(1, Deposit::where('waste_customer_id', $this->custSarti->id)->count());
    }

    public function test_empty_rows_are_ignored_and_invalid_weight_produces_error(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 5, 3000, 15000],
                [null, null, null, null, null], // completely blank row
                ['Pak Budi', 'Emberan', -2, 2500, -5000], // negative weight => ERROR
                ['', '', '', '', ''], // empty string row
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        // Total rows must be 2 (blank rows ignored!)
        $this->assertCount(2, $staging['rows']);
        $this->assertEquals('VALID', $staging['rows'][0]['status']);
        $this->assertEquals('ERROR', $staging['rows'][1]['status']);
    }

    /**
     * Realistic 300-row scenario: repeated customer/material labels must be
     * resolvable through UNIQUE entity actions only, never row by row.
     *
     * Layout: 50 rows exact-matched + 250 rows across 8 new unique customers
     * and 3 new unique materials.
     */
    public function test_300_rows_resolve_through_unique_entity_actions_only(): void
    {
        $sheetRows = [['Nasabah', 'Material', 'Berat', 'Harga', 'Total']];

        // 50 rows of exact matches (automatically VALID)
        for ($i = 0; $i < 30; $i++) {
            $sheetRows[] = ['Bu Sarti', 'Asoy', 5, 3000, 15000];
        }
        for ($i = 0; $i < 20; $i++) {
            $sheetRows[] = ['Pak Budi', 'Emberan', 4, 2500, 10000];
        }

        // 8 new unique customers with repeated labels (250 rows total)
        $newCustomers = [
            'Warga Baru Alpha' => 45,
            'Warga Baru Beta' => 40,
            'Warga Baru Gamma' => 35,
            'Warga Baru Delta' => 30,
            'Warga Baru Epsilon' => 30,
            'Warga Baru Zeta' => 25,
            'Warga Baru Eta' => 25,
            'Warga Baru Theta' => 20,
        ];

        // 3 new unique materials mapped to existing categories by price
        $newMaterials = [
            'Kardus Aqua Bekas' => ['category' => 'catAsoy', 'price' => 3000],
            'Botol Kaca Hijau' => ['category' => 'catEmberan', 'price' => 2500],
            'Koran Bekas Tebal' => ['category' => 'catGabrukan', 'price' => 4000],
        ];
        $newMaterialNames = array_keys($newMaterials);

        $rowIdx = 0;
        foreach ($newCustomers as $custName => $rowCount) {
            for ($i = 0; $i < $rowCount; $i++) {
                // First rows of each customer use a new material label (cycled),
                // remaining rows use an existing master material.
                if ($i < 10) {
                    $matName = $newMaterialNames[$rowIdx % 3];
                    $price = $newMaterials[$matName]['price'];
                } else {
                    $matName = 'Asoy';
                    $price = 3000;
                }
                $sheetRows[] = [$custName, $matName, 2, $price, 2 * $price];
                $rowIdx++;
            }
        }

        $this->assertCount(301, $sheetRows); // header + 300 data rows

        $file = $this->createExcelFile(['28-09-2026' => $sheetRows]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);
        $this->assertNotEmpty($token);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertCount(300, $staging['rows']);

        // 1. Unique grouping: 8 unresolved customers (not 250 actions), 3 unresolved materials
        $previewRes = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $previewRes->assertStatus(200);

        $unmatchedCustomers = [];
        $unmatchedMaterials = [];
        foreach ($staging['rows'] as $r) {
            if (in_array($r['customer_status'], ['NEEDS MAPPING', 'AMBIGUOUS'], true)) {
                $unmatchedCustomers[$r['raw_customer']] = true;
            }
            if ($r['material_status'] === 'NEEDS MAPPING') {
                $unmatchedMaterials[$r['raw_material']] = true;
            }
        }
        $this->assertCount(8, $unmatchedCustomers);
        // Final concept: unknown materials automatically become their own
        // (3 distinct materials, 0 blocking) instead of NEEDS MAPPING
        $this->assertCount(0, $unmatchedMaterials);
        $autoCats = WasteCategory::whereIn('name', array_keys($newMaterials))->get();
        $this->assertCount(3, $autoCats);
        $this->assertCount(3, $autoCats->pluck('id')->unique());

        // Existing exact matches remain automatically VALID
        $exactValid = array_filter($staging['rows'], fn($r) => in_array($r['raw_customer'], ['Bu Sarti', 'Pak Budi'], true));
        $this->assertCount(50, $exactValid);
        foreach ($exactValid as $r) {
            $this->assertEquals('VALID', $r['status']);
        }

        // 2 & 3. ONE bulk operation creates all 8 customers and propagates to every repeated row
        $bulkPayload = ['token' => $token, 'customers' => []];
        foreach (array_keys($newCustomers) as $custName) {
            $bulkPayload['customers'][] = ['raw_customer' => $custName, 'name' => $custName];
        }

        $this->actingAs($this->adminUser)
            ->post(route('bank-sampah.deposits.import.bulk-add-customers'), $bulkPayload)
            ->assertRedirect();

        // 4. No duplicate customers for repeated raw names
        foreach (array_keys($newCustomers) as $custName) {
            $this->assertEquals(1, WasteCustomer::where('name', $custName)->count());
        }

        $staging = Cache::get("deposit_import_{$token}");
        foreach ($staging['rows'] as $r) {
            // Every row now references a customer (propagation to all repeated rows)
            $this->assertNotNull($r['customer_id'], "Row {$r['excel_row_num']} still has no customer");
        }

        // 5 & 6. ONE explicit mapping per unique material resolves all its repeated rows
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.resolve'), [
            'token' => $token,
            'material_mappings' => [
                'Kardus Aqua Bekas' => $this->catAsoy->id,
                'Botol Kaca Hijau' => $this->catEmberan->id,
                'Koran Bekas Tebal' => $this->catGabrukan->id,
            ],
        ])->assertRedirect();

        $staging = Cache::get("deposit_import_{$token}");
        foreach ($staging['rows'] as $r) {
            $this->assertNotNull($r['category_id'], "Row {$r['excel_row_num']} still has no category");
        }

        // 7 & 8. canCommit becomes true: preview shows ready state with zero unresolved blockers
        $readyRes = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $readyRes->assertStatus(200);
        $readyRes->assertSee('Siap Diimpor', false);
        $readyRes->assertDontSee('Belum Siap Diimpor', false);

        // Final commit uses existing rules and creates one deposit per unique customer (10 total)
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), [
            'token' => $token,
        ])->assertRedirect();

        $this->assertEquals(10, Deposit::whereDate('date', '2026-09-28')->count());
        $this->assertEquals(300, DepositDetail::count());
    }

    public function test_bulk_customer_creation_is_atomic_on_validation_failure(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Nasabah Atom A', 'Asoy', 2, 3000, 6000],
                ['Nasabah Atom B', 'Asoy', 2, 3000, 6000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // One entry violates validation (empty name) -> whole bulk operation must fail atomically
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.bulk-add-customers'), [
            'token' => $token,
            'customers' => [
                ['raw_customer' => 'Nasabah Atom A', 'name' => 'Nasabah Atom A'],
                ['raw_customer' => 'Nasabah Atom B', 'name' => ''],
            ],
        ]);

        $this->assertDatabaseMissing('waste_customers', ['name' => 'Nasabah Atom A']);
        $this->assertDatabaseMissing('waste_customers', ['name' => 'Nasabah Atom B']);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertEquals('NEEDS MAPPING', $staging['rows'][0]['status']);
        $this->assertEquals('NEEDS MAPPING', $staging['rows'][1]['status']);
    }

    /**
     * Dataset A: 100 rows, 10 unique customers, 5 unique materials.
     * Resolution UI works on unique entities; bulk add + one-mapping-per-label
     * resolves everything without row-level mapping.
     */
    public function test_dataset_a_100_rows_resolve_by_unique_entity(): void
    {
        $materials = [
            'Asoy' => 3000, 'Emberan' => 2500, 'Gabrukan' => 4000,
            'Kardus A Baru' => 3000, 'Botol B Baru' => 2500,
        ];
        $sheetRows = [['Nasabah', 'Material', 'Berat', 'Harga', 'Total']];
        $matNames = array_keys($materials);
        for ($i = 0; $i < 100; $i++) {
            $cust = 'Warga Entity ' . (($i % 10) + 1);
            $mat = $matNames[$i % 5];
            $price = $materials[$mat];
            $sheetRows[] = [$cust, $mat, 2, $price, 2 * $price];
        }

        $file = $this->createExcelFile(['28-09-2026' => $sheetRows]);
        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertCount(100, $staging['rows']);

        // Unique entity counts, not row counts
        $uniqueCust = collect($staging['rows'])->pluck('raw_customer')->unique();
        $uniqueMat = collect($staging['rows'])->pluck('raw_material')->unique();
        $this->assertCount(10, $uniqueCust);
        $this->assertCount(5, $uniqueMat);

        // Resolve workspace shows entity tabs (10 customers, 2 new materials)
        $resolve = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $resolve->assertStatus(200);
        $resolve->assertSee('Nasabah (10)', false);
        $resolve->assertSee('Material (5)', false);

        // Bulk add all 10 unique new customers in ONE operation
        $payload = ['token' => $token, 'customers' => []];
        for ($i = 1; $i <= 10; $i++) {
            $payload['customers'][] = ['raw_customer' => "Warga Entity $i", 'name' => "Warga Entity $i"];
        }
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.bulk-add-customers'), $payload);

        // One mapping per unique material label resolves all its repeated rows
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.resolve'), [
            'token' => $token,
            'material_mappings' => [
                'Kardus A Baru' => $this->catAsoy->id,
                'Botol B Baru' => $this->catEmberan->id,
            ],
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        $unresolved = array_filter($staging['rows'], fn($r) => in_array($r['status'], ['NEEDS MAPPING', 'AMBIGUOUS', 'ERROR'], true));
        $this->assertCount(0, $unresolved);

        // Recap view renders and commit succeeds with 10 deposits (1 per unique customer)
        $recap = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'recap']));
        $recap->assertStatus(200);
        $recap->assertSee('Simpan & Import 100 Data', false);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), ['token' => $token]);
        $this->assertEquals(10, Deposit::whereDate('date', '2026-09-28')->count());
    }

    /**
     * Dataset B: 1000 rows, 50 unique customers, 20 unique materials.
     * No 1000 mapping controls; pagination + search present; mapping persists
     * across interactions; recap correct; commit dataset unchanged.
     */
    public function test_dataset_b_1000_rows_bounded_controls_with_pagination(): void
    {
        $sheetRows = [['Nasabah', 'Material', 'Berat', 'Harga', 'Total']];
        for ($i = 0; $i < 1000; $i++) {
            $cust = 'Warga Massal ' . (($i % 50) + 1);
            $matIdx = ($i % 20) + 1;
            // First 3 master materials exist; remaining 17 are new unique labels
            $mat = $matIdx <= 3
                ? ['Asoy', 'Emberan', 'Gabrukan'][$matIdx - 1]
                : 'Material Massal ' . $matIdx;
            $price = 3000;
            $sheetRows[] = [$cust, $mat, 1, $price, $price];
        }

        $file = $this->createExcelFile(['28-09-2026' => $sheetRows]);
        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertCount(1000, $staging['rows']);

        $resolve = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $resolve->assertStatus(200);
        // Entity-level tabs with unique counts (50 customers, 17 materials)
        $resolve->assertSee('Nasabah (50)', false);
        $resolve->assertSee('Material (20)', false);
        // Search + pagination controls exist; new-customer rows are status-only
        // (no per-row select: one bulk action covers all 50), material keeps per-label selects
        $resolve->assertSee('Search nasabah', false);
        $resolve->assertSee('Search material', false);
        $resolve->assertSee('Halaman', false);
        $resolve->assertSee('+ Tambahkan 50 Nasabah Baru', false);
        $this->assertEquals(0, substr_count($resolve->getContent(), 'name="customer_mappings['));
        // No per-row material mapping inputs: all 20 labels auto-listed as their own materials
        $this->assertEquals(0, substr_count($resolve->getContent(), 'name="material_mappings['));

        // Bulk add all 50, then map all 17 materials in ONE submit
        $payload = ['token' => $token, 'customers' => []];
        for ($i = 1; $i <= 50; $i++) {
            $payload['customers'][] = ['raw_customer' => "Warga Massal $i", 'name' => "Warga Massal $i"];
        }
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.bulk-add-customers'), $payload);

        $matMap = [];
        for ($i = 4; $i <= 20; $i++) {
            $matMap["Material Massal $i"] = $this->catAsoy->id;
        }
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.resolve'), [
            'token' => $token,
            'material_mappings' => $matMap,
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        $unresolved = array_filter($staging['rows'], fn($r) => in_array($r['status'], ['NEEDS MAPPING', 'AMBIGUOUS', 'ERROR'], true));
        $this->assertCount(0, $unresolved);

        // Recap + commit: 50 deposits for 1000 rows
        $recap = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'recap']));
        $recap->assertStatus(200);
        $recap->assertSee('Simpan & Import 1000 Data', false);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), ['token' => $token]);
        $this->assertEquals(50, Deposit::whereDate('date', '2026-09-28')->count());
        $this->assertEquals(1000, DepositDetail::count());
    }

    /**
     * Dataset C: duplicate raw customer/material labels across rows —
     * one entity mapping applies to every matching row.
     */
    public function test_dataset_c_duplicate_labels_resolve_once_for_all_rows(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['DUP Customer', 'DUP Material', 2, 3000, 6000],
                ['DUP Customer', 'Asoy', 1, 3000, 3000],
                ['Bu Sarti', 'DUP Material', 3, 3000, 9000],
                ['DUP Customer', 'DUP Material', 4, 3000, 12000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // One bulk add resolves all 3 rows carrying the duplicate label
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.bulk-add-customers'), [
            'token' => $token,
            'customers' => [['raw_customer' => 'DUP Customer', 'name' => 'DUP Customer']],
        ]);
        $this->assertEquals(1, WasteCustomer::where('name', 'DUP Customer')->count());

        // One material mapping resolves both rows carrying the duplicate label
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.resolve'), [
            'token' => $token,
            'material_mappings' => ['DUP Material' => $this->catAsoy->id],
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        foreach ($staging['rows'] as $r) {
            $this->assertEquals('VALID', $r['status']);
        }
    }

    /**
     * Dataset D: existing alias automatically resolves without manual mapping.
     */
    public function test_dataset_d_existing_alias_resolves_without_manual_mapping(): void
    {
        \App\Models\MaterialAlias::create([
            'source_type' => \App\Models\MaterialAlias::SOURCE_BANK_SAMPAH,
            'source_id' => null,
            'external_name' => 'Kresek Asoy Alias',
            'normalized_name' => 'kresek asoy alias',
            'waste_category_id' => $this->catAsoy->id,
            'is_active' => true,
        ]);

        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Kresek Asoy Alias', 2, 3000, 6000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertEquals('ALIAS', $staging['rows'][0]['status']);
        $this->assertEquals($this->catAsoy->id, $staging['rows'][0]['category_id']);
    }

    public function test_bulk_add_ux_ten_new_customers_single_action_with_feedback(): void
    {
        $rows = [['Nasabah', 'Material', 'Berat', 'Harga', 'Total']];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = ["Pelanggan Baru $i", 'Asoy', 2, 3000, 6000];
        }
        $file = $this->createExcelFile(['28-09-2026' => $rows]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // Step 2 shows ONE primary bulk action with the unique count
        $preview = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $preview->assertStatus(200);
        $preview->assertSee('+ Tambahkan 10 Nasabah', false);
        $preview->assertSee('+ Tambahkan 10 Nasabah Baru', false);
        $preview->assertSee('10 nasabah baru ditemukan', false);

        // Modal lists the 10 unique names for review
        $preview->assertSee('Tambah 10 Nasabah Baru', false);
        $preview->assertSee('Tambahkan 10 Nasabah & Hubungkan Otomatis', false);
        $preview->assertSee('otomatis dihubungkan ke seluruh baris Excel yang sesuai', false);

        // Bulk button opens the modal via window event (scope-independent wiring)
        $preview->assertSee("open-bulk-nasabah-modal", false);
        $preview->assertSee('bulkNsbOpen', false);

        // ONE submit creates all 10 and propagates to staged rows
        $payload = ['token' => $token, 'customers' => []];
        for ($i = 1; $i <= 10; $i++) {
            $payload['customers'][] = ['raw_customer' => "Pelanggan Baru $i", 'name' => "Pelanggan Baru $i"];
        }
        $res = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.bulk-add-customers'), $payload);
        $res->assertRedirect();
        $res->assertSessionHas('success');

        $this->assertEquals(10, WasteCustomer::where('name', 'like', 'Pelanggan Baru%')->count());

        $staging = Cache::get("deposit_import_{$token}");
        foreach ($staging['rows'] as $r) {
            $this->assertEquals('VALID', $r['status']);
            $this->assertNotNull($r['customer_id']);
        }

        // Resolution summary flips to complete
        $after = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $after->assertStatus(200);
        $after->assertSee('Nasabah (10)', false);
        $after->assertSee('10/10', false);
        $after->assertSee('Nasabah Terhubung.', false);
    }

    public function test_bulk_add_ux_many_unresolved_needs_single_action_not_52_individual(): void
    {
        $rows = [['Nasabah', 'Material', 'Berat', 'Harga', 'Total']];
        for ($i = 1; $i <= 52; $i++) {
            // Each name repeats 3x to prove unique-entity (not row) resolution
            for ($r = 0; $r < 3; $r++) {
                $rows[] = ["Warga Massal $i", 'Asoy', 1, 3000, 3000];
            }
        }
        $file = $this->createExcelFile(['28-09-2026' => $rows]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $preview = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $preview->assertStatus(200);
        // Exactly ONE primary bulk action for all 52
        $preview->assertSee('+ Tambahkan 52 Nasabah', false);
        $preview->assertSee('+ Tambahkan 52 Nasabah Baru', false);
        $preview->assertSee('Tambahkan 52 Nasabah & Hubungkan Otomatis', false);
        $preview->assertSee('52 nasabah baru ditemukan', false);
        // Per-row fallback exists but is visually quieter (ghost style, no primary bg)
        $this->assertEquals(1, substr_count($preview->getContent(), '+ Tambahkan 52 Nasabah'));

        $payload = ['token' => $token, 'customers' => []];
        for ($i = 1; $i <= 52; $i++) {
            $payload['customers'][] = ['raw_customer' => "Warga Massal $i", 'name' => "Warga Massal $i"];
        }
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.bulk-add-customers'), $payload);

        // No duplicate customers despite 3x repeated labels
        $this->assertEquals(52, WasteCustomer::where('name', 'like', 'Warga Massal%')->count());

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertCount(156, $staging['rows']);
        foreach ($staging['rows'] as $r) {
            $this->assertEquals('VALID', $r['status']);
        }
    }

    public function test_bulk_add_ux_mixed_new_existing_ambiguous_keeps_paths_separate(): void
    {
        // Two customers sharing one normalized name -> ambiguity
        WasteCustomer::create([
            'customer_code' => WasteCustomer::generateNextCustomerCode(),
            'name' => 'Ahmad Hidayat', 'status' => 'active', 'joined_at' => now(),
        ]);
        WasteCustomer::create([
            'customer_code' => WasteCustomer::generateNextCustomerCode(),
            'name' => 'AHMAD HIDAYAT', 'status' => 'active', 'joined_at' => now(),
        ]);

        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 2, 3000, 6000],
                ['Warga Campuran Baru', 'Asoy', 2, 3000, 6000],
                ['ahmad hidayat', 'Asoy', 2, 3000, 6000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $preview = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $preview->assertStatus(200);
        // Bulk Add path covers all unmatched names (1 new + 1 ambiguous = 2)...
        $preview->assertSee('+ Tambahkan 2 Nasabah', false);
        // ...while ambiguous one ALSO gets the choose path, not creation-only
        $preview->assertSee('Nama ganda di database', false);
        $preview->assertSee('-- Pilih Nasabah --', false);

        // Bulk-adding only the genuinely new name leaves ambiguity blocking
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.bulk-add-customers'), [
            'token' => $token,
            'customers' => [['raw_customer' => 'Warga Campuran Baru', 'name' => 'Warga Campuran Baru']],
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        $statuses = array_column($staging['rows'], 'status', 'raw_customer');
        $this->assertEquals('VALID', $statuses['Warga Campuran Baru']);
        $this->assertEquals('AMBIGUOUS', $statuses['ahmad hidayat']);
    }

    public function test_bulk_add_ux_failure_keeps_staging_without_false_success(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Gagal Satu', 'Asoy', 2, 3000, 6000],
                ['Gagal Dua', 'Asoy', 2, 3000, 6000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // Second entry violates validation (empty name) -> whole bulk fails atomically
        $res = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.bulk-add-customers'), [
            'token' => $token,
            'customers' => [
                ['raw_customer' => 'Gagal Satu', 'name' => 'Gagal Satu'],
                ['raw_customer' => 'Gagal Dua', 'name' => ''],
            ],
        ]);

        // No false success message, error surfaced instead
        $res->assertSessionMissing('success');
        $res->assertSessionHasErrors();
        $this->assertDatabaseMissing('waste_customers', ['name' => 'Gagal Satu']);

        // Staging preserved: rows still unresolved, session intact
        $staging = Cache::get("deposit_import_{$token}");
        $this->assertNotNull($staging);
        $this->assertEquals('NEEDS MAPPING', $staging['rows'][0]['status']);
        $this->assertEquals('NEEDS MAPPING', $staging['rows'][1]['status']);

        $preview = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $preview->assertStatus(200);
        $preview->assertSee('+ Tambahkan 2 Nasabah', false);
    }

    public function test_bulk_add_53_new_customers_resolves_all_rows_updates_counter_and_readiness(): void
    {
        $rows = [['Nasabah', 'Material', 'Berat', 'Harga', 'Total']];
        for ($i = 1; $i <= 53; $i++) {
            $rows[] = ["Nasabah Uji $i", 'Asoy', 1, 3000, 3000];
        }
        $file = $this->createExcelFile(['28-09-2026' => $rows]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $preview = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $preview->assertStatus(200);

        // Verify modal is not nested inside any tab container with x-show.
        // It carries its own scope and opens via window event (scope-independent).
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($preview->getContent());
        $xpath = new \DOMXPath($doc);
        $modals = $xpath->query("//*[@x-show='bulkNsbOpen']");
        $this->assertEquals(1, $modals->length);
        $modal = $modals->item(0);
        $this->assertTrue($modal->hasAttribute('x-data'), 'Modal must carry its own Alpine scope');
        $this->assertStringContainsString(
            'open-bulk-nasabah-modal',
            $modal->getAttribute('x-on:open-bulk-nasabah-modal.window') . $modal->ownerDocument->saveHTML($modal),
            'Modal must listen for the bulk open event'
        );
        $parent = $modal->parentNode;
        $this->assertFalse($parent->hasAttribute('x-show'), 'Modal must not be nested inside any tab with x-show');

        // 1. Tab Nasabah (53) and primary action
        $preview->assertSee('Nasabah (53)', false);
        $preview->assertSee('+ Tambahkan 53 Nasabah Baru', false);
        $preview->assertSee('53 nasabah baru ditemukan', false);

        // 2. Modal review list and CTA
        $preview->assertSee('Tambah 53 Nasabah Baru', false);
        $preview->assertSee('Tambahkan 53 Nasabah & Hubungkan Otomatis', false);
        for ($i = 1; $i <= 53; $i++) {
            $preview->assertSee("Nasabah Uji $i", false);
        }

        // 3. Counter and readiness before resolution
        $preview->assertSee('Nasabah (53)', false);
        $preview->assertSee('0/53', false);
        $preview->assertSee('Belum Siap Diimpor', false);

        // 4. Submit bulk add for all 53 customers
        $payload = ['token' => $token, 'customers' => []];
        for ($i = 1; $i <= 53; $i++) {
            $payload['customers'][] = [
                'raw_customer' => "Nasabah Uji $i",
                'name' => "Nasabah Uji $i",
            ];
        }
        $res = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.bulk-add-customers'), $payload);
        $res->assertRedirect(route('bank-sampah.deposits.import', ['token' => $token]));
        $res->assertSessionHas('success');

        // 5. 53 customers created using existing mechanism
        $this->assertEquals(53, WasteCustomer::where('name', 'like', 'Nasabah Uji%')->count());

        // 6. All Excel rows automatically connected
        $staging = Cache::get("deposit_import_{$token}");
        $this->assertCount(53, $staging['rows']);
        foreach ($staging['rows'] as $r) {
            $this->assertEquals('VALID', $r['status']);
            $this->assertNotNull($r['customer_id']);
        }

        // 7. Tab counter changes to Nasabah (53) 53/53, readiness updated
        $after = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $after->assertStatus(200);
        $after->assertSee('53/53', false);
        $after->assertSee('Nasabah Terhubung.', false);
        $after->assertSee('Siap Diimpor', false);
        $after->assertSee('Nasabah (53)', false);
    }

    public function test_batch_assign_existing_category_to_materials_from_recap(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 5, 3000, 15000],
                ['Bu Sarti', 'Emberan', 3, 2500, 7500],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // Access recap view
        $recapView = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'recap']));
        $recapView->assertStatus(200);
        $recapView->assertSee('Rekap Material', false);
        $recapView->assertSee('Kategori', false);
        $recapView->assertSee('+ Tambahkan Kategori', false);
        $recapView->assertSee('Belum dikategorikan', false);
        $recapView->assertSee('Pilih Kategori Yang Ada', false);
        $recapView->assertSee('+ Buat Kategori Baru', false);

        // Create an existing category group
        $group = \App\Models\WasteCategoryGroup::create([
            'name' => 'Plastik Daur Ulang',
            'code' => 'PDU',
            'is_active' => true,
        ]);

        // Batch assign existing category to both materials
        $res = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.assign-material-category'), [
            'token' => $token,
            'material_ids' => [$this->catAsoy->id, $this->catEmberan->id],
            'waste_category_group_id' => $group->id,
        ]);
        $res->assertRedirect(route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'recap']));
        $res->assertSessionHas('success');

        $this->assertEquals($group->id, $this->catAsoy->fresh()->waste_category_group_id);
        $this->assertEquals($group->id, $this->catEmberan->fresh()->waste_category_group_id);

        // Recap view now displays the category label
        $afterRecap = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'recap']));
        $afterRecap->assertStatus(200);
        $afterRecap->assertSee('Plastik Daur Ulang', false);
    }

    public function test_batch_assign_new_category_created_and_applied_from_recap(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 5, 3000, 15000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $recapView = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'recap']));
        $recapView->assertStatus(200);
        $recapView->assertSee('Pilih Kategori Yang Ada', false);
        $recapView->assertSee('+ Buat Kategori Baru', false);

        // Submit with new category name
        $res = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.assign-material-category'), [
            'token' => $token,
            'material_ids' => [$this->catAsoy->id],
            'new_category_name' => 'Kategori Baru Otomatis',
        ]);
        $res->assertRedirect(route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'recap']));
        $res->assertSessionHas('success');

        $createdGroup = \App\Models\WasteCategoryGroup::where('name', 'Kategori Baru Otomatis')->first();
        $this->assertNotNull($createdGroup);
        $this->assertEquals($createdGroup->id, $this->catAsoy->fresh()->waste_category_group_id);

        $afterRecap = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'recap']));
        $afterRecap->assertStatus(200);
        $afterRecap->assertSee('Kategori Baru Otomatis', false);
    }

    public function test_master_data_category_can_be_assigned_and_set_to_uncategorized(): void
    {
        $perm = Permission::firstOrCreate(['name' => 'manage_waste_prices', 'guard_name' => 'web']);
        $this->adminUser->givePermissionTo($perm);

        $group = \App\Models\WasteCategoryGroup::create([
            'name' => 'Grup Master Data',
            'code' => 'GMD',
            'is_active' => true,
        ]);

        // 1. Assign group via master data update
        $res = $this->actingAs($this->adminUser)->put(route('bank-sampah.waste-categories.update', $this->catAsoy), [
            'name' => $this->catAsoy->name,
            'unit' => $this->catAsoy->unit,
            'member_price' => $this->catAsoy->member_price,
            'waste_category_group_id' => $group->id,
        ]);
        $res->assertRedirect(route('bank-sampah.waste-categories.index'));
        $this->assertEquals($group->id, $this->catAsoy->fresh()->waste_category_group_id);

        // 2. Set to uncategorized (null) via master data update
        $res2 = $this->actingAs($this->adminUser)->put(route('bank-sampah.waste-categories.update', $this->catAsoy), [
            'name' => $this->catAsoy->name,
            'unit' => $this->catAsoy->unit,
            'member_price' => $this->catAsoy->member_price,
            'waste_category_group_id' => '',
        ]);
        $res2->assertRedirect(route('bank-sampah.waste-categories.index'));
        $this->assertNull($this->catAsoy->fresh()->waste_category_group_id);
    }

    public function test_resolve_ux_flow_nasabah_done_guides_to_material_and_recap(): void
    {
        // 1 customer (already registered: Bu Sarti), 2 unknown materials
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Material Belum Dipetakan 1', 5, 3000, 15000],
                ['Bu Sarti', 'Material Belum Dipetakan 2', 3, 2500, 7500],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $preview = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $preview->assertStatus(200);

        // Nasabah is 1/1 resolved
        $preview->assertSee('Nasabah (1)', false);
        $preview->assertSee('1/1', false);
        $preview->assertSee('Nasabah Terhubung.', false);
        // Bulk Add button is NOT shown because all customers are resolved
        $preview->assertDontSee('+ Tambahkan 1 Nasabah Baru', false);

        // Final concept: unknown materials auto-become their own — import is
        // ready immediately, no material mapping needed
        $preview->assertSee('Siap Diimpor', false);
        $preview->assertDontSee('Belum Siap Diimpor', false);

        // Material tab lists each label as its own material with Rekap CTA
        $preview->assertSee('2 Material Terdaftar', false);
        $preview->assertSee('Material Belum Dipetakan 1', false);
        $preview->assertSee('Material Belum Dipetakan 2', false);
        $preview->assertSee('Lanjut ke Rekap', false);
        $preview->assertDontSee('Tambah sebagai Material Baru', false);
        $preview->assertDontSee('Belum terhubung', false);

        // Submit mapping
        $mapRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.resolve'), [
            'token' => $token,
            'material_mappings' => [
                'Material Belum Dipetakan 1' => $this->catAsoy->id,
                'Material Belum Dipetakan 2' => $this->catEmberan->id,
            ],
        ]);

        // After mapping succeeds, redirected directly to Step Rekap
        $mapRes->assertRedirect(route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'recap']));
        $mapRes->assertSessionHas('success');

        // Access resolve view: shows all data ready status and CTA Lanjut ke Rekap
        $afterResolve = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'resolve']));
        $afterResolve->assertStatus(200);
        $afterResolve->assertSee('2 Material Terdaftar', false);
        $afterResolve->assertSee('Lanjut ke Rekap', false);
    }

    public function test_full_lifecycle_53_nasabah_30_materials_e2e_flow_and_database_verification(): void
    {
        // 1. Synthetic dataset: 53 unique new customers, 30 unique raw materials, 106 rows total
        $sheetRows = [['Nasabah', 'Material', 'Berat', 'Harga', 'Total']];
        $expectedGrandTotal = 0;
        $expectedGrandWeight = 0;
        $customerTotals = [];

        for ($c = 1; $c <= 53; $c++) {
            $custName = "Pelanggan E2E $c";
            $customerTotals[$custName] = 0;
            // 2 rows per customer with distinct materials from 30 unique labels
            for ($r = 1; $r <= 2; $r++) {
                $matIdx = (($c * 2 + $r) % 30) + 1; // 1 to 30
                $matLabel = "Label Material Unik $matIdx";
                $weight = 1.5;
                $targetCat = ($matIdx % 3 === 0) ? $this->catAsoy : (($matIdx % 3 === 1) ? $this->catEmberan : $this->catGabrukan);
                $price = (float) $targetCat->member_price;
                $rowTotal = (int) round($weight * $price);

                $sheetRows[] = [$custName, $matLabel, $weight, $price, $rowTotal];
                $expectedGrandTotal += $rowTotal;
                $expectedGrandWeight += $weight;
                $customerTotals[$custName] += $rowTotal;
            }
        }

        $file = $this->createExcelFile(['28-09-2026' => $sheetRows]);
        $tempCopyPath = tempnam(sys_get_temp_dir(), 'test_ecobank_dup_') . '.xlsx';
        copy($file->getPathname(), $tempCopyPath);

        // STEP 1: Upload Excel & Parse
        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $uploadRes->assertRedirect();
        $token = $this->extractTokenFromRedirect($uploadRes);
        $this->assertNotEmpty($token);

        // STEP 1.5: Preview Sheet & Confirm Date
        $previewRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);
        $previewRes->assertRedirect(route('bank-sampah.deposits.import', ['token' => $token]));

        // STEP 2A: Resolve Nasabah (Initial state: 53 unresolved customers)
        $step2A = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $step2A->assertStatus(200);
        $step2A->assertSee('53 nasabah baru ditemukan', false);
        $step2A->assertSee('+ Tambahkan 53 Nasabah Baru', false);
        $step2A->assertSee('Tambah 53 Nasabah Baru', false);
        $step2A->assertSee('Tambahkan 53 Nasabah & Hubungkan Otomatis', false);

        // Bulk Add all 53 customers
        $bulkPayload = ['token' => $token, 'customers' => []];
        for ($c = 1; $c <= 53; $c++) {
            $bulkPayload['customers'][] = [
                'raw_customer' => "Pelanggan E2E $c",
                'name' => "Pelanggan E2E $c",
            ];
        }
        $bulkRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.bulk-add-customers'), $bulkPayload);
        $bulkRes->assertRedirect(route('bank-sampah.deposits.import', ['token' => $token]));
        $bulkRes->assertSessionHas('success');

        // Verify 53 customers created in DB
        $this->assertEquals(53, WasteCustomer::where('name', 'like', 'Pelanggan E2E%')->count());
        $createdCustomers = WasteCustomer::where('name', 'like', 'Pelanggan E2E%')->get();
        foreach ($createdCustomers as $cust) {
            $this->assertNotNull($cust->customer_code);
            $this->assertEquals('active', $cust->status);
        }

        // STEP 2B: Verify Nasabah Terhubung & Material is Next Step
        $step2B = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $step2B->assertStatus(200);
        $step2B->assertSee('Nasabah (53)', false);
        $step2B->assertSee('53/53', false);
        $step2B->assertSee('Nasabah Terhubung.', false);
        // Bulk Add button is NOT shown because all are registered
        $step2B->assertDontSee('+ Tambahkan 53 Nasabah Baru', false);
        // Final concept: 30 labels auto-became 30 materials — ready, no mapping
        $step2B->assertSee('Siap Diimpor', false);
        $step2B->assertDontSee('Belum Siap Diimpor', false);
        $step2B->assertSee('30 Material Terdaftar', false);
        $step2B->assertSee('Lanjut ke Rekap', false);

        // STEP 2C: Map all 30 unique materials
        $materialMappings = [];
        for ($m = 1; $m <= 30; $m++) {
            $targetCat = ($m % 3 === 0) ? $this->catAsoy : (($m % 3 === 1) ? $this->catEmberan : $this->catGabrukan);
            $materialMappings["Label Material Unik $m"] = $targetCat->id;
        }

        $mapRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.resolve'), [
            'token' => $token,
            'material_mappings' => $materialMappings,
        ]);
        // System redirects directly to Step Rekap
        $mapRes->assertRedirect(route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'recap']));
        $mapRes->assertSessionHas('success');

        // STEP 3: Rekap Material + Rekap Nasabah Verification
        $recapView = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'recap']));
        $recapView->assertStatus(200);
        $recapView->assertSee('Rekap Material', false);
        $recapView->assertSee('Rekap Nasabah', false);
        $recapView->assertSee('Kategori', false);
        $recapView->assertSee('+ Tambahkan Kategori', false);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertNotNull($staging);
        $this->assertCount(106, $staging['rows']);
        foreach ($staging['rows'] as $r) {
            $this->assertEquals('VALID', $r['status']);
            $this->assertNotNull($r['customer_id']);
            $this->assertNotNull($r['category_id']);
        }

        $recap = app(\App\Services\DepositImportService::class)->buildRecap($staging['rows']);
        $this->assertEquals($expectedGrandWeight, (float) $recap['grand_weight']);
        $this->assertEquals($expectedGrandTotal, (float) $recap['grand_amount']);
        $this->assertCount(53, $recap['customers']);

        // STEP 4: Commit Import & Database Verification
        $bankSampahService = app(\App\Services\BankSampahService::class);
        foreach ($createdCustomers as $cust) {
            $this->assertEquals(0, $bankSampahService->getCustomerBalance($cust->id));
        }
        $this->assertEquals(0, Deposit::whereDate('date', '2026-09-28')->count());
        $this->assertEquals(0, DepositDetail::count());
        $this->assertEquals(0, SavingsLedger::count());

        $commitRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), ['token' => $token]);
        $commitRes->assertRedirect(route('bank-sampah.deposits.import', ['result' => 'success']));
        $commitRes->assertSessionHas('import_result');

        // Result view displays success
        $resultView = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['result' => 'success']));
        $resultView->assertStatus(200);
        $resultView->assertSee('Import Setoran Berhasil!', false);

        // Verification: 53 Deposits created (1 per customer on confirmed date)
        $this->assertEquals(53, Deposit::whereDate('date', '2026-09-28')->count());
        // Verification: 106 DepositDetails created
        $this->assertEquals(106, DepositDetail::count());
        // Verification: 53 SavingsLedger credits created
        $this->assertEquals(53, SavingsLedger::where('type', 'credit')->count());

        // Verification: each customer's balance increased by exact amount from Excel
        $totalBalanceSum = 0;
        foreach ($createdCustomers as $cust) {
            $balance = $bankSampahService->getCustomerBalance($cust->id);
            $expectedBal = $customerTotals[$cust->name];
            $this->assertEquals($expectedBal, $balance, "Customer {$cust->name} balance mismatch");
            $totalBalanceSum += $balance;
        }
        $this->assertEquals($expectedGrandTotal, $totalBalanceSum);

        // STEP 5: Protection / Idempotency
        // Second commit attempt with same token must fail
        $secondCommit = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), ['token' => $token]);
        $secondCommit->assertSessionHasErrors('file');

        // Re-upload same file/sheet/date blocked by fingerprint
        $dupFile = new UploadedFile($tempCopyPath, 'catatan_timbangan.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $dupUpload = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $dupFile]);
        $dupToken = $this->extractTokenFromRedirect($dupUpload);
        $dupPreview = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $dupToken,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);
        $dupPreview->assertSessionHasErrors('sheet_name');

        // Balances and deposits remain unchanged (no double credit)
        $this->assertEquals(53, Deposit::whereDate('date', '2026-09-28')->count());
        $this->assertEquals(106, DepositDetail::count());
        $this->assertEquals(53, SavingsLedger::count());
    }

    public function test_commit_rolls_back_atomically_on_database_failure(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 5, 3000, 15000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // Mock BankSampahService to throw an exception during recordDeposit
        $mock = $this->mock(\App\Services\BankSampahService::class);
        $mock->shouldReceive('recordDeposit')->andThrow(new \RuntimeException('Database simulation failure during insert.'));

        $commitRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), ['token' => $token]);
        $commitRes->assertSessionHasErrors('commit');

        // Rollback verified: 0 deposits, 0 details, 0 savings ledgers
        $this->assertEquals(0, Deposit::whereDate('date', '2026-09-28')->count());
        $this->assertEquals(0, DepositDetail::count());
        $this->assertEquals(0, SavingsLedger::count());
    }

    protected function seedBulkGuardStaging(string $token): void
    {
        Cache::put("deposit_import_{$token}", [
            'token' => $token,
            'file_path' => storage_path('app/temp_imports/' . $token . '.xlsx'),
            'filename' => 'catatan_timbangan.xlsx',
            'sheets' => ['28-09-2026'],
            'default_date' => '2026-09-28',
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
            'batch_code' => 'IMP-TEST-' . $token,
            'fingerprint' => md5($token),
            'raw_rows' => [],
            'rows' => [],
            'customer_mappings' => [],
            'material_mappings' => [],
            'price_modes' => [],
            'duplicate_policies' => [],
        ], now()->addHours(3));
    }

    public function test_bulk_quick_add_without_confirmation_is_blocked(): void
    {
        $token = 'bulk-guard-' . uniqid();
        $this->seedBulkGuardStaging($token);

        $res = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.quick-add-category'), [
            'token' => $token,
            'raw_materials' => ['Jelantah', 'Aki'],
            'name' => 'Lain Lain',
        ]);

        $res->assertRedirect(route('bank-sampah.deposits.import', ['token' => $token]));
        $res->assertSessionHasErrors('raw_materials');

        // Nothing is created: no master, no aliases, no staging mappings
        $this->assertDatabaseMissing('waste_categories', ['name' => 'Lain Lain']);
        $this->assertEquals(0, MaterialAlias::whereIn('external_name', ['Jelantah', 'Aki'])->count());
        $staging = Cache::get("deposit_import_{$token}");
        $this->assertEmpty($staging['material_mappings']);
    }

    public function test_bulk_quick_add_is_blocked_even_with_confirmation(): void
    {
        $token = 'bulk-guard-' . uniqid();
        $this->seedBulkGuardStaging($token);

        // Final concept: several Excel labels may never become one material,
        // with or without confirmation. Each label stays its own material.
        $res = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.quick-add-category'), [
            'token' => $token,
            'raw_materials' => ['Jelantah', 'Aki'],
            'name' => 'Lain Lain',
            'confirm_bulk_merge' => '1',
        ]);

        $res->assertRedirect(route('bank-sampah.deposits.import', ['token' => $token]));
        $res->assertSessionHasErrors('raw_materials');

        // Nothing is created: no merged material, no aliases, no staging mappings
        $this->assertDatabaseMissing('waste_categories', ['name' => 'Lain Lain']);
        $this->assertEquals(0, MaterialAlias::whereIn('external_name', ['Jelantah', 'Aki'])->count());
        $staging = Cache::get("deposit_import_{$token}");
        $this->assertEmpty($staging['material_mappings']);
    }

    public function test_single_element_raw_materials_array_creates_one_material(): void
    {
        $token = 'bulk-guard-' . uniqid();
        $this->seedBulkGuardStaging($token);

        $res = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.quick-add-category'), [
            'token' => $token,
            'raw_materials' => ['Jelantah'],
            'name' => 'Minyak Jelantah',
        ]);

        $res->assertRedirect(route('bank-sampah.deposits.import', ['token' => $token]));
        $res->assertSessionHas('success');

        $cat = WasteCategory::where('name', 'Minyak Jelantah')->first();
        $this->assertNotNull($cat);
        $this->assertDatabaseHas('material_aliases', [
            'external_name' => 'Jelantah',
            'waste_category_id' => $cat->id,
        ]);
    }

    public function test_unknown_materials_auto_become_own_materials_and_commit_without_mapping(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Kardus Bekas', 5, 3000, 15000],
                ['Bu Sarti', 'Koran', 3, 2500, 7500],
                ['Pak Budi', 'Kardus Remuk', 4, 3000, 12000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // No material mapping performed at all: each label is its own material
        $staging = Cache::get("deposit_import_{$token}");
        $this->assertCount(3, $staging['rows']);
        foreach ($staging['rows'] as $r) {
            $this->assertNotEquals('NEEDS MAPPING', $r['status']);
            $this->assertNotNull($r['category_id']);
        }
        $catIds = collect($staging['rows'])->pluck('category_id', 'raw_material');
        $this->assertCount(3, $catIds->unique());
        $this->assertEquals('Kardus Bekas', WasteCategory::find($catIds['Kardus Bekas'])->name);
        $this->assertEquals('Koran', WasteCategory::find($catIds['Koran'])->name);
        $this->assertEquals('Kardus Remuk', WasteCategory::find($catIds['Kardus Remuk'])->name);

        // Material tab lists all three as their own with Rekap CTA, nothing blocking
        $view = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $view->assertStatus(200);
        $view->assertSee('3 Material Terdaftar', false);
        $view->assertSee('Lanjut ke Rekap', false);
        $view->assertDontSee('Tambah sebagai Material Baru', false);
        $view->assertDontSee('Belum terhubung', false);

        // Commit directly without any material mapping or category
        $commitRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), ['token' => $token]);
        $commitRes->assertRedirect(route('bank-sampah.deposits.import', ['result' => 'success']));

        $this->assertEquals(2, Deposit::whereDate('date', '2026-09-28')->count());
        $this->assertEquals(3, DepositDetail::count());
        $detailsByCat = DepositDetail::pluck('waste_category_id');
        $this->assertCount(3, $detailsByCat->unique());
    }

    public function test_new_customer_rows_render_status_only_without_per_row_actions(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Warga Baru A', 'Asoy', 2, 3000, 6000],
                ['Warga Baru B', 'Asoy', 3, 3000, 9000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $view = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $view->assertStatus(200);

        // Single bulk action, status-only rows
        $view->assertSee('+ Tambahkan 2 Nasabah Baru', false);
        $view->assertSee('Nasabah Baru', false);
        $view->assertSee('Nasabah (2)', false);
        $view->assertSee('0/2', false);

        // No per-row manual actions for genuinely new names
        $view->assertDontSee('-- Pilih Nasabah --', false);
        $view->assertDontSee('Daftar Baru', false);
        $view->assertDontSee('Hubungkan Nasabah Terpilih', false);
    }

    public function test_auto_matched_customer_row_shows_connected_status(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 5, 3000, 15000],
                ['Warga Baru C', 'Asoy', 2, 3000, 6000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $view = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $view->assertStatus(200);

        $view->assertSee('Nasabah (2)', false);
        $view->assertSee('1/2', false);
        $view->assertSee('✓ Terhubung otomatis', false);
        $view->assertSee('+ Tambahkan 1 Nasabah Baru', false);
        $view->assertDontSee('-- Pilih Nasabah --', false);
    }

    public function test_exact_customer_code_auto_matches_without_mapping(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                [$this->custSarti->customer_code, 'Asoy', 5, 3000, 15000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertEquals('VALID', $staging['rows'][0]['status']);
        $this->assertEquals('MATCHED', $staging['rows'][0]['customer_status']);
        $this->assertEquals($this->custSarti->id, $staging['rows'][0]['customer_id']);

        $view = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $view->assertStatus(200);
        $view->assertSee('Nasabah (1)', false);
        $view->assertSee('1/1', false);
        $view->assertDontSee('-- Pilih Nasabah --', false);
    }

    public function test_exact_normalized_name_auto_matches_without_mapping(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['  bu   SARTI ', 'Asoy', 5, 3000, 15000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertEquals('VALID', $staging['rows'][0]['status']);
        $this->assertEquals('MATCHED', $staging['rows'][0]['customer_status']);
        $this->assertEquals($this->custSarti->id, $staging['rows'][0]['customer_id']);
    }

    public function test_single_quick_add_without_confirmation_still_works(): void
    {
        $token = 'bulk-guard-' . uniqid();
        $this->seedBulkGuardStaging($token);

        $res = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.quick-add-category'), [
            'token' => $token,
            'raw_material' => 'Jelantah',
            'name' => 'Minyak Jelantah',
        ]);

        $res->assertRedirect(route('bank-sampah.deposits.import', ['token' => $token]));
        $res->assertSessionHas('success');

        $cat = WasteCategory::where('name', 'Minyak Jelantah')->first();
        $this->assertNotNull($cat);
        $this->assertDatabaseHas('material_aliases', [
            'external_name' => 'Jelantah',
            'waste_category_id' => $cat->id,
        ]);
    }

    public function test_cancel_session_cleans_staging_and_returns_to_deposits_without_touching_db(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 5, 3000, 15000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $this->assertNotNull(Cache::get("deposit_import_{$token}"));

        $res = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.reset'), [
            'token' => $token,
        ]);

        // Back to Setoran page with confirmation message
        $res->assertRedirect(route('bank-sampah.deposits.index'));
        $res->assertSessionHas('success');

        // Staging/session/cache cleaned, nothing committed to operational tables
        $this->assertNull(Cache::get("deposit_import_{$token}"));
        $this->assertEquals(0, Deposit::count());
        $this->assertEquals(0, DepositDetail::count());
        $this->assertEquals(0, SavingsLedger::count());
    }
}
