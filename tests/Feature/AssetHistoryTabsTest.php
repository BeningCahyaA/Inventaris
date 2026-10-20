<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\Category;
use App\Models\CategoryLog;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AssetHistoryTabsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        Event::fake();
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
    }

    public function test_history_tabs_render_and_activity_search_includes_assets_and_categories(): void
    {
        $asset = Asset::factory()->create(['name' => 'Laptop Audit']);
        $category = Category::create(['name' => 'Kategori Audit', 'slug' => 'audit', 'prefix' => 'A']);
        AssetLog::create(['asset_id' => $asset->id, 'user_id' => auth()->id(), 'action' => 'update', 'status' => 'approved']);
        CategoryLog::create(['category_id' => $category->id, 'user_id' => auth()->id(), 'action' => 'delete', 'status' => 'pending']);
        $this->get(route('assets.history'))->assertOk()->assertSee('Riwayat peminjaman')->assertSee('Log aktivitas')->assertSee('Daftar peminjaman');
        $this->get(route('assets.history', ['tab' => 'activity']))->assertOk()
            ->assertSee('Laptop Audit')->assertSee('Kategori Audit')->assertSee('Menunggu persetujuan');
        $this->get(route('assets.history', ['tab' => 'activity', 'search' => 'Laptop']))->assertOk()
            ->assertSee('Laptop Audit')->assertDontSee('Kategori Audit');
        $this->get(route('assets.history', ['tab' => 'invalid']))->assertOk()->assertSee('Daftar peminjaman');
        $this->get(route('assets.history', ['tab' => 'history']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee('Daftar peminjaman')->assertDontSee('<x-app-layout>', false);
    }
    public function test_approved_updates_link_to_the_asset_and_category_edit_forms(): void
    {
        $category = Category::create(['name' => 'Peralatan', 'slug' => 'peralatan', 'prefix' => 'P']);
        $asset = Asset::factory()->create(['category' => $category->slug, 'status' => 'borrowed']);
        AssetLog::create(['asset_id' => $asset->id, 'user_id' => auth()->id(), 'action' => 'update', 'status' => 'approved']);
        CategoryLog::create(['category_id' => $category->id, 'user_id' => auth()->id(), 'action' => 'update', 'status' => 'approved']);
        $this->get(route('approvals.index', ['status' => 'approved']))->assertOk()
            ->assertSee('href="'.route('assets.edit', $asset->id).'"', false)
            ->assertSee('href="'.route('categories.edit', $category->id).'"', false);
        $this->get(route('assets.edit', $asset->id))->assertOk();
        $this->get(route('categories.edit', $category->id))->assertOk();
        $this->get(route('approvals.index', ['status' => 'approved', 'search' => 'Peralatan']))->assertOk()
            ->assertSee($category->name)->assertDontSee($asset->asset_tag);
        $this->get(route('approvals.index', ['status' => 'pending', 'search' => 'Peralatan']))->assertOk()
            ->assertDontSee('href="'.route('categories.edit', $category->id).'"', false);

        $asset->delete();
        $this->get(route('approvals.index', ['status' => 'approved']))->assertOk()->assertSee('Data sudah dihapus');
    }

}
