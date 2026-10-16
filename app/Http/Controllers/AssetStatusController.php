<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStatusRequest;
use App\Models\Asset;
use App\Models\AssetTransaction;
use App\Services\AssetStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AssetStatusController extends Controller
{
    public function __construct(
        private readonly AssetStatusService $statusService,
    ) {}

    /**
     * Update status cepat dari halaman index (web).
     */
    public function updateFromWeb(UpdateStatusRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $asset = Asset::where('asset_tag', $validated['asset_tag'])->firstOrFail();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $message = $this->statusService->processStatusUpdate(
            asset: $asset,
            action: $validated['action'],
            user: $user,
            borrowerName: $validated['borrower_name'] ?? null,
            dueAt: $validated['due_at'] ?? null,
            notes: $validated['notes'] ?? null,
        );

        return redirect()->route('assets.index')->with('success', $message);
    }

    /**
     * Halaman riwayat transaksi aset.
     */
    public function history(Request $request): View
    {
        $activeTab = $request->query('tab') === 'activity' ? 'activity' : 'history';
        if ($activeTab === 'activity') {
            $assetLogs = \Illuminate\Support\Facades\DB::table('asset_logs')
                ->leftJoin('assets', 'assets.id', '=', 'asset_logs.asset_id')
                ->leftJoin('users', 'users.id', '=', 'asset_logs.user_id')
                ->select('asset_logs.id', 'asset_logs.action', 'asset_logs.status', 'asset_logs.created_at', 'users.name as actor', 'assets.name as reference', 'assets.asset_tag as tag')
                ->selectRaw("'Aset' as type");
            $categoryLogs = \Illuminate\Support\Facades\DB::table('category_logs')
                ->leftJoin('categories', 'categories.id', '=', 'category_logs.category_id')
                ->leftJoin('users', 'users.id', '=', 'category_logs.user_id')
                ->select('category_logs.id', 'category_logs.action', 'category_logs.status', 'category_logs.created_at', 'users.name as actor', 'categories.name as reference', 'categories.slug as tag')
                ->selectRaw("'Kategori' as type");
            $logs = \Illuminate\Support\Facades\DB::query()->fromSub($assetLogs->unionAll($categoryLogs), 'activity')
                ->when($request->filled('search'), function ($query) use ($request) {
                    $query->where(function ($query) use ($request) {
                        foreach (['actor', 'reference', 'tag'] as $column) {
                            $query->orWhere($column, 'like', '%'.$request->input('search').'%');
                        }
                    });
                })
                ->orderByDesc('created_at')->orderByDesc('id')->orderBy('type')
                ->paginate(10)->withQueryString();

            return view('assets.history', compact('activeTab', 'logs'));
        }

        $transactions = AssetTransaction::with(['asset', 'borrower'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('loan_state'), function ($query) use ($request) {
                // loan_state alias maps to the status column
                $map = ['overdue' => 'overdue', 'borrowed' => 'borrowed', 'returned' => 'returned'];
                $status = $map[$request->loan_state] ?? null;
                if ($status) {
                    $query->where('status', $status);
                }
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('borrowed_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('borrowed_at', '<=', $request->date_to);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('borrower_name', 'like', '%' . $search . '%')
                        ->orWhereHas('asset', function ($assetQuery) use ($search) {
                            $assetQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('asset_tag', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest('borrowed_at')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('assets.partials.history_table', compact('transactions'));
        }

        return view('assets.history', compact('transactions', 'activeTab'));
    }
}
