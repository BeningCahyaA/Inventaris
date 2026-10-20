<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\CategoryLog;
use App\Models\Category;
use App\Models\User;
use App\Services\AssetDepreciationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AssetDepreciationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        Event::fake();
        Notification::fake();
        Carbon::setTestNow('2026-10-13 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function asset(array $values = []): Asset
    {
        $rate = $values['depreciation_rate'] ?? 10;
        unset($values['depreciation_rate']);
        $category = Category::create(['name' => uniqid('Category'), 'slug' => uniqid('category'), 'prefix' => uniqid(), 'depreciation_rate' => $rate]);
        return Asset::factory()->create(array_merge([
            'category' => $category->slug,
            'price' => 10000000,
            'purchase_date' => '2023-10-13', 'status' => 'available',
        ], $values));
    }

    public function test_annual_depreciation_uses_original_value_and_catches_up_without_duplicates(): void
    {
        $asset = $this->asset();
        $service = app(AssetDepreciationService::class);
        $service->sync($asset);
        $service->sync($asset);
        $rows = $asset->depreciations()->orderBy('year_number')->get();
        $this->assertCount(3, $rows);
        $this->assertSame(['1000000.00', '1000000.00', '1000000.00'], $rows->pluck('amount')->all());
        $this->assertSame('7000000.00', $rows->last()->closing_value);
        $this->assertSame('2026-10-13', $rows->last()->depreciated_on->toDateString());
        $this->assertEquals(10000000, $asset->fresh()->price);
    }

    public function test_depreciation_waits_for_a_full_year_and_never_goes_below_zero(): void
    {
        $asset = $this->asset(['purchase_date' => '2025-10-14']);
        $service = app(AssetDepreciationService::class);
        $service->sync($asset);
        $this->assertSame(0, $asset->depreciations()->count());
        $service->sync($asset, Carbon::parse('2040-10-14'));
        $this->assertSame(10, $asset->depreciations()->count());
        $this->assertSame('0.00', $asset->depreciations()->orderByDesc('year_number')->first()->closing_value);
    }

    public function test_rate_changes_preserve_history_and_apply_to_future_years(): void
    {
        $asset = $this->asset(['purchase_date' => '2025-10-13']);
        $service = app(AssetDepreciationService::class);
        $service->sync($asset);
        $asset->categoryDefinition->update(['depreciation_rate' => 20]);
        $service->sync($asset, Carbon::parse('2027-10-13'));
        $rows = $asset->depreciations()->orderBy('year_number')->get();
        $this->assertSame('10.00', $rows[0]->rate);
        $this->assertSame('20.00', $rows[1]->rate);
        $this->assertSame('7000000.00', $rows[1]->closing_value);
    }

    public function test_zero_rate_future_purchase_and_leap_year_dates(): void
    {
        $service = app(AssetDepreciationService::class);
        $zero = $this->asset(['depreciation_rate' => 0]);
        $future = $this->asset(['purchase_date' => '2027-01-01']);
        $service->sync($zero);
        $service->sync($future);
        $this->assertSame(0, $zero->depreciations()->count());
        $this->assertSame(0, $future->depreciations()->count());
        $leap = $this->asset(['purchase_date' => '2024-02-29']);
        $service->sync($leap, Carbon::parse('2025-02-28'));
        $this->assertSame('2025-02-28', $leap->depreciations()->first()->depreciated_on->toDateString());
    }

    public function test_rounding_and_final_partial_charge_stop_at_zero(): void
    {
        $asset = $this->asset(['price' => 100, 'depreciation_rate' => 33.33, 'purchase_date' => '2020-10-13']);
        app(AssetDepreciationService::class)->sync($asset);
        $rows = $asset->depreciations()->orderBy('year_number')->get();
        $this->assertCount(4, $rows);
        $this->assertSame('0.01', $rows->last()->amount);
        $this->assertSame('0.00', $rows->last()->closing_value);
    }

    public function test_daily_command_uses_jakarta_calendar_date_and_is_idempotent(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-13 00:10:00', 'Asia/Jakarta'));
        $asset = $this->asset(['purchase_date' => '2025-10-13']);
        $this->artisan('assets:depreciate')->assertExitCode(0);
        $this->artisan('assets:depreciate')->assertExitCode(0);
        $this->assertSame(1, $asset->depreciations()->count());
    }

    public function test_admin_rate_change_requires_approval_and_detail_shows_history(): void
    {
        $asset = $this->asset();
        $category = $asset->categoryDefinition;
        $admin = User::factory()->create(['role' => 'admin']);
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $category->update(['prefix' => 'M']);
        $payload = ['name' => $category->name, 'slug' => $category->slug, 'prefix' => 'M', 'depreciation_rate' => 20];
        $this->actingAs($admin)->put(route('categories.update', $category->id), $payload)
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('10.00', $asset->fresh()->depreciation_rate);
        $this->get(route('assets.show', $asset->id))->assertOk()->assertSee('7.000.000,00');
        $log = CategoryLog::where('category_id', $category->id)->where('status', 'pending')->firstOrFail();
        $this->actingAs($superAdmin)->post(route('approvals.approve', ['type' => 'category', 'id' => $log->id]))
            ->assertRedirect()->assertSessionHas('success');
        $this->assertSame('20.00', $asset->fresh()->depreciation_rate);
        $this->get(route('assets.show', $asset->id))->assertOk()->assertSee('Riwayat penyusutan')->assertSee('7.000.000,00');
        $payload['depreciation_rate'] = 101;
        $this->put(route('categories.update', $category->id), $payload)->assertSessionHasErrors('depreciation_rate');
        $this->get(route('assets.edit', $asset->id))->assertOk()->assertDontSee('name="depreciation_rate"', false);
    }
    public function test_dashboard_and_asset_list_show_original_and_depreciated_values(): void
    {
        $asset = $this->asset();
        app(AssetDepreciationService::class)->sync($asset);
        $this->asset(['status' => 'sold', 'price' => 5000000]);
        $deleted = $this->asset(['price' => 2000000]);
        $deleted->delete();
        \Illuminate\Support\Facades\DB::connection()->getPdo()->sqliteCreateFunction('DATE_FORMAT', fn ($value, $format) => substr($value, 0, 7), 2);
        \Illuminate\Support\Facades\Cache::flush();
        $stats = app(\App\Services\DashboardService::class)->getStats();
        $this->assertSame(10000000, $stats['totalValue']);
        $this->assertSame(7000000.0, $stats['depreciatedValue']);
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $this->get(route('assets.index'))->assertOk()->assertSee('Nilai setelah penyusutan')->assertSee('7.000.000,00');
        $this->get(route('dashboard'))->assertOk()->assertSee('Total nilai aset')->assertSee('Nilai aset setelah penyusutan')->assertSee('7.000.000,00');
        $asset->update(['price' => 100]);
        $this->asset(['price' => 500]);
        \Illuminate\Support\Facades\Cache::flush();
        $this->assertSame(500.0, app(\App\Services\DashboardService::class)->getStats()['depreciatedValue']);
    }

}
