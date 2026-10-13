<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Http\Requests\BulkAssetUpdateRequest;
use App\Models\Asset;
use App\Models\Category;
use App\Models\AssetAttachment;
use App\Models\AssetLog;
use App\Models\AssetTransaction;
use App\Models\User;
use App\Notifications\ApprovalSubmittedNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(Request $request): View|string
    {
        $query = Asset::active()->with('activeTransaction');

        $this->applyAssetFilters($request, $query);

        $assets = $query->latest()->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('assets.partials.table', compact('assets'))->render();
        }

        $categories = Category::orderBy('name')->get();

        return view('assets.index', compact('assets', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('assets.create', compact('categories'));
    }

    public function archive(Request $request): View
    {
        $query = Asset::where('status', 'sold')->with('sale');
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('asset_tag', 'like', '%'.$search.'%'));
        }
        $assets = $query->latest()->paginate(12)->withQueryString();

        return view('assets.archive', compact('assets'));
    }

    public function store(StoreAssetRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $newTag = DB::transaction(function () use ($request, $validated) {
            $categoryModel = Category::where('slug', $validated['category'])->firstOrFail();
            $prefix = $categoryModel->prefix;
            $year = date('y');

            $lastAsset = Asset::where('asset_tag', 'LIKE', "$prefix-$year-%")
                ->lockForUpdate()
                ->orderBy('id', 'desc')->first();
            $sequence = $lastAsset ? intval(substr($lastAsset->asset_tag, -3)) + 1 : 1;
            $newTag = sprintf("%s-%s-%03d", $prefix, $year, $sequence);

            $asset = Asset::create([
                'name' => $validated['name'],
                'category' => $validated['category'],
                'asset_tag' => $newTag,
                'status' => $validated['status'],
                'purchase_date' => $validated['purchase_date'],
                'warranty_expiry_date' => $validated['warranty_expiry_date'] ?? null,
                'condition' => $validated['condition'],
                'price' => $validated['price'] ?? 0,
                'depreciation_rate' => $validated['depreciation_rate'] ?? 0,
                'person_in_charge' => $validated['person_in_charge'] ?? null,
                'location' => $validated['location'] ?? null,
                'vendor' => $validated['vendor'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'description' => $validated['description'],
            ]);

            $this->storeAttachments($request, $asset, Auth::id());
            if ($asset->status === 'sold') {
                app(\App\Services\AssetSaleService::class)->record($asset, app(\App\Services\AssetSaleService::class)->prepare($request));
            } else {
                app(\App\Services\AssetDepreciationService::class)->sync($asset);
            }

            /** @var \App\Models\User $user */
            $user = Auth::user();

            AssetLog::create([
                'asset_id' => $asset->id,
                'user_id' => $user->id,
                'action' => 'create',
                'new_data' => $asset->toArray(),
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            Log::info("User ID {$user->id} created new asset: {$newTag}");

            return $newTag;
        });

        return redirect()->route($validated['status'] === 'sold' ? 'assets.archive' : 'assets.index')->with('success', 'Aset Baru Berhasil Ditambah: ' . $newTag);
    }

    public function show(int $id): View
    {
        $asset = Asset::with([
            'attachments',
            'transactions' => fn ($q) => $q->latest('borrowed_at')->take(10),
            'logs' => fn ($q) => $q->with('user')->latest()->take(10)
        ])->findOrFail($id);

        app(\App\Services\AssetDepreciationService::class)->sync($asset);
        $asset->load(['sale', 'depreciations' => fn ($q) => $q->orderByDesc('year_number')]);
        $depreciatedValue = max(0, round((float) $asset->price - (float) $asset->depreciations->sum('amount'), 2));

        return view('assets.show', compact('asset', 'depreciatedValue'));
    }

    public function edit(int $id): View
    {
        $asset = Asset::with(['attachments', 'activeTransaction'])->findOrFail($id);
        abort_if($asset->status === 'sold', 422, 'Aset dijual tersimpan di arsip dan tidak dapat diubah.');
        $categories = Category::orderBy('name')->get();

        return view('assets.edit', compact('asset', 'categories'));
    }

    public function update(UpdateAssetRequest $request, int $id): RedirectResponse
    {
        $validated = $request->validated();
        $asset = Asset::findOrFail($id);

        abort_if($asset->status === 'sold', 422, 'Aset dijual tersimpan di arsip dan tidak dapat diubah.');
        app(\App\Services\AssetDepreciationService::class)->sync($asset, $validated['status'] === 'sold' ? Carbon::parse($validated['sale_date']) : null);
        $validated['depreciation_rate'] = $validated['depreciation_rate'] ?? $asset->depreciation_rate ?? 0;

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $newData = [
            'name' => $validated['name'],
            'category' => $validated['category'],
            'status' => $validated['status'],
            'description' => $validated['description'],
            'purchase_date' => $validated['purchase_date'],
            'warranty_expiry_date' => $validated['warranty_expiry_date'] ?? null,
            'condition' => $validated['condition'],
            'price' => $validated['price'] ?? 0,
            'depreciation_rate' => $validated['depreciation_rate'],
            'person_in_charge' => $validated['person_in_charge'] ?? null,
            'location' => $validated['location'] ?? null,
            'vendor' => $validated['vendor'] ?? null,
            'serial_number' => $validated['serial_number'] ?? null,
        ];

        // Simpan data peminjaman terpisah (tidak masuk ke tabel assets)
        $borrowerData = [
            'borrower_name' => $validated['borrower_name'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'borrow_notes' => $validated['borrow_notes'] ?? null,
        ];

        if ($user->role === 'super_admin') {
            DB::transaction(function () use ($request, $asset, $newData, $borrowerData, $validated, $user) {
                $oldData = $asset->toArray();
                $oldStatus = $asset->status;
                $asset->update($validated);
                if ($asset->status === 'sold') {
                    app(\App\Services\AssetSaleService::class)->record($asset, app(\App\Services\AssetSaleService::class)->prepare($request));
                }
            Log::info("Super Admin (ID: {$user->id}) updated asset: {$asset->asset_tag}");

                // Handle transaksi peminjaman/pengembalian
                $this->handleTransaction($asset, $oldStatus, $validated['status'], $user, $borrowerData);

                AssetLog::create([
                    'asset_id' => $asset->id,
                    'user_id' => $user->id,
                    'action' => 'update',
                    'old_data' => $oldData,
                    'new_data' => array_merge($asset->toArray(), $borrowerData),
                    'status' => 'approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);

                $this->storeAttachments($request, $asset, $user->id);

                if ($request->filled('delete_attachments')) {
                    $attachmentsToDelete = $asset->attachments()->whereIn('id', $request->delete_attachments)->get();
                    foreach ($attachmentsToDelete as $attachment) {
                        if (Storage::disk('public')->exists($attachment->file_path)) {
                            abort_unless(Storage::disk('public')->delete($attachment->file_path), 500, 'File lampiran gagal dihapus.');
                        }
                        $attachment->delete();
                    }
                }
            });

            return redirect()->route($asset->status === 'sold' ? 'assets.archive' : 'assets.index')->with('success', 'Data Aset Berhasil Diperbarui!');
        }

        // Admin: Cek apakah sudah ada tiket pending untuk aset ini
        $existingPending = AssetLog::where('asset_id', $asset->id)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return redirect()->route('assets.index')->with('error', 'Sudah ada tiket pending untuk aset ini. Tunggu tiket sebelumnya diproses.');
        }

        // Admin: Upload lampiran langsung (additive), tapi delete menunggu approval
        if ($validated['status'] === 'sold') {
            $newData['sale_data'] = app(\App\Services\AssetSaleService::class)->prepare($request);
        }
        $this->storeAttachments($request, $asset, $user->id);

        if ($request->filled('delete_attachments')) {
            $newData['delete_attachments'] = $request->delete_attachments;
        }

        // Admin: Masuk antrean log (data asli tidak berubah dulu)
        $log = AssetLog::create([
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'action' => 'update',
            'old_data' => $asset->toArray(),
            'new_data' => array_merge($newData, $borrowerData),
            'status' => 'pending',
        ]);

        // Kirim notifikasi ke semua super_admin
        User::where('role', 'super_admin')->each(function (User $sa) use ($asset, $user, $log) {
            $sa->notify(new ApprovalSubmittedNotification(
                entityType: 'aset',
                action: 'update',
                entityName: $asset->name,
                requesterName: $user->name,
                logId: $log->id,
                logType: 'asset',
            ));
        });

        Log::info("Admin (ID: {$user->id}) requested update for asset: {$asset->asset_tag}");
        return redirect()->route('assets.index')->with('success', 'Permintaan update aset telah dikirim dan menunggu persetujuan Super Admin.');
    }

    /**
     * Handle transaksi peminjaman/pengembalian berdasarkan perubahan status.
     */
    private function handleTransaction(Asset $asset, string $oldStatus, string $newStatus, User $user, array $borrowerData): void
    {
        // Status berubah ke in_use → buat transaksi peminjaman baru
        if ($newStatus === 'in_use' && $oldStatus !== 'in_use') {
            AssetTransaction::create([
                'asset_id' => $asset->id,
                'borrower_user_id' => $user->id,
                'borrower_name' => $borrowerData['borrower_name'] ?: $user->name,
                'borrowed_at' => now(),
                'due_at' => $borrowerData['due_at'] ?? null,
                'notes' => $borrowerData['borrow_notes'] ?? null,
                'created_by' => $user->id,
                'status' => 'borrowed',
            ]);
        }

        // Status berubah dari in_use ke status lain → tutup transaksi aktif
        if ($oldStatus === 'in_use' && $newStatus !== 'in_use') {
            $activeTransaction = $asset->transactions()
                ->whereNull('returned_at')
                ->latest('borrowed_at')
                ->first();

            if ($activeTransaction) {
                $returnedAt = now();
                $durationDays = max(1, Carbon::parse($activeTransaction->borrowed_at)->diffInDays($returnedAt));

                $activeTransaction->update([
                    'returned_at' => $returnedAt,
                    'duration_days' => $durationDays,
                    'status' => 'returned',
                    'notes' => $borrowerData['borrow_notes'] ?: $activeTransaction->notes,
                ]);
            }
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        $asset = Asset::findOrFail($id);
        abort_if($asset->status === 'sold', 422, 'Riwayat aset dijual disimpan di arsip.');

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role === 'super_admin') {
            DB::transaction(function () use ($asset, $user) {
                AssetLog::create([
                    'asset_id' => $asset->id,
                    'user_id' => $user->id,
                    'action' => 'delete',
                    'old_data' => $asset->toArray(),
                    'status' => 'approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);

                $asset->delete();
                Log::info("Super Admin (ID: {$user->id}) deleted asset: {$asset->asset_tag}");
            });

            return redirect()->route('assets.index')->with('success', 'Aset Berhasil Dihapus.');
        }

        // Admin: Cek tiket pending
        $existingPending = AssetLog::where('asset_id', $asset->id)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return redirect()->route('assets.index')->with('error', 'Sudah ada tiket pending untuk aset ini. Tunggu tiket sebelumnya diproses.');
        }

        // Admin: Request hapus
        $log = AssetLog::create([
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'action' => 'delete',
            'old_data' => $asset->toArray(),
            'status' => 'pending',
        ]);

        // Kirim notifikasi ke semua super_admin
        User::where('role', 'super_admin')->each(function (User $sa) use ($asset, $user, $log) {
            $sa->notify(new ApprovalSubmittedNotification(
                entityType: 'aset',
                action: 'delete',
                entityName: $asset->name,
                requesterName: $user->name,
                logId: $log->id,
                logType: 'asset',
            ));
        });

        Log::info("Admin (ID: {$user->id}) requested delete for asset: {$asset->asset_tag}");
        return redirect()->route('assets.index')->with('success', 'Permintaan hapus aset dikirim ke Super Admin.');
    }


    /**
     * Bulk update: status, category, person_in_charge, location.
     * - super_admin → eksekusi langsung dalam satu DB transaction
     * - admin       → buat pending log per aset (approval flow)
     */
    public function bulkUpdate(BulkAssetUpdateRequest $request): RedirectResponse
    {
        $assetIds = $request->input('asset_ids');
        $changes  = $request->onlyFilled();

        if (empty($changes)) {
            return back()->with('error', 'Tidak ada perubahan yang dipilih untuk bulk update.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        DB::transaction(function () use ($assetIds, $changes, $user): void {
            $assets = Asset::whereIn('id', $assetIds)->lockForUpdate()->get();
            abort_if($assets->contains(fn ($asset) => $asset->status === 'sold'), 422, 'Aset dijual tidak dapat diubah melalui tindakan massal.');

            foreach ($assets as $asset) {
                if ($user->role === 'super_admin') {
                    $oldData = $asset->toArray();
                    $asset->update($changes);

                    AssetLog::create([
                        'asset_id'    => $asset->id,
                        'user_id'     => $user->id,
                        'action'      => 'update',
                        'old_data'    => $oldData,
                        'new_data'    => $asset->fresh()->toArray(),
                        'status'      => 'approved',
                        'approved_by' => $user->id,
                        'approved_at' => now(),
                    ]);
                    Log::info("Super Admin (ID: {$user->id}) bulk updated asset: {$asset->asset_tag}");
                } else {
                    // Admin: pending log per aset
                    $log = AssetLog::create([
                        'asset_id' => $asset->id,
                        'user_id'  => $user->id,
                        'action'   => 'update',
                        'old_data' => $asset->toArray(),
                        'new_data' => array_merge($asset->toArray(), $changes),
                        'status'   => 'pending',
                    ]);

                    // Notify super_admin
                    User::where('role', 'super_admin')->each(function (User $sa) use ($asset, $user, $log): void {
                        $sa->notify(new ApprovalSubmittedNotification(
                            entityType: 'aset',
                            action: 'update',
                            entityName: $asset->name,
                            requesterName: $user->name,
                            logId: $log->id,
                            logType: 'asset',
                        ));
                    });
                }
            }
        });

        $count = count($assetIds);

        if ($user->role === 'super_admin') {
            Log::info("Super Admin (ID: {$user->id}) bulk updated " . count($assetIds) . " assets");
            return back()->with('success', 'Aset yang dipilih berhasil diperbarui!');
        }

        return redirect()->route('assets.index')
            ->with('success', "Permintaan bulk update {$count} aset telah dikirim ke Super Admin.");
    }

    /**
     * Bulk delete request.
     * - super_admin → hapus langsung dalam satu DB transaction
     * - admin       → buat pending log delete per aset
     */
    public function bulkDeleteRequest(Request $request): RedirectResponse
    {
        $request->validate([
            'asset_ids'   => ['required', 'array', 'min:1'],
            'asset_ids.*' => ['integer', 'exists:assets,id'],
        ]);

        $assetIds = $request->input('asset_ids');

        /** @var \App\Models\User $user */
        $user = Auth::user();

        DB::transaction(function () use ($assetIds, $user): void {
            $assets = Asset::whereIn('id', $assetIds)->lockForUpdate()->get();
            abort_if($assets->contains(fn ($asset) => $asset->status === 'sold'), 422, 'Riwayat aset dijual disimpan di arsip.');

            foreach ($assets as $asset) {
                if ($user->role === 'super_admin') {
                    AssetLog::create([
                        'asset_id'    => $asset->id,
                        'user_id'     => $user->id,
                        'action'      => 'delete',
                        'old_data'    => $asset->toArray(),
                        'status'      => 'approved',
                        'approved_by' => $user->id,
                        'approved_at' => now(),
                    ]);
                    $asset->delete();
                } else {
                    $log = AssetLog::create([
                        'asset_id' => $asset->id,
                        'user_id'  => $user->id,
                        'action'   => 'delete',
                        'old_data' => $asset->toArray(),
                        'status'   => 'pending',
                    ]);

                    User::where('role', 'super_admin')->each(function (User $sa) use ($asset, $user, $log): void {
                        $sa->notify(new ApprovalSubmittedNotification(
                            entityType: 'aset',
                            action: 'delete',
                            entityName: $asset->name,
                            requesterName: $user->name,
                            logId: $log->id,
                            logType: 'asset',
                        ));
                    });
                }
            }
        });

        $count = count($assetIds);

        if ($user->role === 'super_admin') {
            return redirect()->route('assets.index')
                ->with('success', "{$count} aset berhasil dihapus.");
        }

        return redirect()->route('assets.index')
            ->with('success', "Permintaan hapus {$count} aset telah dikirim ke Super Admin.");
    }

    /**
     * API: Mengambil semua ID aset tanpa pagination.
     */
    public function getAllAssetIds(Request $request): JsonResponse
    {
        $query = Asset::query();

        $this->applyAssetFilters($request, $query);

        $assetIds = $query->pluck('id')->toArray();

        return response()->json([
            'success' => true,
            'total' => count($assetIds),
            'ids' => $assetIds,
        ]);
    }

    private function applyAssetFilters(Request $request, $query): void
    {
        $query->where('status', '!=', 'sold');
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('asset_tag', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere('serial_number', 'like', '%' . $search . '%')
                    ->orWhere('vendor', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->date_to);
        }

        // Lifecycle: warranty state
        if ($request->filled('warranty_state')) {
            $today = now()->toDateString();
            $in30  = now()->addDays(30)->toDateString();

            if ($request->warranty_state === 'expiring') {
                $query->whereNotNull('warranty_expiry_date')
                      ->whereDate('warranty_expiry_date', '>=', $today)
                      ->whereDate('warranty_expiry_date', '<=', $in30);
            } elseif ($request->warranty_state === 'expired') {
                $query->whereNotNull('warranty_expiry_date')
                      ->whereDate('warranty_expiry_date', '<', $today);
            }
        }

        // Lifecycle: loan state
        if ($request->filled('loan_state')) {
            if ($request->loan_state === 'overdue') {
                $query->whereHas('transactions', fn ($q) => $q->where('status', 'overdue'));
            } elseif ($request->loan_state === 'borrowed') {
                $query->whereHas('activeTransaction');
            } elseif ($request->loan_state === 'returned') {
                $query->whereHas('transactions', fn ($q) => $q->where('status', 'returned'));
            }
        }
    }

    private function storeAttachments(Request $request, Asset $asset, ?int $uploadedBy = null): void
    {
        if (! $request->hasFile('attachments')) {
            return;
        }

        foreach ($request->file('attachments') as $file) {
            if (! $file) {
                continue;
            }

            $path = $file->store('asset-attachments', 'public');
            abort_unless($path !== false, 500, 'Lampiran gagal disimpan.');

            AssetAttachment::create([
                'asset_id' => $asset->id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => $uploadedBy,
            ]);
        }
    }
}
