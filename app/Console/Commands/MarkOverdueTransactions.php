<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\AssetTransaction;
use App\Models\User;
use App\Notifications\DailyAssetAlertSummaryNotification;
use Illuminate\Console\Command;

class MarkOverdueTransactions extends Command
{
    protected $signature = 'assets:mark-overdue';

    protected $description = 'Tandai transaksi peminjaman overdue dan kirim ringkasan harian ke super_admin';

    public function handle(): int
    {
        // 1. Mark overdue transactions
        $overdueCount = AssetTransaction::where('status', 'borrowed')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->update(['status' => 'overdue']);

        if ($overdueCount > 0) {
            $this->info("{$overdueCount} transaksi ditandai sebagai OVERDUE.");
        } else {
            $this->info('Tidak ada transaksi overdue baru.');
        }

        // 2. Lifecycle counts for summary
        $today = now()->toDateString();
        $in30  = now()->addDays(30)->toDateString();

        $totalOverdue = AssetTransaction::where('status', 'overdue')->count();

        $warrantyExpiring = Asset::whereNotNull('warranty_expiry_date')
            ->whereDate('warranty_expiry_date', '>=', $today)
            ->whereDate('warranty_expiry_date', '<=', $in30)
            ->count();

        $warrantyExpired = Asset::whereNotNull('warranty_expiry_date')
            ->whereDate('warranty_expiry_date', '<', $today)
            ->count();

        $maintenanceAging = \App\Models\AssetLog::where('action', 'maintenance')
            ->where('status', 'approved')
            ->whereHas('asset', fn ($q) => $q->where('status', 'maintenance'))
            ->where('created_at', '<=', now()->subDays(7))
            ->count();

        // 3. Send daily summary to all super_admins
        User::where('role', 'super_admin')->each(function (User $sa) use (
            $totalOverdue,
            $warrantyExpiring,
            $warrantyExpired,
            $maintenanceAging,
        ): void {
            $sa->notify(new DailyAssetAlertSummaryNotification(
                overdueCount: $totalOverdue,
                warrantyExpiringCount: $warrantyExpiring,
                warrantyExpiredCount: $warrantyExpired,
                maintenanceAgingCount: $maintenanceAging,
            ));
        });

        $this->info('Ringkasan harian lifecycle dikirim ke super_admin.');

        return self::SUCCESS;
    }
}
