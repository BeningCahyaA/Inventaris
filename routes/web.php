<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AssetStatusController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Login
Route::get('/', function () {
    return view('auth.login');
})->middleware('guest')->name('login');

Route::post('/', [AuthenticatedSessionController::class, 'store'])->middleware('guest')->name('login.post');

// Dashboard
Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/api/dashboard/stats', [DashboardController::class, 'stats'])->middleware(['auth', 'verified'])->name('dashboard.stats');

// --- GRUP ROUTE YANG WAJIB LOGIN ---
Route::middleware('auth')->group(function () {

    // === 1. FITUR OPERASIONAL ASET (Admin & Super Admin) ===
    Route::middleware(['role:super_admin,admin'])->group(function () {
        // CRUD Aset
        Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
        Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create');
        Route::get('/assets/archive', [AssetController::class, 'archive'])->name('assets.archive');
        Route::get('/assets/{id}/sale-proof', [\App\Http\Controllers\AssetAttachmentController::class, 'saleProof'])->name('assets.sale-proof');
        Route::get('/asset-sale-requests/{logId}/proof', [\App\Http\Controllers\AssetAttachmentController::class, 'saleRequestProof'])->name('assets.sale-request-proof');
        Route::post('/assets/store', [AssetController::class, 'store'])->name('assets.store');
        Route::get('/assets/{id}/edit', [AssetController::class, 'edit'])->name('assets.edit');
        Route::put('/assets/{id}', [AssetController::class, 'update'])->name('assets.update');
        Route::delete('/assets/{id}', [AssetController::class, 'destroy'])->name('assets.destroy');
        Route::get('/assets/{id}/attachments/{attachmentId}', [\App\Http\Controllers\AssetAttachmentController::class, 'show'])->name('assets.attachments.show');
        Route::delete('/assets/{id}/attachments/{attachmentId}', [\App\Http\Controllers\AssetAttachmentController::class, 'destroy'])->name('assets.attachments.destroy');

        // CRUD Kategori
        Route::resource('categories', CategoryController::class);

        // Status Update (Check-in/out)
        // Status History
        Route::get('/assets/history', [AssetStatusController::class, 'history'])->name('assets.history');

        // Report & Print
        Route::get('/assets/print', [ReportController::class, 'printPreview'])->name('assets.print');
        Route::get('/assets/download-pdf', [ReportController::class, 'downloadPdf'])->name('assets.pdf');
        Route::get('/assets/export', [ReportController::class, 'exportReport'])->name('assets.export');
        Route::get('/assets/export-csv', [ReportController::class, 'exportCsv'])->name('assets.export-csv');

        // API untuk Select All
        Route::get('/api/assets/all-ids', [AssetController::class, 'getAllAssetIds'])->name('assets.all-ids');

        // Bulk actions
        Route::middleware('throttle:10,1')->group(function () {
            Route::post('/assets/bulk-update', [AssetController::class, 'bulkUpdate'])->name('assets.bulk-update');
            Route::post('/assets/bulk-delete-request', [AssetController::class, 'bulkDeleteRequest'])->name('assets.bulk-delete-request');
        });

        Route::get('/assets/{id}', [AssetController::class, 'show'])->name('assets.show');
    });

    // === 2. FITUR SISTEM CORE (KHUSUS Super Admin) ===
    Route::middleware(['role:super_admin'])->group(function () {
        Route::resource('users', UserController::class);
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::post('/approvals/{type}/{id}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{type}/{id}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
        
        // Bulk Actions
        Route::post('/approvals/bulk-approve', [ApprovalController::class, 'bulkApprove'])->name('approvals.bulk-approve');
        Route::post('/approvals/bulk-reject', [ApprovalController::class, 'bulkReject'])->name('approvals.bulk-reject');
    });

    // === 3. TIKET SAYA (Admin & Super Admin) ===
    Route::get('/my-tickets', [ApprovalController::class, 'myTickets'])->name('approvals.my-tickets');

    // === 4. NOTIFICATION CENTER ===
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::delete('/notifications/delete-all', [NotificationController::class, 'deleteAll'])->name('notifications.delete-all');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/api/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');

    // === 5. QR SCANNER (Admin & Super Admin) ===
    Route::middleware(['role:super_admin,admin', 'throttle:30,1'])->group(function () {
        Route::get('/scanner', [ScannerController::class, 'index'])->name('scanner.index');
        Route::post('/scanner/resolve', [ScannerController::class, 'resolve'])->name('scanner.resolve');
        Route::post('/scanner/status', [ScannerController::class, 'updateStatus'])->name('scanner.status');
        Route::post('/scanner/quick-edit', [ScannerController::class, 'quickEdit'])->name('scanner.quick-edit');
    });


});

require __DIR__.'/auth.php';
