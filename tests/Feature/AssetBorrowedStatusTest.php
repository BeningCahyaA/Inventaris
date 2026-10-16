<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AssetBorrowedStatusTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        Event::fake();
        Notification::fake();
        Category::create(['name' => 'Mobile', 'slug' => 'mobile', 'prefix' => 'M']);
    }

    private function payload(Asset $asset, string $status = 'borrowed'): array
    {
        return array_merge($asset->only(['name', 'category', 'condition', 'description', 'price']), [
            'purchase_date' => '2025-01-01', 'status' => $status,
            'borrower_name' => 'Budi Peminjam', 'due_at' => now()->addWeek()->toDateString(),
            'borrow_notes' => 'Untuk kegiatan kantor',
        ]);
    }

    public function test_edit_borrowed_status_records_history_filters_and_closes_on_return_without_duplicates(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $asset = Asset::factory()->create(['name' => 'Laptop Dipinjam', 'category' => 'mobile', 'status' => 'available']);
        Asset::factory()->create(['name' => 'Laptop Tersedia', 'category' => 'mobile', 'status' => 'available']);
        $this->get(route('assets.edit', $asset->id))->assertOk()->assertSee('value="borrowed"', false);
        $this->put(route('assets.update', $asset->id), $this->payload($asset))->assertSessionHasNoErrors();
        $this->assertSame('borrowed', $asset->fresh()->status);
        $transaction = $asset->transactions()->sole();
        $this->assertSame('Budi Peminjam', $transaction->borrower_name);
        $this->assertSame('borrowed', $transaction->status);
        $this->get(route('assets.history'))->assertOk()->assertSee('Budi Peminjam')->assertSee('Laptop Dipinjam');
        $this->get(route('assets.index', ['status' => 'borrowed']))->assertOk()->assertSee('Laptop Dipinjam')->assertDontSee('Laptop Tersedia');
        $this->put(route('assets.update', $asset->id), $this->payload($asset))->assertSessionHasNoErrors();
        $this->assertSame(1, $asset->transactions()->count());
        $this->put(route('assets.update', $asset->id), $this->payload($asset, 'available'))->assertSessionHasNoErrors();
        $this->assertSame('returned', $transaction->fresh()->status);
        $this->assertNotNull($transaction->fresh()->returned_at);
    }

    public function test_admin_borrowing_enters_history_only_after_approval(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $asset = Asset::factory()->create(['category' => 'mobile', 'status' => 'available']);
        $this->actingAs($admin)->put(route('assets.update', $asset->id), $this->payload($asset))->assertSessionHasNoErrors();
        $this->assertSame('available', $asset->fresh()->status);
        $this->assertSame(0, $asset->transactions()->count());
        $log = AssetLog::where('asset_id', $asset->id)->where('status', 'pending')->sole();
        $this->actingAs($superAdmin)->post(route('approvals.approve', ['type' => 'asset', 'id' => $log->id]))->assertSessionHas('success');
        $this->assertSame('borrowed', $asset->fresh()->status);
        $this->assertSame('Budi Peminjam', $asset->transactions()->sole()->borrower_name);
    }

    public function test_borrowing_requires_a_borrower(): void
    {
        $asset = Asset::factory()->create(['category' => 'mobile', 'status' => 'available']);
        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->put(route('assets.update', $asset->id), array_merge($this->payload($asset), ['borrower_name' => '']))
            ->assertSessionHasErrors('borrower_name');
        $this->assertSame(0, $asset->transactions()->count());
    }
    public function test_bulk_borrowing_creates_history_for_each_asset_and_return_closes_it(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $assets = Asset::factory()->count(2)->create(['category' => 'mobile', 'status' => 'available']);
        $payload = ['asset_ids' => $assets->modelKeys(), 'status' => 'borrowed', 'borrower_name' => 'Tim Acara', 'location' => 'Ruang Acara'];
        $this->post(route('assets.bulk-update'), array_merge($payload, ['borrower_name' => '']))->assertSessionHasErrors('borrower_name');
        $this->post(route('assets.bulk-update'), $payload)->assertSessionHasNoErrors();
        foreach ($assets as $asset) {
            $this->assertSame('borrowed', $asset->fresh()->status);
            $this->assertSame('Ruang Acara', $asset->fresh()->location);
            $this->assertSame('Tim Acara', $asset->transactions()->sole()->borrower_name);
        }
        $this->post(route('assets.bulk-update'), ['asset_ids' => $assets->modelKeys(), 'status' => 'available'])->assertSessionHasNoErrors();
        foreach ($assets as $asset) {
            $this->assertSame('returned', $asset->transactions()->sole()->status);
        }
    }

    public function test_admin_bulk_borrowing_preserves_borrower_until_approval(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $asset = Asset::factory()->create(['category' => 'mobile', 'status' => 'available']);
        $this->post(route('assets.bulk-update'), ['asset_ids' => [$asset->id], 'status' => 'borrowed', 'borrower_name' => 'Tim Gudang'])->assertSessionHasNoErrors();
        $this->assertSame(0, $asset->transactions()->count());
        $log = AssetLog::where('asset_id', $asset->id)->where('status', 'pending')->sole();
        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->post(route('approvals.approve', ['type' => 'asset', 'id' => $log->id]))->assertSessionHas('success');
        $this->assertSame('Tim Gudang', $asset->transactions()->sole()->borrower_name);
    }

}
