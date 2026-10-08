<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\User;
use App\Notifications\ApprovalSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ScannerControllerTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------ helpers

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    private function asset(array $attrs = []): Asset
    {
        return Asset::factory()->create(array_merge(['asset_tag' => 'TEST-26-001', 'status' => 'available'], $attrs));
    }

    // ------------------------------------------------------------------ index

    public function test_scanner_page_requires_auth(): void
    {
        $this->get(route('scanner.index'))->assertRedirect(route('login'));
    }

    public function test_scanner_page_accessible_by_admin_and_super_admin(): void
    {
        $this->actingAs($this->admin())->get(route('scanner.index'))->assertOk();
        $this->actingAs($this->superAdmin())->get(route('scanner.index'))->assertOk();
    }

    // ------------------------------------------------------------------ resolve

    public function test_resolve_returns_found_true_for_valid_tag(): void
    {
        $asset = $this->asset(['name' => 'Laptop Scanner', 'asset_tag' => 'SC-26-001', 'status' => 'available']);

        $response = $this->actingAs($this->admin())
            ->postJson(route('scanner.resolve'), ['asset_tag' => 'SC-26-001']);

        $response->assertOk()
            ->assertJson([
                'found'         => true,
                'asset'         => ['asset_tag' => 'SC-26-001', 'name' => 'Laptop Scanner', 'status' => 'available'],
                'valid_actions' => ['check_out', 'maintenance'],
            ]);
    }

    public function test_resolve_returns_found_false_for_unknown_tag(): void
    {
        $response = $this->actingAs($this->admin())
            ->postJson(route('scanner.resolve'), ['asset_tag' => 'UNKNOWN-99-999']);

        $response->assertStatus(404)->assertJson(['found' => false]);
    }

    public function test_resolve_valid_actions_for_in_use_asset(): void
    {
        $asset = $this->asset(['asset_tag' => 'IN-26-001', 'status' => 'in_use']);

        $response = $this->actingAs($this->admin())
            ->postJson(route('scanner.resolve'), ['asset_tag' => 'IN-26-001']);

        $response->assertOk()->assertJson(['valid_actions' => ['check_in']]);
    }

    public function test_resolve_valid_actions_for_maintenance_asset(): void
    {
        $asset = $this->asset(['asset_tag' => 'MT-26-001', 'status' => 'maintenance']);

        $response = $this->actingAs($this->admin())
            ->postJson(route('scanner.resolve'), ['asset_tag' => 'MT-26-001']);

        $response->assertOk()->assertJson(['valid_actions' => ['check_in']]);
    }

    public function test_resolve_empty_valid_actions_for_broken_asset(): void
    {
        $this->asset(['asset_tag' => 'BR-26-001', 'status' => 'broken']);

        $response = $this->actingAs($this->admin())
            ->postJson(route('scanner.resolve'), ['asset_tag' => 'BR-26-001']);

        $response->assertOk()->assertJson(['valid_actions' => []]);
    }

    public function test_resolve_returns_person_in_charge_and_description(): void
    {
        $this->asset([
            'asset_tag'        => 'SC-26-002',
            'person_in_charge' => 'Budi',
            'description'      => 'Laptop kantor',
        ]);

        $response = $this->actingAs($this->admin())
            ->postJson(route('scanner.resolve'), ['asset_tag' => 'SC-26-002']);

        $response->assertOk()
            ->assertJsonPath('asset.person_in_charge', 'Budi')
            ->assertJsonPath('asset.description', 'Laptop kantor');
    }

    // ------------------------------------------------------------------ updateStatus

    public function test_check_out_creates_transaction_and_updates_status(): void
    {
        $asset = $this->asset(['asset_tag' => 'CO-26-001', 'status' => 'available']);
        $due   = now()->addDays(7)->format('Y-m-d');

        $response = $this->actingAs($this->superAdmin())
            ->postJson(route('scanner.status'), [
                'asset_tag'     => 'CO-26-001',
                'action'        => 'check_out',
                'borrower_name' => 'Siti',
                'due_at'        => $due,
            ]);

        $response->assertOk()->assertJson(['success' => true]);

        $asset->refresh();
        $this->assertEquals('in_use', $asset->status);
        $this->assertDatabaseHas('asset_transactions', [
            'asset_id'      => $asset->id,
            'borrower_name' => 'Siti',
            'status'        => 'borrowed',
        ]);
    }

    public function test_check_in_marks_transaction_returned(): void
    {
        $user  = $this->superAdmin();
        $asset = $this->asset(['asset_tag' => 'CI-26-001', 'status' => 'available']);

        // Check out first
        $this->actingAs($user)->postJson(route('scanner.status'), [
            'asset_tag'     => 'CI-26-001',
            'action'        => 'check_out',
            'borrower_name' => 'Andi',
            'due_at'        => now()->addDays(3)->format('Y-m-d'),
        ]);

        // Now check in
        $response = $this->actingAs($user)->postJson(route('scanner.status'), [
            'asset_tag' => 'CI-26-001',
            'action'    => 'check_in',
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $asset->refresh();
        $this->assertEquals('available', $asset->status);

        $tx = \App\Models\AssetTransaction::where('asset_id', $asset->id)->latest('id')->first();
        $this->assertEquals('returned', $tx->status);
        $this->assertNotNull($tx->returned_at);
    }

    public function test_scanner_status_requires_valid_asset_tag(): void
    {
        $response = $this->actingAs($this->admin())
            ->postJson(route('scanner.status'), [
                'asset_tag' => 'NOEXIST-00-000',
                'action'    => 'check_out',
            ]);

        $response->assertStatus(422);
    }

    // ------------------------------------------------------------------ quickEdit

    public function test_super_admin_quick_edit_directly_updates_asset(): void
    {
        $superAdmin = $this->superAdmin();
        $asset      = $this->asset(['name' => 'Old Name', 'location' => 'Gudang A']);

        $response = $this->actingAs($superAdmin)
            ->postJson(route('scanner.quick-edit'), [
                'asset_id' => $asset->id,
                'name'     => 'New Name',
                'location' => 'Gudang B',
            ]);

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'name' => 'New Name', 'location' => 'Gudang B']);
        $this->assertDatabaseHas('asset_logs', [
            'asset_id' => $asset->id,
            'action'   => 'update',
            'status'   => 'approved',
        ]);
    }

    public function test_super_admin_quick_edit_returns_updated_asset_fields(): void
    {
        $superAdmin = $this->superAdmin();
        $asset      = $this->asset(['condition' => 'baik']);

        $response = $this->actingAs($superAdmin)
            ->postJson(route('scanner.quick-edit'), [
                'asset_id'  => $asset->id,
                'condition' => 'rusak',
            ]);

        $response->assertOk()
            ->assertJsonPath('asset.condition', 'rusak');
    }

    public function test_admin_quick_edit_creates_pending_log_not_direct_update(): void
    {
        Notification::fake();

        $admin      = $this->admin();
        $superAdmin = $this->superAdmin();
        $asset      = $this->asset(['name' => 'Unchanged', 'location' => 'Lokasi Lama']);

        $response = $this->actingAs($admin)
            ->postJson(route('scanner.quick-edit'), [
                'asset_id' => $asset->id,
                'location' => 'Lokasi Baru',
            ]);

        $response->assertOk()->assertJson(['success' => true]);

        // Asset NOT changed yet
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'location' => 'Lokasi Lama']);

        // Pending log created
        $this->assertDatabaseHas('asset_logs', [
            'asset_id' => $asset->id,
            'action'   => 'update',
            'status'   => 'pending',
        ]);

        Notification::assertSentTo($superAdmin, ApprovalSubmittedNotification::class);
    }

    public function test_quick_edit_requires_at_least_one_field(): void
    {
        $asset = $this->asset();

        $response = $this->actingAs($this->admin())
            ->postJson(route('scanner.quick-edit'), ['asset_id' => $asset->id]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_quick_edit_validates_condition_enum(): void
    {
        $asset = $this->asset();

        $response = $this->actingAs($this->admin())
            ->postJson(route('scanner.quick-edit'), [
                'asset_id'  => $asset->id,
                'condition' => 'hancur', // invalid value
            ]);

        $response->assertStatus(422);
    }

    public function test_quick_edit_requires_auth(): void
    {
        $asset = $this->asset();

        $this->postJson(route('scanner.quick-edit'), [
            'asset_id' => $asset->id,
            'location' => 'Test',
        ])->assertUnauthorized();
    }
}
