<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Ringkasan harian lifecycle aset: overdue loan, garansi akan/sudah habis,
 * maintenance terlalu lama. Dikirim ke semua super_admin.
 */
class DailyAssetAlertSummaryNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly int $overdueCount,
        public readonly int $warrantyExpiringCount,
        public readonly int $warrantyExpiredCount,
        public readonly int $maintenanceAgingCount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $parts = [];

        if ($this->overdueCount > 0) {
            $parts[] = "{$this->overdueCount} pinjaman overdue";
        }
        if ($this->warrantyExpiringCount > 0) {
            $parts[] = "{$this->warrantyExpiringCount} garansi segera habis";
        }
        if ($this->warrantyExpiredCount > 0) {
            $parts[] = "{$this->warrantyExpiredCount} garansi sudah habis";
        }
        if ($this->maintenanceAgingCount > 0) {
            $parts[] = "{$this->maintenanceAgingCount} maintenance > 7 hari";
        }

        $summary = empty($parts) ? 'Tidak ada peringatan lifecycle hari ini.' : implode(', ', $parts) . '.';

        return [
            'type'                     => 'daily_alert_summary',
            'overdue_count'            => $this->overdueCount,
            'warranty_expiring_count'  => $this->warrantyExpiringCount,
            'warranty_expired_count'   => $this->warrantyExpiredCount,
            'maintenance_aging_count'  => $this->maintenanceAgingCount,
            'link'                     => route('assets.index'),
            'message'                  => "Ringkasan harian aset: {$summary}",
        ];
    }
}
