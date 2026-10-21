<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserCreationAuthorization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
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

    public function test_admin_cannot_view_or_create_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('users.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('users.create'))->assertForbidden();
        $this->actingAs($admin)->get(route('users.two-factor.setup'))->assertForbidden();
        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Blocked User',
            'email' => 'blocked@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'blocked@test.com']);
    }

    public function test_super_admin_can_view_users(): void
    {
        $superAdmin = $this->superAdmin();
        User::factory()->count(3)->create();

        $response = $this->actingAs($superAdmin)->get(route('users.index'));

        $response->assertOk();
        $response->assertViewHas('users');
    }

    public function test_super_admin_without_totp_cannot_create_user(): void
    {
        $superAdmin = $this->superAdmin();

        $this->actingAs($superAdmin)->get(route('users.create'))
            ->assertRedirect(route('users.two-factor.setup'));

        $this->post(route('users.store'), [
            'name' => 'New Admin',
            'email' => 'newadmin@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'newadmin@test.com']);
    }

    public function test_owner_can_activate_totp_without_email_access(): void
    {
        $superAdmin = $this->superAdmin();
        $this->actingAs($superAdmin);

        $this->post(route('users.two-factor.begin-setup'), ['password' => 'password'])
            ->assertRedirect(route('users.two-factor.setup'));

        $setupPage = $this->get(route('users.two-factor.setup'));
        $setupPage->assertOk();
        $setupPage->assertDontSee('JBSWY3DPEHPK3PXP');

        $encryptedSecret = $this->app['session.store']->get('pending_totp_secret');
        $secret = Crypt::decryptString($encryptedSecret);

        $this->post(route('users.two-factor.activate'), ['code' => $this->totpCode($secret)])
            ->assertRedirect(route('users.index'));

        $superAdmin->refresh();
        $this->assertTrue($superAdmin->hasEnabledTotp());
        $this->assertDatabaseHas('users', ['id' => $superAdmin->id, 'email' => $superAdmin->email]);
        $this->assertSame('super_admin', $superAdmin->role);
        $this->assertNotSame($secret, $superAdmin->getRawOriginal('totp_secret'));
    }

    public function test_wrong_otp_does_not_authorize_user_creation(): void
    {
        $superAdmin = $this->superAdminWithTotp();

        $wrongCode = $this->totpCode($superAdmin->totp_secret) === '000000' ? '000001' : '000000';
        $this->actingAs($superAdmin)
            ->post(route('users.two-factor.authorize-creation'), ['code' => $wrongCode])
            ->assertSessionHasErrors('code');

        $this->assertDatabaseMissing('users', ['email' => 'wrong-otp@test.com']);
        $this->post(route('users.store'), [
            'name' => 'Wrong OTP',
            'email' => 'wrong-otp@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])->assertForbidden();
    }

    public function test_valid_otp_allows_exactly_one_user_creation(): void
    {
        $superAdmin = $this->superAdminWithTotp();
        $this->actingAs($superAdmin);
        $code = $this->totpCode($superAdmin->totp_secret);

        $this->post(route('users.two-factor.authorize-creation'), [
            'code' => $code,
        ])->assertRedirect(route('users.create'));
        $this->post(route('users.two-factor.authorize-creation'), ['code' => $code])
            ->assertSessionHasErrors('code');
        $this->get(route('users.create'))->assertOk();

        $payload = [
            'name' => 'New Admin',
            'email' => 'newadmin@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ];

        $this->post(route('users.store'), $payload)->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'newadmin@test.com', 'role' => 'admin']);

        $this->post(route('users.store'), [
            ...$payload,
            'email' => 'replayed@test.com',
        ])->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'replayed@test.com']);
    }

    public function test_direct_store_request_without_otp_authorization_is_rejected(): void
    {
        $superAdmin = $this->superAdminWithTotp();

        $this->actingAs($superAdmin)->post(route('users.store'), [
            'name' => 'Direct Request',
            'email' => 'direct@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'direct@test.com']);
    }

    public function test_expired_creation_authorization_is_rejected(): void
    {
        $superAdmin = $this->superAdminWithTotp();
        $this->actingAs($superAdmin);
        $token = bin2hex(random_bytes(32));
        $sessionId = $this->app['session.store']->getId();

        UserCreationAuthorization::create([
            'user_id' => $superAdmin->id,
            'session_hash' => hash('sha256', $sessionId),
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->subSecond(),
        ]);
        $this->withSession(['user_creation_token' => $token]);

        $this->post(route('users.store'), [
            'name' => 'Expired Grant',
            'email' => 'expired@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'expired@test.com']);
    }

    private function superAdminWithTotp(): User
    {
        $user = $this->superAdmin();
        $user->totp_secret = 'JBSWY3DPEHPK3PXP';
        $user->totp_enabled_at = now();
        $user->totp_last_used_counter = null;
        $user->save();

        return $user->refresh();
    }

    private function totpCode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $buffer = 0;
        $bits = 0;
        $key = '';

        foreach (str_split($secret) as $character) {
            $buffer = ($buffer << 5) | strpos($alphabet, $character);
            $bits += 5;

            if ($bits >= 8) {
                $bits -= 8;
                $key .= chr(($buffer >> $bits) & 0xff);
            }

            $buffer &= (1 << $bits) - 1;
        }

        $counter = intdiv(time(), 30);
        $hash = hash_hmac('sha1', pack('N2', intdiv($counter, 0x100000000), $counter % 0x100000000), $key, true);
        $offset = ord($hash[19]) & 0x0f;
        $binary = ((ord($hash[$offset]) & 0x7f) << 24)
            | (ord($hash[$offset + 1]) << 16)
            | (ord($hash[$offset + 2]) << 8)
            | ord($hash[$offset + 3]);

        return str_pad((string) ($binary % 1_000_000), 6, '0', STR_PAD_LEFT);
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
