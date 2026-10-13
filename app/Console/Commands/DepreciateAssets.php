<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Services\AssetDepreciationService;
use Illuminate\Console\Command;

class DepreciateAssets extends Command
{
    protected $signature = 'assets:depreciate';
    protected $description = 'Catat penyusutan tahunan aset yang sudah jatuh tempo';

    public function handle(AssetDepreciationService $service): int
    {
        Asset::active()->where(fn ($q) => $q->where('depreciation_rate', '>', 0)->orWhereHas('depreciations'))
            ->chunkById(100, function ($assets) use ($service) {
                foreach ($assets as $asset) $service->sync($asset);
            });
        $this->info('Riwayat penyusutan aset berhasil diperbarui.');

        return self::SUCCESS;
    }
}
