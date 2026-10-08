<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Dikirim ke semua super_admin saat admin mengajukan tiket update/delete
 * untuk aset atau kategori.
 */
class ApprovalSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $entityType,   // 'asset' | 'category'
        public readonly string $action,       // 'update' | 'delete'
        public readonly string $entityName,   // nama aset/kategori
        public readonly string $requesterName,
        public readonly int    $logId,
        public readonly string $logType,      // 'asset' | 'category'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'approval_submitted',
            'entity_type'    => $this->entityType,
            'action'         => $this->action,
            'entity_name'    => $this->entityName,
            'requester_name' => $this->requesterName,
            'log_id'         => $this->logId,
            'log_type'       => $this->logType,
            'link'           => route('approvals.index'),
            'message'        => "{$this->requesterName} mengajukan permintaan " . strtoupper($this->action) . " {$this->entityType}: \"{$this->entityName}\".",
        ];
    }
}
