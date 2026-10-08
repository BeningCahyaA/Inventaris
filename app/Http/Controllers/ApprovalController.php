<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\AssetTransaction;
use App\Models\Category;
use App\Models\CategoryLog;
use App\Models\User;
use App\Notifications\ApprovalResolvedNotification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    /**
     * Tampilkan halaman antrean approval (aset + kategori).
     */
    public function index(Request $request): View
    {
        $status = $request->get('status', 'pending');

        // Asset Logs
        $assetQuery = AssetLog::with(['user', 'asset']);
        if ($status !== 'all') {
            $assetQuery->where('status', $status);
        }
        $pendingAssetLogs = $assetQuery->latest()->get();

        $pendingAssetLogs->transform(function ($log) {
            $log->changed_fields = $this->computeDiff($log);
            $log->log_type = 'asset';
            return $log;
        });

        // Category Logs
        $categoryQuery = CategoryLog::with(['user', 'category']);
        if ($status !== 'all') {
            $categoryQuery->where('status', $status);
        }
        $pendingCategoryLogs = $categoryQuery->latest()->get();

        $pendingCategoryLogs->transform(function ($log) {
            $log->changed_fields = $this->computeDiff($log);
            $log->log_type = 'category';
            return $log;
        });

        // Merge & sort by created_at desc
        $merged = $pendingAssetLogs->concat($pendingCategoryLogs)
            ->sortByDesc('created_at')
            ->values();

        $perPage = 10;
        $page = (int) $request->get('page', 1);
        $pendingLogs = new LengthAwarePaginator(
            $merged->forPage($page, $perPage),
            $merged->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('assets.approvals', compact('pendingLogs', 'status'));
    }

    /**
     * Setujui permintaan perubahan (approve).
     */
    public function approve(Request $request, string $type, int $id): RedirectResponse
    {
        try {
            if ($type === 'category') {
                $this->processApproveCategory($id);
            } else {
                $this->processApproveAsset($id);
            }
            Log::info("Approval: Super Admin (ID: " . Auth::id() . ") approved {$type} request (ID: {$id})");
            return back()->with('success', 'Tiket permintaan berhasil disetujui!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Tolak permintaan perubahan (reject).
     */
    public function reject(Request $request, string $type, int $id): RedirectResponse
    {
        $note = $request->rejection_note ?? 'Ditolak oleh Super Admin tanpa catatan.';
        
        try {
            if ($type === 'category') {
                $this->processRejectCategory($id, $note);
            } else {
                $this->processRejectAsset($id, $note);
            }
            Log::info("Approval: Super Admin (ID: " . Auth::id() . ") rejected {$type} request (ID: {$id})");
            return back()->with('success', 'Tiket permintaan telah ditolak.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Bulk Approve
     */
    public function bulkApprove(Request $request): RedirectResponse
    {
        $selected = $request->input('selected_ids', []);
        if (empty($selected)) {
            return back()->with('error', 'Tidak ada tiket yang dipilih.');
        }

        $count = 0;
        foreach ($selected as $item) {
            [$type, $id] = explode(':', $item);
            try {
                if ($type === 'category') {
                    $this->processApproveCategory((int)$id);
                } else {
                    $this->processApproveAsset((int)$id);
                }
                $count++;
            } catch (\Exception $e) {
                // Skip or log error
            }
        }

        return back()->with('success', "$count tiket berhasil disetujui secara massal!");
    }

    /**
     * Bulk Reject
     */
    public function bulkReject(Request $request): RedirectResponse
    {
        $selected = $request->input('selected_ids', []);
        $note = $request->rejection_note ?? 'Ditolak secara massal oleh Super Admin.';

        if (empty($selected)) {
            return back()->with('error', 'Tidak ada tiket yang dipilih.');
        }

        $count = 0;
        foreach ($selected as $item) {
            [$type, $id] = explode(':', $item);
            try {
                if ($type === 'category') {
                    $this->processRejectCategory((int)$id, $note);
                } else {
                    $this->processRejectAsset((int)$id, $note);
                }
                $count++;
            } catch (\Exception $e) {
                // Skip or log error
            }
        }

        return back()->with('success', "$count tiket telah ditolak secara massal.");
    }

    /**
     * Tampilkan tiket milik user yang sedang login (untuk Admin).
     */
    public function myTickets(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $status = $request->get('status', 'all');

        // Asset tickets
        $assetQuery = AssetLog::with(['asset', 'approver'])
            ->where('user_id', $user->id);
        if ($status !== 'all') {
            $assetQuery->where('status', $status);
        }
        $assetTickets = $assetQuery->latest()->get();

        $assetTickets->transform(function ($log) {
            $log->changed_fields = $this->computeDiff($log);
            $log->log_type = 'asset';
            return $log;
        });

        // Category tickets
        $categoryQuery = CategoryLog::with(['category', 'approver'])
            ->where('user_id', $user->id);
        if ($status !== 'all') {
            $categoryQuery->where('status', $status);
        }
        $categoryTickets = $categoryQuery->latest()->get();

        $categoryTickets->transform(function ($log) {
            $log->changed_fields = $this->computeDiff($log);
            $log->log_type = 'category';
            return $log;
        });

        $merged = $assetTickets->concat($categoryTickets)
            ->sortByDesc('created_at')
            ->values();

        $perPage = 10;
        $page = (int) $request->get('page', 1);
        $tickets = new LengthAwarePaginator(
            $merged->forPage($page, $perPage),
            $merged->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('assets.my_tickets', compact('tickets', 'status'));
    }

    // --- Private helpers ---

    private function processApproveAsset(int $id): void
    {
        $log = AssetLog::findOrFail($id);
        $asset = Asset::find($log->asset_id);

        if (! $asset && $log->action !== 'delete') {
            throw new \Exception('Aset terkait tidak ditemukan.');
        }

        DB::transaction(function () use ($log, $asset) {
            if ($log->action === 'update') {
                $newData = $log->new_data ?? [];
                $oldStatus = $asset->status;

                // Filter field yang bukan kolom tabel assets
                $nonAssetFields = ['borrower_name', 'due_at', 'borrow_notes', 'notes', 'id', 'created_at', 'updated_at', 'delete_attachments'];
                $assetData = array_diff_key($newData, array_flip($nonAssetFields));

                $asset->update($assetData);

                // Check for attachments marked for deletion
                if (!empty($newData['delete_attachments'])) {
                    $attachmentsToDelete = \App\Models\AssetAttachment::whereIn('id', $newData['delete_attachments'])->get();
                    foreach ($attachmentsToDelete as $attachment) {
                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($attachment->file_path)) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($attachment->file_path);
                        }
                        $attachment->delete();
                    }
                }

                // Handle transaksi peminjaman jika status berubah
                $newStatus = $assetData['status'] ?? $oldStatus;
                /** @var \App\Models\User $approver */
                $approver = Auth::user();

                if ($newStatus === 'in_use' && $oldStatus !== 'in_use') {
                    AssetTransaction::create([
                        'asset_id' => $asset->id,
                        'borrower_user_id' => $log->user_id,
                        'borrower_name' => $newData['borrower_name'] ?? $approver->name,
                        'borrowed_at' => now(),
                        'due_at' => $newData['due_at'] ?? null,
                        'notes' => $newData['borrow_notes'] ?? $newData['notes'] ?? null,
                        'created_by' => $log->user_id,
                        'status' => 'borrowed',
                    ]);
                }

                if ($oldStatus === 'in_use' && $newStatus !== 'in_use') {
                    $activeTransaction = $asset->transactions()
                        ->whereNull('returned_at')
                        ->latest('borrowed_at')
                        ->first();

                    if ($activeTransaction) {
                        $returnedAt = now();
                        $durationDays = (int)max(1, Carbon::parse($activeTransaction->borrowed_at)->diffInDays($returnedAt));
                        $activeTransaction->update([
                            'returned_at' => $returnedAt,
                            'duration_days' => $durationDays,
                            'status' => 'returned',
                        ]);
                    }
                }
            } elseif ($log->action === 'delete') {
                $asset?->delete();
            }

            $log->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        });
    }

    private function processApproveCategory(int $id): void
    {
        $log = CategoryLog::findOrFail($id);
        $category = Category::find($log->category_id);

        if (! $category && $log->action !== 'delete') {
            throw new \Exception('Kategori terkait tidak ditemukan.');
        }

        DB::transaction(function () use ($log, $category) {
            if ($log->action === 'update' && $category) {
                $oldSlug = $category->slug;
                $category->update($log->new_data ?? []);

                // Update slug di tabel assets jika berubah
                $newSlug = $log->new_data['slug'] ?? $oldSlug;
                if ($oldSlug !== $newSlug) {
                    Asset::where('category', $oldSlug)->update(['category' => $newSlug]);
                }
            } elseif ($log->action === 'delete' && $category) {
                if ($category->assets()->count() > 0) {
                    throw new \Exception("Tidak bisa menghapus kategori \"{$category->name}\" karena masih digunakan.");
                }
                $category->delete();
            }

            $log->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        });
    }

    private function processRejectAsset(int $id, string $note): void
    {
        $log  = AssetLog::findOrFail($id);

        $log->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_note' => $note,
        ]);

        $requester = User::find($log->user_id);
        $asset     = Asset::find($log->asset_id);
        if ($requester) {
            $requester->notify(new ApprovalResolvedNotification(
                entityType: 'aset',
                action: $log->action,
                entityName: optional($asset)->name ?? 'Aset #' . $log->asset_id,
                resolution: 'rejected',
                rejectionNote: $note,
                approverName: Auth::user()->name,
                linkTarget: route('approvals.my-tickets'),
            ));
        }
    }

    private function processRejectCategory(int $id, string $note): void
    {
        $log      = CategoryLog::findOrFail($id);
        $category = Category::find($log->category_id);

        $log->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_note' => $note,
        ]);

        $requester = User::find($log->user_id);
        if ($requester) {
            $requester->notify(new ApprovalResolvedNotification(
                entityType: 'kategori',
                action: $log->action,
                entityName: optional($category)->name ?? 'Kategori #' . $log->category_id,
                resolution: 'rejected',
                rejectionNote: $note,
                approverName: Auth::user()->name,
                linkTarget: route('approvals.my-tickets'),
            ));
        }
    }

    private function computeDiff(AssetLog|CategoryLog $log): array
    {
        $oldData = $log->old_data ?? [];
        $newData = $log->new_data ?? [];
        $diff = [];

        if ($log->action === 'update') {
            foreach ($newData as $key => $newValue) {
                $oldValue = $oldData[$key] ?? null;
                $comparableOld = is_array($oldValue) ? json_encode($oldValue) : (string) $oldValue;
                $comparableNew = is_array($newValue) ? json_encode($newValue) : (string) $newValue;
                if ($comparableOld !== $comparableNew) {
                    $diff[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                }
            }
        }

        return $diff;
    }
}
