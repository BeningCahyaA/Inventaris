<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Dikirim ke requester (admin) saat tiket di-approve atau di-reject.
 */
class ApprovalResolvedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string  $entityType,    // 'asset' | 'category'
        public readonly string  $action,        // 'update' | 'delete'
        public readonly string  $entityName,
        public readonly string  $resolution,    // 'approved' | 'rejected'
        public readonly ?string $rejectionNote,
        public readonly string  $approverName,
        public readonly string  $linkTarget,    // URL untuk link
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $verb = $this->resolution === 'approved' ? 'disetujui' : 'ditolak';

        $message = "Tiket " . strtoupper($this->action) . " {$this->entityType} \"{$this->entityName}\" {$verb} oleh {$this->approverName}.";

        if ($this->resolution === 'rejected' && $this->rejectionNote) {
            $message .= " Catatan: {$this->rejectionNote}";
        }

        return [
            'type'           => 'approval_resolved',
            'entity_type'    => $this->entityType,
            'action'         => $this->action,
            'entity_name'    => $this->entityName,
            'resolution'     => $this->resolution,
            'rejection_note' => $this->rejectionNote,
            'approver_name'  => $this->approverName,
            'link'           => $this->linkTarget,
            'message'        => $message,
        ];
    }
}
