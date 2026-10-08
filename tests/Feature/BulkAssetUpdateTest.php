<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\User;
use App\Notifications\ApprovalSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BulkAssetUpdateTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------ helpers

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function assets(int $count = 3): \Illuminate\Database\Eloquent\Collection
    {
        return Asset::factory()->count($count)->create(['status' => 'available', 'location' => 'Ruang A']);
    }

    private function assetIds(\Illuminate\Database\Eloquent\Collection $assets): array
    {
        return $assets->pluck('id')->all();
    }

    // ------------------------------------------------------------------ bulkUpdate – access

    public function test_bulk_update_requires_auth(): void
    {
        $assets = $this->assets(2);

        $this->post(route('assets.bulk-update'), [
            'asset_ids' => $this->assetIds($assets),
            'location'  => 'Ruang B',
        ])->assertRedirect(route('login'));
    }

    public function test_bulk_update_requires_asset_ids(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('assets.bulk-update'), ['location' => 'Ruang B'])
            ->assertSessionHasErrors('asset_ids');
    }

    public function test_bulk_update_requires_existing_asset_ids(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('assets.bulk-update'), [
                'asset_ids' => [99999],
                'location'  => 'Ruang B',
            ])
            ->assertSessionHasErrors('asset_ids.0');
    }

    // ------------------------------------------------------------------ bulkUpdate – super_admin

    public function test_super_admin_bulk_update_applies_changes_directly(): void
    {
        $superAdmin = $this->superAdmin();
        $assets     = $this->assets(3);

        $response = $this->actingAs($superAdmin)->post(route('assets.bulk-update'), [
            'asset_ids' => $this->assetIds($assets),
            'location'  => 'Ruang Z',
        ]);

        $response->assertRedirect(route('assets.index'))->assertSessionHas('success');

        foreach ($assets as $asset) {
            $this->assertDatabaseHas('assets', ['id' => $asset->id, 'location' => 'Ruang Z']);
        }
    }

    public function test_super_admin_bulk_update_creates_approved_logs(): void
    {
        $superAdmin = $this->superAdmin();
        $assets     = $this->assets(2);

        $this->actingAs($superAdmin)->post(route('assets.bulk-update'), [
            'asset_ids' => $this->assetIds($assets),
            'location'  => 'Ruang Z',
        ]);

        foreach ($assets as $asset) {
            $this->assertDatabaseHas('asset_logs', [
                'asset_id' => $asset->id,
                'action'   => 'update',
                'status'   => 'approved',
            ]);
        }
    }

    public function test_super_admin_bulk_update_can_change_multiple_fields(): void
    {
        $superAdmin = $this->superAdmin();
        $assets     = $this->assets(2);

        $this->actingAs($superAdmin)->post(route('assets.bulk-update'), [
            'asset_ids'        => $this->assetIds($assets),
            'location'         => 'Gudang Baru',
            'person_in_charge' => 'Budi',
        ]);

        foreach ($assets as $asset) {
            $this->assertDatabaseHas('assets', [
                'id'               => $asset->id,
                'location'         => 'Gudang Baru',
                'person_in_charge' => 'Budi',
            ]);
        }
    }

    public function test_super_admin_bulk_update_ignores_empty_fields(): void
    {
        $superAdmin = $this->superAdmin();
        $asset      = Asset::factory()->create(['location' => 'Asli', 'person_in_charge' => 'Asli PIC']);

        $this->actingAs($superAdmin)->post(route('assets.bulk-update'), [
            'asset_ids' => [$asset->id],
            'location'  => 'Berubah',
            // person_in_charge tidak dikirim → tidak berubah
        ]);

        $this->assertDatabaseHas('assets', [
            'id'               => $asset->id,
            'location'         => 'Berubah',
            'person_in_charge' => 'Asli PIC',
        ]);
    }

    // ------------------------------------------------------------------ bulkUpdate – admin

    public function test_admin_bulk_update_creates_pending_log_per_asset(): void
    {
        Notification::fake();

        $admin      = $this->admin();
        $superAdmin = $this->superAdmin();
        $assets     = $this->assets(3);

        $response = $this->actingAs($admin)->post(route('assets.bulk-update'), [
            'asset_ids' => $this->assetIds($assets),
            'location'  => 'Ruang Z',
        ]);

        $response->assertRedirect(route('assets.index'))->assertSessionHas('success');

        // Assets NOT changed
        foreach ($assets as $asset) {
            $this->assertDatabaseHas('assets', ['id' => $asset->id, 'location' => 'Ruang A']);
        }

        // Pending log per asset created
        $this->assertDatabaseCount('asset_logs', 3);
        foreach ($assets as $asset) {
            $this->assertDatabaseHas('asset_logs', [
                'asset_id' => $asset->id,
                'action'   => 'update',
                'status'   => 'pending',
            ]);
        }
    }

    public function test_admin_bulk_update_sends_notification_to_super_admin_per_asset(): void
    {
        Notification::fake();

        $admin      = $this->admin();
        $superAdmin = $this->superAdmin();
        $assets     = $this->assets(3);

        $this->actingAs($admin)->post(route('assets.bulk-update'), [
            'asset_ids' => $this->assetIds($assets),
            'location'  => 'Ruang Z',
        ]);

        Notification::assertSentToTimes($superAdmin, ApprovalSubmittedNotification::class, 3);
    }

    public function test_bulk_update_with_no_changes_returns_error(): void
    {
        $superAdmin = $this->superAdmin();
        $assets     = $this->assets(2);

        $response = $this->actingAs($superAdmin)->post(route('assets.bulk-update'), [
            'asset_ids' => $this->assetIds($assets),
            // no fields to change
        ]);

        $response->assertRedirect()->assertSessionHas('error');
    }

    // ------------------------------------------------------------------ bulkDeleteRequest – super_admin

    public function test_super_admin_bulk_delete_removes_assets_directly(): void
    {
        $superAdmin = $this->superAdmin();
        $assets     = $this->assets(2);

        $response = $this->actingAs($superAdmin)->post(route('assets.bulk-delete-request'), [
            'asset_ids' => $this->assetIds($assets),
        ]);

        $response->assertRedirect(route('assets.index'))->assertSessionHas('success');

        foreach ($assets as $asset) {
            $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
        }
    }

    public function test_super_admin_bulk_delete_logs_approved_delete(): void
    {
        $superAdmin = $this->superAdmin();
        $assets     = $this->assets(2);
        $ids        = $this->assetIds($assets);

        // Intercept log creation before the cascade delete wipes them.
        // We count how many 'delete' logs are created inside the transaction.
        $logCount = 0;
        \App\Models\AssetLog::created(function (\App\Models\AssetLog $log) use (&$logCount) {
            if ($log->action === 'delete' && $log->status === 'approved') {
                $logCount++;
            }
        });

        $this->actingAs($superAdmin)->post(route('assets.bulk-delete-request'), [
            'asset_ids' => $ids,
        ]);

        // asset_logs.asset_id has onDelete(cascade), so logs are wiped when assets
        // are deleted. Verify the log creation was triggered once per asset.
        $this->assertSame(2, $logCount, 'Expected one approved delete log per asset before cascade removal.');

        // Assets themselves should be gone.
        foreach ($ids as $id) {
            $this->assertDatabaseMissing('assets', ['id' => $id]);
        }
    }

    // ------------------------------------------------------------------ bulkDeleteRequest – admin

    public function test_admin_bulk_delete_creates_pending_logs_not_deleting(): void
    {
        Notification::fake();

        $admin      = $this->admin();
        $superAdmin = $this->superAdmin();
        $assets     = $this->assets(2);

        $response = $this->actingAs($admin)->post(route('assets.bulk-delete-request'), [
            'asset_ids' => $this->assetIds($assets),
        ]);

        $response->assertRedirect()->assertSessionHas('success');

        // Assets still exist
        foreach ($assets as $asset) {
            $this->assertDatabaseHas('assets', ['id' => $asset->id]);
        }

        // Pending log created per asset
        foreach ($assets as $asset) {
            $this->assertDatabaseHas('asset_logs', [
                'asset_id' => $asset->id,
                'action'   => 'delete',
                'status'   => 'pending',
            ]);
        }

        Notification::assertSentToTimes($superAdmin, ApprovalSubmittedNotification::class, 2);
    }

    public function test_admin_bulk_delete_requires_auth(): void
    {
        $assets = $this->assets(2);

        $this->post(route('assets.bulk-delete-request'), [
            'asset_ids' => $this->assetIds($assets),
        ])->assertRedirect(route('login'));
    }

    public function test_bulk_delete_validates_existing_asset_ids(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('assets.bulk-delete-request'), [
                'asset_ids' => [88888, 99999],
            ])
            ->assertSessionHasErrors();
    }

    // ------------------------------------------------------------------ warranty_state / loan_state filters

    public function test_warranty_state_expiring_filter(): void
    {
        $user = $this->admin();

        Asset::factory()->create([
            'name'                => 'Expiring Soon',
            'warranty_expiry_date' => now()->addDays(10)->format('Y-m-d'),
        ]);
        Asset::factory()->create([
            'name'                => 'Still Valid',
            'warranty_expiry_date' => now()->addDays(90)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('assets.index', ['warranty_state' => 'expiring']));

        $response->assertOk();
        $response->assertSee('Expiring Soon');
        $response->assertDontSee('Still Valid');
    }

    public function test_warranty_state_expired_filter(): void
    {
        $user = $this->admin();

        Asset::factory()->create([
            'name'                => 'Already Expired',
            'warranty_expiry_date' => now()->subDays(5)->format('Y-m-d'),
        ]);
        Asset::factory()->create([
            'name'                => 'Still Covered',
            'warranty_expiry_date' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('assets.index', ['warranty_state' => 'expired']));

        $response->assertOk();
        $response->assertSee('Already Expired');
        $response->assertDontSee('Still Covered');
    }

    public function test_loan_state_overdue_filter(): void
    {
        $user      = $this->admin();
        $overdue   = Asset::factory()->create(['name' => 'Overdue Asset', 'status' => 'in_use']);
        $normal    = Asset::factory()->create(['name' => 'Normal Asset', 'status' => 'available']);

        \App\Models\AssetTransaction::create([
            'asset_id'      => $overdue->id,
            'borrower_name' => 'Tester',
            'borrowed_at'   => now()->subDays(10),
            'due_at'        => now()->subDays(3),
            'status'        => 'overdue',
            'created_by'    => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('assets.index', ['loan_state' => 'overdue']));

        $response->assertOk();
        $response->assertSee('Overdue Asset');
        $response->assertDontSee('Normal Asset');
    }

    public function test_loan_state_returned_filter_only_shows_assets_with_returned_transactions(): void
    {
        $user       = $this->admin();
        $returned   = Asset::factory()->create(['name' => 'Returned Asset', 'status' => 'available']);
        $neverLoaned = Asset::factory()->create(['name' => 'Never Loaned', 'status' => 'available']);

        \App\Models\AssetTransaction::create([
            'asset_id'      => $returned->id,
            'borrower_name' => 'Tester',
            'borrowed_at'   => now()->subDays(5),
            'returned_at'   => now()->subDays(1),
            'status'        => 'returned',
            'created_by'    => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('assets.index', ['loan_state' => 'returned']));

        $response->assertOk();
        $response->assertSee('Returned Asset');
        $response->assertDontSee('Never Loaned');
    }}