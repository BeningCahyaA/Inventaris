<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\User;
use App\Notifications\ApprovalSubmittedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetAttachmentController extends Controller
{
    public function show(int $id, int $attachmentId): StreamedResponse
    {
        $attachment = Asset::findOrFail($id)->attachments()->findOrFail($attachmentId);
        abort_unless(Storage::disk('public')->exists($attachment->file_path), 404);

        return Storage::disk('public')->response($attachment->file_path, $attachment->file_name, [
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    public function destroy(int $id, int $attachmentId): RedirectResponse
    {
        $asset = Asset::findOrFail($id);
        $attachment = $asset->attachments()->findOrFail($attachmentId);
        $user = Auth::user();
        $immediate = $user->role === 'super_admin';

        if (! $immediate && $asset->logs()->where('status', 'pending')->exists()) {
            return back()->with('error', 'Sudah ada tiket pending untuk aset ini. Tunggu tiket sebelumnya diproses.');
        }

        $log = DB::transaction(function () use ($asset, $attachment, $user, $immediate) {
            if ($immediate) {
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    abort_unless(Storage::disk('public')->delete($attachment->file_path), 500, 'File gagal dihapus.');
                }
                $attachment->delete();
            }

            return AssetLog::create([
                'asset_id' => $asset->id,
                'user_id' => $user->id,
                'action' => 'update',
                'old_data' => ['attachment_name' => $attachment->file_name],
                'new_data' => ['delete_attachments' => [$attachment->id]],
                'status' => $immediate ? 'approved' : 'pending',
                'approved_by' => $immediate ? $user->id : null,
                'approved_at' => $immediate ? now() : null,
            ]);
        });

        if (! $immediate) {
            User::where('role', 'super_admin')->each(function (User $sa) use ($asset, $user, $log) {
                $sa->notify(new ApprovalSubmittedNotification(
                    entityType: 'aset', action: 'update', entityName: $asset->name,
                    requesterName: $user->name, logId: $log->id, logType: 'asset',
                ));
            });
        }

        return back()->with('success', $immediate
            ? 'Lampiran berhasil dihapus.'
            : 'Permintaan hapus lampiran dikirim untuk persetujuan Super Admin.');
    }

    public function saleProof(int $id): StreamedResponse
    {
        $sale = Asset::findOrFail($id)->sale()->firstOrFail();
        abort_unless(Storage::disk('local')->exists($sale->proof_path), 404);

        return Storage::disk('local')->response($sale->proof_path, $sale->proof_name, [
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    public function saleRequestProof(int $logId): StreamedResponse
    {
        $log = AssetLog::findOrFail($logId);
        abort_unless(Auth::user()->role === 'super_admin' || $log->user_id === Auth::id(), 403);
        $sale = $log->new_data['sale_data'] ?? [];
        abort_unless(!empty($sale['proof_path']) && Storage::disk('local')->exists($sale['proof_path']), 404);

        return Storage::disk('local')->response($sale['proof_path'], $sale['proof_name'], [
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }
}
