<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\CategoryLog;
use App\Observers\AssetObserver;
use App\Observers\LogObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Asset::observe(AssetObserver::class);
        AssetLog::observe(LogObserver::class);
        CategoryLog::observe(LogObserver::class);
    }
}
