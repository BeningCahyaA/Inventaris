<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\AssetTransaction;
use App\Models\Category;
use App\Models\CategoryLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get dashboard statistics.
     * Use short cache (2s) to prevent DB thrashing during heavy broadcasting
     * but effectively near real-time.
     */
    public function getStats(): array
    {
        return Cache::remember('dashboard_stats_realtime', 2, function () {
            $now = Carbon::now();

            $stats = Asset::active()->selectRaw("
                COUNT(*) as total_assets,
                COALESCE(SUM(price), 0) as total_value,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as status_available,
                SUM(CASE WHEN status = 'in_use' THEN 1 ELSE 0 END) as status_in_use,
                SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as status_maintenance,
                SUM(CASE WHEN status = 'broken' THEN 1 ELSE 0 END) as status_broken,
                SUM(CASE WHEN `condition` = 'baik' THEN 1 ELSE 0 END) as cond_baik,
                SUM(CASE WHEN `condition` = 'rusak' THEN 1 ELSE 0 END) as cond_rusak,
                SUM(CASE WHEN `condition` = 'rusak_total' THEN 1 ELSE 0 END) as cond_rusak_total
            ")->first();

            $categoryStats = Category::leftJoin('assets', function ($join) { $join->on('categories.slug', '=', 'assets.category')->where('assets.status', '!=', 'sold')->whereNull('assets.deleted_at'); })
                ->select('categories.name', 'categories.slug', DB::raw('COUNT(assets.id) as total'))
                ->groupBy('categories.id', 'categories.name', 'categories.slug')
                ->orderBy('categories.name')
                ->get();

            $pendingCount = AssetLog::where('status', 'pending')->count()
                + CategoryLog::where('status', 'pending')->count();

            // Lifecycle monitoring
            $overdueCount = AssetTransaction::where('status', 'overdue')->count();

            $warrantyExpiringCount = Asset::active()->whereNotNull('warranty_expiry_date')
                ->whereDate('warranty_expiry_date', '>=', $now->toDateString())
                ->whereDate('warranty_expiry_date', '<=', $now->copy()->addDays(30)->toDateString())
                ->count();

            $warrantyExpiredCount = Asset::active()->whereNotNull('warranty_expiry_date')
                ->whereDate('warranty_expiry_date', '<', $now->toDateString())
                ->count();

            $maintenanceAgingCount = AssetLog::where('action', 'maintenance')
                ->where('status', 'approved')
                ->whereHas('asset', fn ($q) => $q->where('status', 'maintenance'))
                ->where('created_at', '<=', $now->copy()->subDays(7))
                ->count();

            // Tren penambahan aset per bulan (12 bulan terakhir)
            $trendRaw = Asset::active()->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
                ->where('created_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month');

            $trendLabels = [];
            $trendData = [];
            for ($i = 11; $i >= 0; $i--) {
                $monthKey = $now->copy()->subMonths($i)->format('Y-m');
                $trendLabels[] = $now->copy()->subMonths($i)->translatedFormat('M Y');
                $trendData[] = $trendRaw[$monthKey] ?? 0;
            }

            return [
                'totalAssets'           => (int) $stats->total_assets,
                'totalValue'            => (int) $stats->total_value,
                'pendingCount'          => $pendingCount,
                'statusAvailable'       => (int) $stats->status_available,
                'statusInUse'           => (int) $stats->status_in_use,
                'statusMaintenance'     => (int) $stats->status_maintenance,
                'statusBroken'          => (int) $stats->status_broken,
                'categoryStats'         => $categoryStats,
                'condBaik'              => (int) $stats->cond_baik,
                'condRusak'              => (int) $stats->cond_rusak,
                'condRusakTotal'        => (int) $stats->cond_rusak_total,
                'overdueCount'          => $overdueCount,
                'warrantyExpiringCount' => $warrantyExpiringCount,
                'warrantyExpiredCount'  => $warrantyExpiredCount,
                'maintenanceAgingCount' => $maintenanceAgingCount,
                'trendLabels'           => $trendLabels,
                'trendData'             => $trendData,
            ];
        });
    }
}
