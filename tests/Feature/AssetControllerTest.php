<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_all_asset_ids_returns_all_ids_for_authenticated_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $assets = Asset::factory()->count(3)->create();

        $response = $this->actingAs($user)->getJson(route('assets.all-ids'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'total' => 3,
            ]);

        $ids = $response->json('ids');

        $this->assertCount(3, $ids);
        $this->assertEqualsCanonicalizing($assets->pluck('id')->all(), $ids);
    }

    public function test_get_all_asset_ids_requires_authentication(): void
    {
        $response = $this->getJson(route('assets.all-ids'));

        $response->assertUnauthorized();
    }

    public function test_assets_index_supports_advanced_filters(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        Asset::factory()->create([
            'name' => 'Laptop Admin',
            'category' => 'mobile',
            'status' => 'available',
            'purchase_date' => '2026-01-15',
        ]);

        Asset::factory()->create([
            'name' => 'Monitor Gudang',
            'category' => 'fixed',
            'status' => 'maintenance',
            'purchase_date' => '2025-01-15',
        ]);

        $response = $this->actingAs($user)->get(route('assets.index', [
            'search' => 'Laptop',
            'status' => 'available',
            'category' => 'mobile',
            'date_from' => '2026-01-01',
            'date_to' => '2026-12-31',
        ]));

        $response->assertOk();
        $response->assertSee('Laptop Admin');
        $response->assertDontSee('Monitor Gudang');
    }

    public function test_check_out_and_check_in_via_edit_form(): void
    {
        $user     = User::factory()->create(['role' => 'super_admin']);
        $category = \App\Models\Category::create(['name' => 'Mobile', 'slug' => 'mobile', 'prefix' => 'M']);
        $asset    = Asset::factory()->create([
            'status'   => 'available',
            'asset_tag' => 'M-26-001',
            'category'  => $category->slug,
        ]);

        // Check-out via edit form (status available → in_use)
        $this->actingAs($user)
            ->put(route('assets.update', $asset->id), [
                'name'        => $asset->name,
                'category'    => $category->slug,
                'description' => $asset->description,
                'status' => 'in_use',
                'purchase_date' => $asset->purchase_date->format('Y-m-d'),
                'condition' => $asset->condition,
                'borrower_name' => 'Budi',
                'due_at' => now()->addDays(2)->format('Y-m-d'),
                'borrow_notes' => 'Unit untuk meeting client',
            ])
            ->assertRedirect(route('assets.index'));

        $asset->refresh();
        $this->assertEquals('in_use', $asset->status);
        $this->assertDatabaseHas('asset_transactions', [
            'asset_id' => $asset->id,
            'borrower_name' => 'Budi',
            'status' => 'borrowed',
        ]);

        // Check-in via edit form (status in_use → available)
        $this->actingAs($user)
            ->put(route('assets.update', $asset->id), [
                'name'          => $asset->name,
                'category'      => $category->slug,
                'description'   => $asset->description,
                'status'        => 'available',
                'purchase_date' => $asset->purchase_date->format('Y-m-d'),
                'condition'     => $asset->condition,
            ])
            ->assertRedirect(route('assets.index'));

        $asset->refresh();
        $this->assertEquals('available', $asset->status);

        $transaction = AssetTransaction::where('asset_id', $asset->id)->latest('id')->first();
        $this->assertNotNull($transaction?->returned_at);
        $this->assertEquals('returned', $transaction?->status);
    }
}
