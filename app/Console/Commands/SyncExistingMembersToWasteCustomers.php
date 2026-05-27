<?php

namespace App\Console\Commands;

use App\Models\Deposit;
use App\Models\Member;
use App\Models\SavingsLedger;
use App\Models\WasteCustomer;
use App\Models\Withdrawal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncExistingMembersToWasteCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bank-sampah:sync-customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync existing members to waste customers and update legacy transactions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting existing members sync to waste customers...');

        $members = Member::all();
        $totalMembers = $members->count();
        $this->info("Found {$totalMembers} members in the database.");

        $syncedCount = 0;
        $mappedDeposits = 0;
        $mappedWithdrawals = 0;
        $mappedLedgers = 0;

        foreach ($members as $member) {
            DB::transaction(function () use ($member, &$syncedCount, &$mappedDeposits, &$mappedWithdrawals, &$mappedLedgers) {
                // Find or create WasteCustomer for this member
                $customer = WasteCustomer::where('member_id', $member->id)->first();

                if (!$customer) {
                    $customer = WasteCustomer::create([
                        'user_id' => $member->user_id,
                        'member_id' => $member->id,
                        'customer_code' => WasteCustomer::generateNextCustomerCode(),
                        'name' => $member->name,
                        'phone' => $member->phone,
                        'address' => $member->address,
                        'status' => 'active',
                        'joined_at' => $member->created_at,
                    ]);
                    $syncedCount++;
                }

                // Map legacy deposits
                $updatedDeposits = Deposit::where('member_id', $member->id)
                    ->whereNull('waste_customer_id')
                    ->update(['waste_customer_id' => $customer->id]);
                $mappedDeposits += $updatedDeposits;

                // Map legacy withdrawals
                $updatedWithdrawals = Withdrawal::where('member_id', $member->id)
                    ->whereNull('waste_customer_id')
                    ->update(['waste_customer_id' => $customer->id]);
                $mappedWithdrawals += $updatedWithdrawals;

                // Map legacy savings ledgers
                $updatedLedgers = SavingsLedger::where('member_id', $member->id)
                    ->whereNull('waste_customer_id')
                    ->update(['waste_customer_id' => $customer->id]);
                $mappedLedgers += $updatedLedgers;
            });
        }

        $this->info("Successfully synced {$syncedCount} new waste customers.");
        $this->info("Mapped {$mappedDeposits} deposits to waste customers.");
        $this->info("Mapped {$mappedWithdrawals} withdrawals to waste customers.");
        $this->info("Mapped {$mappedLedgers} savings ledgers to waste customers.");

        return self::SUCCESS;
    }
}
