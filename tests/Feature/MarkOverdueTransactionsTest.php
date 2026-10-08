<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkOverdueTransactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_marks_overdue_transactions(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create(['status' => 'in_use']);

        // Transaksi yang sudah jatuh tempo
        AssetTransaction::create([
            'asset_id' => $asset->id,
            'borrower_user_id' => $user->id,
            'borrower_name' => 'Tester',
            'borrowed_at' => now()->subDays(5),
            'due_at' => now()->subDays(1),
            'created_by' => $user->id,
            'status' => 'borrowed',
        ]);

        $this->artisan('assets:mark-overdue')
            ->expectsOutputToContain('1 transaksi ditandai sebagai OVERDUE')
            ->assertExitCode(0);

        $this->assertDatabaseHas('asset_transactions', [
            'asset_id' => $asset->id,
            'status' => 'overdue',
        ]);
    }

    public function test_does_not_mark_non_overdue_transactions(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create(['status' => 'in_use']);

        // Transaksi yang belum jatuh tempo
        AssetTransaction::create([
            'asset_id' => $asset->id,
            'borrower_user_id' => $user->id,
            'borrower_name' => 'Tester',
            'borrowed_at' => now(),
            'due_at' => now()->addDays(3),
            'created_by' => $user->id,
            'status' => 'borrowed',
        ]);

        $this->artisan('assets:mark-overdue')
            ->expectsOutputToContain('Tidak ada transaksi overdue')
            ->assertExitCode(0);

        $this->assertDatabaseHas('asset_transactions', [
            'asset_id' => $asset->id,
            'status' => 'borrowed',
        ]);
    }
}
