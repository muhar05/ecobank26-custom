<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WasteCategory;
use App\Models\WasteCategoryGroup;

class SyncWasteCategoryGroups extends Command
{
    protected $signature = 'bank-sampah:sync-waste-category-groups';

    protected $description = 'Sync legacy string category_group from waste_categories to dynamic waste_category_group_id relations';

    public function handle()
    {
        $this->info('Starting sync of legacy category groups...');

        $categories = WasteCategory::all();
        $syncedCount = 0;
        $unmatchedCount = 0;

        // Fetch all groups to prevent repetitive DB queries
        $groups = WasteCategoryGroup::all();

        foreach ($categories as $category) {
            $legacyGroup = trim($category->category_group ?? '');

            if (empty($legacyGroup)) {
                // If it is empty, set group to null (Belum Dikategorikan)
                $category->update(['waste_category_group_id' => null]);
                continue;
            }

            // Normalization
            $normalizedLegacy = strtolower($legacyGroup);

            // Determine matching group code based on normalized string
            $targetCode = null;
            if (str_contains($normalizedLegacy, 'plastik') || str_contains($normalizedLegacy, 'pls')) {
                $targetCode = 'PLS';
            } elseif (str_contains($normalizedLegacy, 'kertas') || str_contains($normalizedLegacy, 'krt')) {
                $targetCode = 'KRT';
            } elseif (str_contains($normalizedLegacy, 'logam') || str_contains($normalizedLegacy, 'log')) {
                $targetCode = 'LOG';
            } elseif (str_contains($normalizedLegacy, 'kaca') || str_contains($normalizedLegacy, 'kca')) {
                $targetCode = 'KCA';
            } elseif (str_contains($normalizedLegacy, 'organik') || str_contains($normalizedLegacy, 'org')) {
                $targetCode = 'ORG';
            } elseif (str_contains($normalizedLegacy, 'elektronik') || str_contains($normalizedLegacy, 'elk')) {
                $targetCode = 'ELK';
            } elseif (str_contains($normalizedLegacy, 'lainnya') || str_contains($normalizedLegacy, 'lny')) {
                $targetCode = 'LNY';
            }

            $matchedGroup = null;
            if ($targetCode) {
                $matchedGroup = $groups->firstWhere('code', $targetCode);
            }

            // Direct fallback: check if any group name matches exactly case-insensitively
            if (!$matchedGroup) {
                $matchedGroup = $groups->first(function ($g) use ($normalizedLegacy) {
                    return strtolower($g->name) === $normalizedLegacy || strtolower($g->code) === $normalizedLegacy;
                });
            }

            if ($matchedGroup) {
                $category->update([
                    'waste_category_group_id' => $matchedGroup->id
                ]);
                $syncedCount++;
                $this->line("Synced '{$category->name}' to group '{$matchedGroup->name}' ({$matchedGroup->code})");
            } else {
                // If not matched, leave it as null or Lainnya? Leaving null is "Belum Dikategorikan" which is safer
                $category->update([
                    'waste_category_group_id' => null
                ]);
                $unmatchedCount++;
                $this->warn("Could not find matching group for legacy name: '{$legacyGroup}' (Category: '{$category->name}'). Left as 'Belum Dikategorikan'.");
            }
        }

        $this->info("Sync completed! Synced: {$syncedCount}, Unmatched/Reset to Null: {$unmatchedCount}");
        return self::SUCCESS;
    }
}
