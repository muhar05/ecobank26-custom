<?php

namespace App\Services;

use App\Models\CommunityCashLedger;
use App\Models\CommunityContribution;
use App\Models\CommunityExpense;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CommunityCashService
{
    public function recordContribution(array $data): CommunityContribution
    {
        return DB::transaction(function () use ($data) {
            $contribution = CommunityContribution::create($data);

            $lastBalance = $this->getLastBalance($data['fund_category_id']);

            CommunityCashLedger::create([
                'fund_category_id' => $data['fund_category_id'],
                'type' => 'in',
                'amount' => $data['amount'],
                'balance' => $lastBalance + $data['amount'],
                'reference_type' => 'contribution',
                'reference_id' => $contribution->id,
                'date' => $data['date'],
                'description' => $data['description'] ?? 'Iuran: ' . ($data['member_name'] ?? 'Warga'),
            ]);

            return $contribution;
        });
    }

    public function recordExpense(array $data): CommunityExpense
    {
        return DB::transaction(function () use ($data) {
            $expense = CommunityExpense::create($data);

            $lastBalance = $this->getLastBalance($data['fund_category_id']);

            CommunityCashLedger::create([
                'fund_category_id' => $data['fund_category_id'],
                'type' => 'out',
                'amount' => $data['amount'],
                'balance' => $lastBalance - $data['amount'],
                'reference_type' => 'expense',
                'reference_id' => $expense->id,
                'date' => $data['date'],
                'description' => $data['description'],
            ]);

            return $expense;
        });
    }

    public function getBalanceByCategory(?int $categoryId = null): Collection
    {
        $query = CommunityCashLedger::select('fund_category_id')
            ->selectRaw('MAX(id) as last_id');

        if ($categoryId) {
            $query->where('fund_category_id', $categoryId);
        }

        $lastIds = $query->groupBy('fund_category_id')->pluck('last_id');

        return CommunityCashLedger::whereIn('id', $lastIds)
            ->with('fundCategory')
            ->get()
            ->map(fn ($ledger) => [
                'fund_category_id' => $ledger->fund_category_id,
                'name' => $ledger->fundCategory->name,
                'balance' => $ledger->balance,
            ]);
    }

    private function getLastBalance(int $fundCategoryId): float
    {
        return (float) CommunityCashLedger::where('fund_category_id', $fundCategoryId)
            ->orderByDesc('id')
            ->value('balance') ?? 0;
    }
}
