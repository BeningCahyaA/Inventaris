<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    public function test_user_index_requires_super_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('users.index'))->assertForbidden();
    }

    public function test_super_admin_can_view_users(): void
    {
        $superAdmin = $this->superAdmin();
        User::factory()->count(3)->create();

        $response = $this->actingAs($superAdmin)->get(route('users.index'));

        $response->assertOk();
        $response->assertViewHas('users');
    }

    public function test_super_admin_can_create_user(): void
    {
        $superAdmin = $this->superAdmin();

        $response = $this->actingAs($superAdmin)->post(route('users.store'), [
            'name' => 'New Admin',
            'email' => 'newadmin@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'newadmin@test.com', 'role' => 'admin']);
    }

    public function test_super_admin_can_update_user(): void
    {
        $superAdmin = $this->superAdmin();
        $user = User::factory()->create(['role' => 'admin', 'name' => 'Old Name']);

        $response = $this->actingAs($superAdmin)->put(route('users.update', $user), [
            'name' => 'New Name',
            'email' => $user->email,
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    }

    public function test_super_admin_cannot_demote_self(): void
    {
        $superAdmin = $this->superAdmin();

        $response = $this->actingAs($superAdmin)->put(route('users.update', $superAdmin), [
            'name' => $superAdmin->name,
            'email' => $superAdmin->email,
            'role' => 'admin',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $superAdmin->id, 'role' => 'super_admin']);
    }

    public function test_super_admin_cannot_delete_self(): void
    {
        $superAdmin = $this->superAdmin();

        $response = $this->actingAs($superAdmin)->delete(route('users.destroy', $superAdmin));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }

    public function test_cannot_delete_last_super_admin(): void
    {
        $superAdmin = $this->superAdmin();
        $anotherSuperAdmin = User::factory()->create(['role' => 'super_admin']);

        // Menghapus satu super admin OK
        $this->actingAs($superAdmin)->delete(route('users.destroy', $anotherSuperAdmin))->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $anotherSuperAdmin->id]);

        // Membuat user baru untuk coba hapus super admin terakhir lewat user lain
        $admin = User::factory()->create(['role' => 'admin']);
        // Re-create super admin scenario  
        $lastSuperAdmin = User::factory()->create(['role' => 'super_admin']);
        
        // Delete semua super admin lain dulu
        User::where('role', 'super_admin')->where('id', '!=', $lastSuperAdmin->id)->delete();

        $response = $this->actingAs($lastSuperAdmin)->delete(route('users.destroy', $lastSuperAdmin));
        $response->assertSessionHas('error');
    }
}
