<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Asset;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class AssetDepreciationService
{
    public function sync(Asset $asset, ?CarbonInterface $asOf = null): void
    {
        $asOf = ($asOf ?? now('Asia/Jakarta'))->copy()->startOfDay();

        DB::transaction(function () use ($asset, $asOf) {
            $current = Asset::whereKey($asset->id)->lockForUpdate()->firstOrFail();
            $sale = $current->sale;
            if ($sale && $sale->sold_on->toDateString() < $asOf->toDateString()) {
                $asOf = $sale->sold_on;
            }
            if (! $current->purchase_date || $current->purchase_date->toDateString() > $asOf->toDateString()) return;

            $last = $current->depreciations()->orderByDesc('year_number')->first();
            if (! $last && (float) $current->depreciation_rate <= 0) return;

            $opening = max(0, round((float) $current->price - (float) $current->depreciations()->sum('amount'), 2));
            $annual = round((float) $current->price * (float) $current->depreciation_rate / 100, 2);
            $year = ($last?->year_number ?? 0) + 1;
            while ($opening > 0) {
                $date = $current->purchase_date->copy()->addYearsNoOverflow($year);
                if ($date->toDateString() > $asOf->toDateString()) break;

                $amount = min($opening, $annual);
                $closing = max(0, round($opening - $amount, 2));
                $current->depreciations()->create([
                    'year_number' => $year,
                    'depreciated_on' => $date,
                    'original_value' => $current->price,
                    'rate' => $current->depreciation_rate,
                    'opening_value' => $opening,
                    'amount' => $amount,
                    'closing_value' => $closing,
                ]);
                $opening = $closing;
                $year++;
            }
        });
    }
}
