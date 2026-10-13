<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\Category;
use App\Models\User;
use App\Services\AssetDepreciationService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetSaleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        Event::fake();
        Notification::fake();
        Storage::fake('local');
        Storage::fake('public');
        Carbon::setTestNow('2026-10-13 10:00:00');
        Category::create(['name' => 'Mobile', 'slug' => 'mobile', 'prefix' => 'M']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function payload(array $changes = []): array
    {
        return array_merge([
            'name' => 'Laptop Terjual', 'category' => 'mobile', 'status' => 'sold',
            'condition' => 'baik', 'description' => 'Laptop kantor', 'price' => 10000000,
            'purchase_date' => '2023-01-01', 'depreciation_rate' => 10,
            'sale_date' => '2025-06-01', 'sale_price' => 6500000,
            'sale_buyer' => 'Pembeli A', 'sale_proof' => UploadedFile::fake()->create('invoice.pdf', 10, 'application/pdf'),
        ], $changes);
    }

    public function test_sold_asset_is_archived_and_proof_is_available_in_history(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($user)->post(route('assets.store'), $this->payload())
            ->assertRedirect(route('assets.archive'))->assertSessionHasNoErrors();
        $asset = Asset::where('name', 'Laptop Terjual')->firstOrFail();
        $this->assertSame('sold', $asset->status);
        $this->assertSame('6500000.00', $asset->sale->amount);
        Storage::disk('local')->assertExists($asset->sale->proof_path);
        $this->get(route('assets.index'))->assertOk()->assertDontSee('Laptop Terjual');
        $this->get(route('assets.archive'))->assertOk()->assertSee('Laptop Terjual')->assertSee('6.500.000,00');
        $this->get(route('assets.show', $asset->id))->assertOk()->assertSee('Riwayat penjualan')->assertSee('invoice.pdf');
        $this->get(route('assets.sale-proof', $asset->id))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        app(AssetDepreciationService::class)->sync($asset, Carbon::parse('2030-01-01'));
        $this->assertSame(2, $asset->depreciations()->count());
    }

    public function test_sale_requires_proof_and_valid_sale_date(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('assets.store'), $this->payload(['sale_proof' => null, 'sale_date' => '2022-01-01']))
            ->assertSessionHasErrors(['sale_proof', 'sale_date']);
        $this->assertDatabaseCount('asset_sales', 0);
        $this->assertDatabaseCount('assets', 0);
    }

    public function test_admin_sale_is_archived_only_after_super_admin_approval(): void
    {
        $asset = Asset::factory()->create(['category' => 'mobile', 'price' => 10000000, 'status' => 'available', 'purchase_date' => '2023-01-01', 'depreciation_rate' => 10]);
        $admin = User::factory()->create(['role' => 'admin']);
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)->put(route('assets.update', $asset->id), $this->payload())
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('available', $asset->fresh()->status);
        $this->assertNull($asset->fresh()->sale);
        $log = AssetLog::where('asset_id', $asset->id)->where('status', 'pending')->firstOrFail();
        $this->get(route('assets.sale-request-proof', $log->id))->assertOk();
        $this->actingAs($superAdmin)->post(route('approvals.approve', ['type' => 'asset', 'id' => $log->id]))
            ->assertRedirect()->assertSessionHas('success');
        $this->assertSame('sold', $asset->fresh()->status);
        $this->assertSame('Pembeli A', $asset->fresh()->sale->buyer_name);
        $this->assertSame(2, $asset->depreciations()->count());
    }

    public function test_sold_asset_cannot_be_reactivated_from_scanner_or_bulk_actions(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->post(route('assets.store'), $this->payload())->assertSessionHasNoErrors();
        $asset = Asset::where('name', 'Laptop Terjual')->firstOrFail();
        $this->postJson(route('scanner.status'), ['asset_tag' => $asset->asset_tag, 'action' => 'check_in'])->assertStatus(422);
        $this->post(route('assets.bulk-update'), ['asset_ids' => [$asset->id], 'status' => 'available'])->assertStatus(422);
        $this->get(route('assets.edit', $asset->id))->assertStatus(422);
        $this->assertSame('sold', $asset->fresh()->status);
    }

    public function test_private_pending_proof_is_not_visible_to_another_admin(): void
    {
        $asset = Asset::factory()->create(['category' => 'mobile', 'status' => 'available', 'purchase_date' => '2023-01-01']);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put(route('assets.update', $asset->id), $this->payload())->assertSessionHasNoErrors();
        $log = AssetLog::where('asset_id', $asset->id)->where('status', 'pending')->firstOrFail();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('assets.sale-request-proof', $log->id))->assertForbidden();
    }
}
