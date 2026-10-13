<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\AssetTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class AssetStatusService
{
    /**
     * Proses perubahan status aset (check-in, check-out, maintenance).
     * Digunakan oleh AssetStatusController (web) dan ScannerController (API).
     *
     * @return string Pesan sukses
     */
    public function processStatusUpdate(
        Asset $asset,
        string $action,
        User $user,
        ?string $borrowerName = null,
        ?string $dueAt = null,
        ?string $notes = null,
    ): string {
        $oldData = $asset->toArray();

        [$newStatus, $message] = match ($action) {
            'check_out' => ['in_use', 'Aset berhasil dipinjam/digunakan.'],
            'check_in' => ['available', 'Aset berhasil dikembalikan.'],
            'maintenance' => ['maintenance', 'Aset masuk maintenance.'],
        };

        DB::transaction(function () use ($asset, $newStatus, $action, $oldData, $user, $borrowerName, $dueAt, $notes): void {
            $asset->refresh();
            abort_if($asset->status === 'sold', 422, 'Aset sudah dijual dan tersimpan di arsip.');
            $asset->update(['status' => $newStatus]);

            if ($action === 'check_out') {
                $this->createBorrowTransaction($asset, $user, $borrowerName, $dueAt, $notes);
            }

            if ($action === 'check_in') {
                $this->closeActiveTransaction($asset, $notes);
            }

            $this->recordLog($asset, $user, $action, $oldData, $borrowerName, $dueAt, $notes);

            Log::info("Status Change: User (ID: {$user->id}) performed {$action} on asset: {$asset->asset_tag}");
        });

        return $message;
    }

    private function createBorrowTransaction(
        Asset $asset,
        User $user,
        ?string $borrowerName,
        ?string $dueAt,
        ?string $notes,
    ): void {
        AssetTransaction::create([
            'asset_id' => $asset->id,
            'borrower_user_id' => $user->id,
            'borrower_name' => $borrowerName ?: $user->name,
            'borrowed_at' => now(),
            'due_at' => $dueAt,
            'notes' => $notes,
            'created_by' => $user->id,
            'status' => 'borrowed',
        ]);
    }

    private function closeActiveTransaction(Asset $asset, ?string $notes): void
    {
        $activeTransaction = $asset->transactions()
            ->whereNull('returned_at')
            ->latest('borrowed_at')
            ->first();

        if (! $activeTransaction) {
            return;
        }

        $returnedAt = now();
        $durationDays = max(1, Carbon::parse($activeTransaction->borrowed_at)->diffInDays($returnedAt));

        $activeTransaction->update([
            'returned_at' => $returnedAt,
            'duration_days' => $durationDays,
            'status' => 'returned',
            'notes' => $notes ?: $activeTransaction->notes,
        ]);
    }

    private function recordLog(
        Asset $asset,
        User $user,
        string $action,
        array $oldData,
        ?string $borrowerName,
        ?string $dueAt,
        ?string $notes,
    ): void {
        AssetLog::create([
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'action' => $action,
            'old_data' => $oldData,
            'new_data' => array_merge($asset->fresh()->toArray(), [
                'borrower_name' => $borrowerName,
                'due_at' => $dueAt,
                'notes' => $notes,
            ]),
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
    }
}
