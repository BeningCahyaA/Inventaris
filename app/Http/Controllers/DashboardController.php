<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\AssetTransaction;
use App\Models\Category;
use App\Models\CategoryLog;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function __invoke(): View
    {
        $data = $this->dashboardService->getStats();

        // Activity logs — paginated, not cached (page-dependent)
        $assetLogs = AssetLog::with(['user', 'asset'])->latest()->take(20)->get()->map(function ($log) {
            $log->log_type = 'asset';
            $log->ref_name = $log->asset->name ?? 'Aset Dihapus';
            return $log;
        });
        $categoryLogs = CategoryLog::with(['user', 'category'])->latest()->take(20)->get()->map(function ($log) {
            $log->log_type = 'category';
            $log->ref_name = $log->category->name ?? 'Kategori Dihapus';
            return $log;
        });
        $merged = $assetLogs->concat($categoryLogs)->sortByDesc('created_at')->values();

        $perPage = 4;
        $page = (int) request()->get('page', 1);
        $logs = new LengthAwarePaginator(
            $merged->forPage($page, $perPage),
            $merged->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('dashboard', array_merge($data, ['logs' => $logs]));
    }

    /**
     * API endpoint for dashboard polling (now also used as fallback for Echo).
     */
    public function stats(): JsonResponse
    {
        return response()->json($this->dashboardService->getStats());
    }
}
