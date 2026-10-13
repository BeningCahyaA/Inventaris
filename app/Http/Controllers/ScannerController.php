<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ScannerStatusRequest;
use App\Http\Requests\ScannerQuickEditRequest;
use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\User;
use App\Notifications\ApprovalSubmittedNotification;
use App\Services\AssetStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ScannerController extends Controller
{
    public function __construct(
        private readonly AssetStatusService $statusService,
    ) {}

    /**
     * Halaman scanner berbasis kamera.
     */
    public function index(): View
    {
        return view('scanner.index');
    }

    /**
     * Resolve QR tag → kembalikan ringkasan aset + aksi yang valid.
     */
    public function resolve(Request $request): JsonResponse
    {
        $request->validate([
            'asset_tag' => ['required', 'string'],
        ]);

        $asset = Asset::with('activeTransaction')
            ->where('asset_tag', $request->asset_tag)
            ->first();

        if (! $asset) {
            return response()->json([
                'found'    => false,
                'message'  => 'Aset dengan tag "' . $request->asset_tag . '" tidak ditemukan.',
            ], 404);
        }

        // Tentukan aksi yang valid berdasarkan status aset saat ini
        $validActions = match ($asset->status) {
            'available'   => ['check_out', 'maintenance'],
            'in_use'      => ['check_in'],
            'maintenance' => ['check_in'],
            default       => [],
        };

        return response()->json([
            'found'        => true,
            'asset'        => [
                'id'               => $asset->id,
                'asset_tag'        => $asset->asset_tag,
                'name'             => $asset->name,
                'status'           => $asset->status,
                'category'         => $asset->category,
                'location'         => $asset->location,
                'condition'        => $asset->condition,
                'person_in_charge' => $asset->person_in_charge,
                'description'      => $asset->description,
                'borrower'         => optional($asset->activeTransaction)->borrower_name,
                'due_at'           => optional($asset->activeTransaction)?->due_at?->format('d/m/Y'),
            ],
            'valid_actions' => $validActions,
        ]);
    }

    /**
     * Eksekusi aksi status dari scanner.
     */
    public function updateStatus(ScannerStatusRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $asset = Asset::where('asset_tag', $validated['asset_tag'])->firstOrFail();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $message = $this->statusService->processStatusUpdate(
                asset: $asset,
                action: $validated['action'],
                user: $user,
                borrowerName: $validated['borrower_name'] ?? null,
                dueAt: $validated['due_at'] ?? null,
                notes: $validated['notes'] ?? null,
            );

            Log::info("Scanner: User (ID: {$user->id}) updated status for asset: {$asset->asset_tag} to {$validated['action']}");

            return response()->json([
                'success'    => true,
                'message'    => $message,
                'new_status' => $asset->fresh()->status,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Quick edit informasi aset dari scanner.
     * super_admin → langsung update; admin → pending log + notifikasi.
     */
    public function quickEdit(ScannerQuickEditRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $asset = Asset::findOrFail($validated['asset_id']);
        abort_if($asset->status === 'sold', 422, 'Aset sudah dijual dan tersimpan di arsip.');

        /** @var User $user */
        $user = Auth::user();

        $changes = array_filter(
            $request->only(['name', 'location', 'person_in_charge', 'category', 'condition', 'description']),
            fn ($v) => $v !== null && $v !== '',
        );

        if (empty($changes)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada perubahan yang dikirim.'], 422);
        }

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

            Log::info("Scanner Quick Edit: Super Admin (ID: {$user->id}) updated asset: {$asset->asset_tag}");

            return response()->json([
                'success' => true,
                'message' => 'Aset berhasil diperbarui.',
                'asset'   => $asset->fresh()->only(['name', 'location', 'person_in_charge', 'category', 'condition', 'description']),
            ]);
        }

        // Admin → pending log + notifikasi ke super_admin
        DB::transaction(function () use ($asset, $user, $changes) {
            $log = AssetLog::create([
                'asset_id' => $asset->id,
                'user_id'  => $user->id,
                'action'   => 'update',
                'old_data' => $asset->toArray(),
                'new_data' => $changes,
                'status'   => 'pending',
            ]);

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

            Log::info("Scanner Quick Edit: Admin (ID: {$user->id}) requested update for asset: {$asset->asset_tag}");
        });

        return response()->json([
            'success' => true,
            'message' => 'Permintaan edit dikirim, menunggu persetujuan Super Admin.',
        ]);
    }
}
