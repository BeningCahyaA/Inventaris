<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect('/');
    }

    public function test_dashboard_displays_for_authenticated_user(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHasAll([
            'totalAssets', 'totalValue', 'pendingCount',
            'statusAvailable', 'statusInUse', 'statusMaintenance', 'statusBroken',
            'categoryStats',
            'condBaik', 'condRusak', 'condRusakTotal',
            'logs',
        ]);
    }

    public function test_dashboard_stats_are_accurate(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        Asset::factory()->create(['status' => 'available', 'category' => 'mobile', 'condition' => 'baik', 'price' => 1000]);
        Asset::factory()->create(['status' => 'in_use', 'category' => 'fixed', 'condition' => 'rusak', 'price' => 2000]);
        Asset::factory()->create(['status' => 'maintenance', 'category' => 'semi-mobile', 'condition' => 'rusak_total', 'price' => 3000]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('totalAssets', 3);
        $response->assertViewHas('totalValue', 6000);
        $response->assertViewHas('statusAvailable', 1);
        $response->assertViewHas('statusInUse', 1);
        $response->assertViewHas('statusMaintenance', 1);
        $response->assertViewHas('condBaik', 1);
        $response->assertViewHas('condRusak', 1);
        $response->assertViewHas('condRusakTotal', 1);
        // categoryStats is a dynamic collection keyed by category slug
        $response->assertViewHas('categoryStats');
    }
}
