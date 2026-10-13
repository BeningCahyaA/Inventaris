<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetAttachment;
use App\Models\AssetLog;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetAttachmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Isolate file lifecycle tests from the configured application database.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        Storage::fake('public');
        Notification::fake();
        // Dashboard observers use MySQL-only chart queries, unrelated to attachments.
        Event::fake();
    }

    private function attachment(Asset $asset): AssetAttachment
    {
        $path = 'asset-attachments/'.$asset->id.'.png';
        Storage::disk('public')->put($path, 'test-image');

        return $asset->attachments()->create([
            'file_path' => $path, 'file_name' => 'foto.png', 'file_type' => 'application/octet-stream',
        ]);
    }

    public function test_photo_is_served_without_a_storage_link_and_old_mime_types_are_recognized(): void
    {
        $asset = Asset::factory()->create();
        $attachment = $this->attachment($asset);
        $this->assertTrue($attachment->isImage());
        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('assets.attachments.show', [$asset->id, $attachment->id]));
        $response->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertSame('test-image', $response->streamedContent());
    }

    public function test_super_admin_can_delete_a_photo_without_submitting_other_asset_fields(): void
    {
        $asset = Asset::factory()->create();
        $attachment = $this->attachment($asset);
        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->delete(route('assets.attachments.destroy', [$asset->id, $attachment->id]))
            ->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseMissing('asset_attachments', ['id' => $attachment->id]);
        Storage::disk('public')->assertMissing($attachment->file_path);
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'name' => $asset->name]);
    }

    public function test_admin_deletion_is_applied_only_after_approval(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $asset = Asset::factory()->create();
        $attachment = $this->attachment($asset);
        $this->actingAs($admin)->delete(route('assets.attachments.destroy', [$asset->id, $attachment->id]))
            ->assertRedirect()->assertSessionHas('success');
        Storage::disk('public')->assertExists($attachment->file_path);
        $this->assertDatabaseHas('asset_attachments', ['id' => $attachment->id]);
        $log = AssetLog::where('asset_id', $asset->id)->where('status', 'pending')->firstOrFail();
        $this->actingAs($superAdmin)->post(route('approvals.approve', ['type' => 'asset', 'id' => $log->id]))
            ->assertRedirect()->assertSessionHas('success');
        Storage::disk('public')->assertMissing($attachment->file_path);
        $this->assertDatabaseMissing('asset_attachments', ['id' => $attachment->id]);
    }

    public function test_attachment_cannot_be_read_or_deleted_through_another_asset(): void
    {
        $asset = Asset::factory()->create();
        $other = Asset::factory()->create();
        $attachment = $this->attachment($asset);
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $url = route('assets.attachments.show', [$other->id, $attachment->id]);
        $this->get($url)->assertNotFound();
        $this->delete($url)->assertNotFound();
        Storage::disk('public')->assertExists($attachment->file_path);
    }
}
