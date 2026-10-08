<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $isSafeEnvironment = app()->environment(['local', 'testing']);

        $superPassword = $isSafeEnvironment
            ? 'super-123'
            : env('SEED_SUPER_ADMIN_PASSWORD');

        $adminPassword = $isSafeEnvironment
            ? 'admin-123'
            : env('SEED_ADMIN_PASSWORD');

        if (! $isSafeEnvironment && (! $superPassword || ! $adminPassword)) {
            throw new RuntimeException('Seeder user admin membutuhkan env SEED_SUPER_ADMIN_PASSWORD dan SEED_ADMIN_PASSWORD di environment non-local/testing.');
        }

        // Akun Super Admin
        User::updateOrCreate(
            ['email' => 'super@vodeco.co.id'],
            [
                'name' => 'Super Admin',
                'role' => 'super_admin',
                'password' => Hash::make($superPassword),
                'email_verified_at' => now(),
            ]
        );

        // Akun Admin
        User::updateOrCreate(
            ['email' => 'admin@vodeco.co.id'],
            [
                'name' => 'Admin',
                'role' => 'admin',
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
            ]
        );

        // Asset Seeder
        $this->call([
            CategorySeeder::class,
            AssetSeeder::class,
        ]);
    }
}
