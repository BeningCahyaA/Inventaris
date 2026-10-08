<?php

namespace App\Observers;

use App\Models\Asset;

use App\Events\DashboardStatsUpdated;
use App\Services\DashboardService;

class AssetObserver
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Handle the Asset "created" event.
     */
    public function created(Asset $asset): void
    {
        $this->broadcast();
    }

    /**
     * Handle the Asset "updated" event.
     */
    public function updated(Asset $asset): void
    {
        $this->broadcast();
    }

    /**
     * Handle the Asset "deleted" event.
     */
    public function deleted(Asset $asset): void
    {
        $this->broadcast();
    }

    /**
     * Handle the Asset "restored" event.
     */
    public function restored(Asset $asset): void
    {
        $this->broadcast();
    }

    /**
     * Handle the Asset "force deleted" event.
     */
    public function forceDeleted(Asset $asset): void
    {
        $this->broadcast();
    }

    /**
     * Broadcast the updated stats.
     */
    protected function broadcast(): void
    {
        // We use the service to get the latest stats and broadcast them.
        // Even if cached for 2s, it's sufficient for instant UX feel.
        DashboardStatsUpdated::dispatch($this->dashboardService->getStats());
    }
}
