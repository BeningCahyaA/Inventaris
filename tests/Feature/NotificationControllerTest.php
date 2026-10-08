<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ApprovalResolvedNotification;
use App\Notifications\ApprovalSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
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

    /** Seedd a real database notification for the given user */
    private function seedNotification(User $user, array $data = []): string
    {
        $id = (string) \Illuminate\Support\Str::uuid();

        \DB::table('notifications')->insert([
            'id'              => $id,
            'type'            => ApprovalSubmittedNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
            'data'            => json_encode(array_merge([
                'type'    => 'approval_submitted',
                'message' => 'Ada tiket baru',
                'link'    => route('approvals.index'),
            ], $data)),
            'read_at'         => null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return $id;
    }

    // ------------------------------------------------------------------ tests

    public function test_guest_cannot_access_notifications(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_notification_index(): void
    {
        $user = $this->admin();
        $this->actingAs($user)->get(route('notifications.index'))->assertOk();
    }

    public function test_unread_tab_only_shows_unread_notifications(): void
    {
        $user = $this->admin();

        $unreadId = $this->seedNotification($user, ['message' => 'Belum dibaca']);
        $readId   = $this->seedNotification($user, ['message' => 'Sudah dibaca']);

        // Mark second one as read
        \DB::table('notifications')->where('id', $readId)->update(['read_at' => now()]);

        $response = $this->actingAs($user)->get(route('notifications.index', ['tab' => 'unread']));

        $response->assertOk();
        $response->assertSee('Belum dibaca');
        $response->assertDontSee('Sudah dibaca');
    }

    public function test_all_tab_shows_all_notifications(): void
    {
        $user = $this->admin();

        $this->seedNotification($user, ['message' => 'Notif A']);
        $readId = $this->seedNotification($user, ['message' => 'Notif B']);
        \DB::table('notifications')->where('id', $readId)->update(['read_at' => now()]);

        $response = $this->actingAs($user)->get(route('notifications.index', ['tab' => 'all']));

        $response->assertOk();
        $response->assertSee('Notif A');
        $response->assertSee('Notif B');
    }

    public function test_mark_single_notification_as_read_and_redirect(): void
    {
        $user = $this->admin();
        $id   = $this->seedNotification($user);

        $response = $this->actingAs($user)
            ->post(route('notifications.read', $id));

        $response->assertRedirect();

        $this->assertNotNull(
            \DB::table('notifications')->where('id', $id)->value('read_at')
        );
    }

    public function test_cannot_mark_another_users_notification(): void
    {
        $userA = $this->admin();
        $userB = $this->admin();
        $id    = $this->seedNotification($userB);

        // userA tries to mark userB's notification → scoped query returns 404
        $this->actingAs($userA)
            ->post(route('notifications.read', $id))
            ->assertNotFound();

        $this->assertNull(
            \DB::table('notifications')->where('id', $id)->value('read_at')
        );
    }

    public function test_mark_all_read_marks_only_own_notifications(): void
    {
        $userA = $this->admin();
        $userB = $this->admin();

        $idA = $this->seedNotification($userA);
        $idB = $this->seedNotification($userB);

        $this->actingAs($userA)->post(route('notifications.read-all'))->assertRedirect();

        $this->assertNotNull(\DB::table('notifications')->where('id', $idA)->value('read_at'));
        $this->assertNull(\DB::table('notifications')->where('id', $idB)->value('read_at'));
    }

    public function test_unread_count_endpoint_returns_correct_number(): void
    {
        $user = $this->admin();

        $this->seedNotification($user);
        $this->seedNotification($user);
        $readId = $this->seedNotification($user);
        \DB::table('notifications')->where('id', $readId)->update(['read_at' => now()]);

        $response = $this->actingAs($user)->getJson(route('notifications.unread-count'));

        $response->assertOk()->assertJson(['count' => 2]);
    }

    public function test_unread_count_is_zero_when_no_notifications(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->getJson(route('notifications.unread-count'));

        $response->assertOk()->assertJson(['count' => 0]);
    }

    public function test_notifications_are_user_scoped(): void
    {
        $userA = $this->admin();
        $userB = $this->admin();

        $this->seedNotification($userA, ['message' => 'Hanya untuk A']);
        $this->seedNotification($userB, ['message' => 'Hanya untuk B']);

        $responseA = $this->actingAs($userA)->get(route('notifications.index', ['tab' => 'all']));
        $responseA->assertSee('Hanya untuk A');
        $responseA->assertDontSee('Hanya untuk B');
    }

    public function test_approval_submitted_notification_sent_to_super_admin(): void
    {
        Notification::fake();

        $superAdmin = $this->superAdmin();
        $admin      = $this->admin();
        $category   = \App\Models\Category::create(['name' => 'Mobile', 'slug' => 'mobile', 'prefix' => 'M']);
        $asset      = \App\Models\Asset::factory()->create([
            'name'     => 'Laptop Test',
            'status'   => 'available',
            'category' => $category->slug,
        ]);

        $this->actingAs($admin)->put(route('assets.update', $asset->id), [
            'name'          => 'Laptop Updated',
            'category'      => $category->slug,
            'status'        => 'available',
            'description'   => $asset->description ?? '',
            'purchase_date' => $asset->purchase_date->format('Y-m-d'),
            'condition'     => 'baik',
        ]);

        Notification::assertSentTo($superAdmin, ApprovalSubmittedNotification::class);
    }

    public function test_approval_resolved_notification_sent_to_requester(): void
    {
        Notification::fake();

        $superAdmin = $this->superAdmin();
        $admin      = $this->admin();
        $asset      = \App\Models\Asset::factory()->create();

        $log = \App\Models\AssetLog::create([
            'asset_id' => $asset->id,
            'user_id'  => $admin->id,
            'action'   => 'update',
            'old_data' => $asset->toArray(),
            'new_data' => array_merge($asset->toArray(), ['name' => 'New Name']),
            'status'   => 'pending',
        ]);

        $this->actingAs($superAdmin)
            ->post(route('approvals.approve', ['type' => 'asset', 'id' => $log->id]));

        Notification::assertSentTo($admin, ApprovalResolvedNotification::class);
    }
}
