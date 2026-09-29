<?php

namespace App\Services;

use App\Models\MaterialAlias;
use App\Models\WasteCategory;
use App\Models\WasteCategoryGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MaterialMappingService
{
    /**
     * Standard centralized normalization for material labels.
     * Trims, lowercases, and collapses multiple spaces.
     */
    public function normalizeLabel(string $label): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $label)));
    }

    /**
     * Resolve an external material label through the multi-stage pipeline:
     * 1. Exact canonical match (Status: VALID)
     * 2. Known scoped alias match (Status: ALIAS)
     * 3. Candidate suggestions (Status: NEEDS_MAPPING)
     */
    public function resolve(string $rawLabel, string $sourceType = MaterialAlias::SOURCE_BANK_SAMPAH, ?int $sourceId = null): array
    {
        $clean = trim($rawLabel);
        if ($clean === '') {
            return [
                'status'     => 'ERROR',
                'match_type' => 'NONE',
                'category'   => null,
                'alias'      => null,
                'candidates' => collect(),
                'reason'     => 'Nama material kosong',
            ];
        }

        // STEP 1: Exact canonical match (code or normalized name)
        $canonical = $this->findCanonical($clean);
        if ($canonical) {
            return [
                'status'     => 'VALID',
                'match_type' => 'EXACT',
                'category'   => $canonical,
                'alias'      => null,
                'candidates' => collect(),
                'reason'     => 'Cocok dengan Master Kategori',
            ];
        }

        // STEP 2: Known alias match (scoped)
        $alias = $this->findAlias($clean, $sourceType, $sourceId);
        if ($alias && $alias->is_active && $alias->wasteCategory) {
            return [
                'status'     => 'ALIAS',
                'match_type' => 'ALIAS',
                'category'   => $alias->wasteCategory,
                'alias'      => $alias,
                'candidates' => collect(),
                'reason'     => "Cocok melalui Alias: '{$alias->external_name}'",
            ];
        }

        // STEP 3: Candidate suggestion for UI
        $candidates = $this->getCandidates($clean);

        return [
            'status'     => 'NEEDS_MAPPING',
            'match_type' => 'NONE',
            'category'   => null,
            'alias'      => null,
            'candidates' => $candidates,
            'reason'     => 'Material belum terdaftar di master maupun alias',
        ];
    }

    /**
     * Find exact canonical WasteCategory by code or normalized name.
     */
    public function findCanonical(string $rawLabel): ?WasteCategory
    {
        $normalized = $this->normalizeLabel($rawLabel);

        // Check exact code
        $byCode = WasteCategory::whereRaw('LOWER(code) = ?', [$normalized])->first();
        if ($byCode) {
            return $byCode;
        }

        // Check normalized name
        $categories = WasteCategory::with('wasteCategoryGroup')->get();
        foreach ($categories as $cat) {
            if ($this->normalizeLabel($cat->name) === $normalized) {
                return $cat;
            }
        }

        return null;
    }

    /**
     * Find known active MaterialAlias for the given scope.
     */
    public function findAlias(string $rawLabel, string $sourceType = MaterialAlias::SOURCE_BANK_SAMPAH, ?int $sourceId = null): ?MaterialAlias
    {
        $normalized = $this->normalizeLabel($rawLabel);

        $query = MaterialAlias::with(['wasteCategory.wasteCategoryGroup'])
            ->where('is_active', true)
            ->where('source_type', $sourceType)
            ->where('normalized_name', $normalized);

        if ($sourceType === MaterialAlias::SOURCE_BANK_SAMPAH) {
            $query->whereNull('source_id');
        } else {
            $query->where('source_id', $sourceId);
        }

        return $query->first();
    }

    /**
     * Generate candidate suggestions for UI based on group context or prefix tokens.
     * Never auto-selects; strictly for suggestion.
     */
    public function getCandidates(string $rawLabel): \Illuminate\Support\Collection
    {
        $normalized = $this->normalizeLabel($rawLabel);
        $tokens = explode(' ', $normalized);
        $firstWord = $tokens[0] ?? '';

        $candidateIds = collect();

        // 1. Check if first word matches a known WasteCategoryGroup
        if (mb_strlen($firstWord) >= 3) {
            $matchingGroup = WasteCategoryGroup::whereRaw('LOWER(name) = ? OR LOWER(code) = ?', [$firstWord, $firstWord])->first();
            if ($matchingGroup) {
                $categoryIds = WasteCategory::where('waste_category_group_id', $matchingGroup->id)->pluck('id');
                $candidateIds = $candidateIds->merge($categoryIds);
            }
        }

        // 2. Check if any category name starts with the first word
        if (mb_strlen($firstWord) >= 3) {
            $byPrefix = WasteCategory::whereRaw('LOWER(name) LIKE ?', [$firstWord . '%'])->pluck('id');
            $candidateIds = $candidateIds->merge($byPrefix);
        }

        // 3. Check if any active alias globally maps to a category
        $byGlobalAlias = MaterialAlias::where('normalized_name', $normalized)
            ->where('is_active', true)
            ->pluck('waste_category_id');
        $candidateIds = $candidateIds->merge($byGlobalAlias);

        if ($candidateIds->isEmpty()) {
            return collect();
        }

        return WasteCategory::with('wasteCategoryGroup')
            ->whereIn('id', $candidateIds->unique())
            ->take(8)
            ->get();
    }

    /**
     * Register or update an alias safely with application-level uniqueness protection.
     */
    public function registerAlias(
        string $externalName,
        int $wasteCategoryId,
        string $sourceType = MaterialAlias::SOURCE_BANK_SAMPAH,
        ?int $sourceId = null,
        bool $isActive = true
    ): MaterialAlias {
        $normalized = $this->normalizeLabel($externalName);

        return DB::transaction(function () use ($externalName, $normalized, $wasteCategoryId, $sourceType, $sourceId, $isActive) {
            $query = MaterialAlias::where('source_type', $sourceType)
                ->where('normalized_name', $normalized);

            if ($sourceType === MaterialAlias::SOURCE_BANK_SAMPAH) {
                $query->whereNull('source_id');
            } else {
                $query->where('source_id', $sourceId);
            }

            $existing = $query->first();

            if ($existing) {
                $existing->update([
                    'external_name'     => trim($externalName),
                    'waste_category_id' => $wasteCategoryId,
                    'is_active'         => $isActive,
                ]);
                return $existing->fresh(['wasteCategory']);
            }

            return MaterialAlias::create([
                'source_type'       => $sourceType,
                'source_id'         => ($sourceType === MaterialAlias::SOURCE_AGGREGATOR) ? $sourceId : null,
                'external_name'     => trim($externalName),
                'normalized_name'   => $normalized,
                'waste_category_id' => $wasteCategoryId,
                'is_active'         => $isActive,
            ])->load('wasteCategory');
        });
    }

    /**
     * Register multiple aliases in a single transaction.
     */
    public function registerAliases(array $mappings, string $sourceType = MaterialAlias::SOURCE_BANK_SAMPAH, ?int $sourceId = null): array
    {
        return DB::transaction(function () use ($mappings, $sourceType, $sourceId) {
            $results = [];
            foreach ($mappings as $externalName => $catId) {
                if (!empty($externalName) && !empty($catId)) {
                    $results[] = $this->registerAlias(
                        $externalName,
                        (int) $catId,
                        $sourceType,
                        $sourceId,
                        true
                    );
                }
            }
            return $results;
        });
    }

    /**
     * Toggle active state of an alias.
     */
    public function toggleAliasStatus(int $aliasId, bool $isActive): MaterialAlias
    {
        $alias = MaterialAlias::findOrFail($aliasId);
        $alias->update(['is_active' => $isActive]);
        return $alias;
    }
}
