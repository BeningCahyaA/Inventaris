<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\User;
use App\Notifications\ApprovalResolvedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApprovalControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_approval_queue_requires_super_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('approvals.index'))->assertForbidden();
    }

    public function test_super_admin_can_view_approval_queue(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superAdmin)->get(route('approvals.index'));

        $response->assertOk();
        $response->assertViewHas('pendingLogs');
    }

    public function test_super_admin_can_approve_update_request(): void
    {
        Notification::fake();

        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin']);
        $asset = Asset::factory()->create(['name' => 'Old Name']);

        $log = AssetLog::create([
            'asset_id' => $asset->id,
            'user_id' => $admin->id,
            'action' => 'update',
            'old_data' => $asset->toArray(),
            'new_data' => array_merge($asset->toArray(), ['name' => 'New Name']),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($superAdmin)
            ->post(route('approvals.approve', ['type' => 'asset', 'id' => $log->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('asset_logs', ['id' => $log->id, 'status' => 'approved']);
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'name' => 'New Name']);

        Notification::assertSentTo($admin, ApprovalResolvedNotification::class);
    }

    public function test_super_admin_can_reject_request(): void
    {
        Notification::fake();

        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin']);
        $asset = Asset::factory()->create();

        $log = AssetLog::create([
            'asset_id' => $asset->id,
            'user_id' => $admin->id,
            'action' => 'delete',
            'old_data' => $asset->toArray(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($superAdmin)
            ->post(route('approvals.reject', ['type' => 'asset', 'id' => $log->id]), [
                'rejection_note' => 'Tidak disetujui.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('asset_logs', [
            'id' => $log->id,
            'status' => 'rejected',
            'rejection_note' => 'Tidak disetujui.',
        ]);

        Notification::assertSentTo($admin, ApprovalResolvedNotification::class);
    }

    public function test_admin_update_creates_pending_log(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'super_admin']);
        $category = \App\Models\Category::create(['name' => 'Mobile', 'slug' => 'mobile', 'prefix' => 'M']);
        $asset = Asset::factory()->create(['name' => 'Original', 'status' => 'available', 'category' => $category->slug]);

        $response = $this->actingAs($admin)->put(route('assets.update', $asset->id), [
            'name' => 'Updated',
            'category' => $category->slug,
            'status' => 'available',
            'description' => $asset->description,
            'purchase_date' => $asset->purchase_date->format('Y-m-d'),
            'condition' => 'baik',
        ]);

        $response->assertRedirect(route('assets.index'));

        // Aset TIDAK berubah (masih pending)
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'name' => 'Original']);
        // Log pending terbuat
        $this->assertDatabaseHas('asset_logs', [
            'asset_id' => $asset->id,
            'action' => 'update',
            'status' => 'pending',
        ]);
    }
}
