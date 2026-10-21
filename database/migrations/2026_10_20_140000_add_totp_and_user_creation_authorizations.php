<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'totp_secret')) {
                $table->text('totp_secret')->nullable();
            }

            if (! Schema::hasColumn('users', 'totp_enabled_at')) {
                $table->timestamp('totp_enabled_at')->nullable();
            }

            if (! Schema::hasColumn('users', 'totp_last_used_counter')) {
                $table->unsignedBigInteger('totp_last_used_counter')->nullable();
            }
        });

        if (! Schema::hasTable('user_creation_authorizations')) {
            Schema::create('user_creation_authorizations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->char('session_hash', 64);
                $table->char('token_hash', 64);
                $table->timestamp('expires_at');
                $table->timestamp('consumed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasIndex('user_creation_authorizations', 'uca_token_hash_uq')) {
            Schema::table('user_creation_authorizations', function (Blueprint $table) {
                $table->unique('token_hash', 'uca_token_hash_uq');
            });
        }

        if (! Schema::hasIndex('user_creation_authorizations', 'uca_user_session_exp_idx')) {
            Schema::table('user_creation_authorizations', function (Blueprint $table) {
                $table->index(['user_id', 'session_hash', 'expires_at'], 'uca_user_session_exp_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_creation_authorizations');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['totp_secret', 'totp_enabled_at', 'totp_last_used_counter']);
        });
    }
};
