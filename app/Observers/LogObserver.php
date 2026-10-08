<?php

namespace App\Observers;

use App\Events\DashboardStatsUpdated;
use App\Models\AssetLog;
use App\Models\CategoryLog;
use App\Services\DashboardService;

class LogObserver
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function created($log): void
    {
        $this->broadcast();
    }

    public function updated($log): void
    {
        $this->broadcast();
    }

    public function deleted($log): void
    {
        $this->broadcast();
    }

    protected function broadcast(): void
    {
        DashboardStatsUpdated::dispatch($this->dashboardService->getStats());
    }
}
