<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Halaman daftar notifikasi.
     */
    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $tab = $request->get('tab', 'unread');
        $unreadCount = $user->unreadNotifications()->count();

        $query = $user->notifications()->latest();
        if ($tab === 'unread') {
            $query->whereNull('read_at');
        } elseif ($tab === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->paginate(10)->withQueryString();

        return view('notifications.index', compact('notifications', 'tab', 'unreadCount'));
    }

    /**
     * Mark single notification as read.
     */
    public function markRead(string $notificationId): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $notification = $user->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        // Redirect to the notification's link if available
        $data = $notification->data;
        $link = $data['link'] ?? route('notifications.index');

        return redirect($link);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi telah ditandai sudah dibaca.');
    }
    /**
     * Delete a single notification.
     */
    public function destroy(string $id): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->notifications()->findOrFail($id)->delete();

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }

    /**
     * Delete all notifications.
     */
    public function deleteAll(): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->notifications()->delete();

        return back()->with('success', 'Semua notifikasi berhasil dihapus.');
    }
    /**
     * API: unread count (untuk badge polling).
     */
    public function unreadCount(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $response = [
            'count' => $user->unreadNotifications()->count(),
        ];

        if ($user->role === 'super_admin') {
            $response['approvals_count'] = \App\Models\AssetLog::where('status', 'pending')->count()
                + \App\Models\CategoryLog::where('status', 'pending')->count();
        } elseif ($user->role === 'admin') {
            $response['my_tickets_count'] = \App\Models\AssetLog::where('user_id', $user->id)->where('status', 'pending')->count()
                + \App\Models\CategoryLog::where('user_id', $user->id)->where('status', 'pending')->count();
        }

        return response()->json($response);
    }
}
