<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\UserCreationAuthorization;
use Illuminate\Http\Request;

class UserCreationAuthorizationService
{
    public function currentFor(Request $request, User $user): ?UserCreationAuthorization
    {
        $token = $request->session()->get('user_creation_token');

        if (! is_string($token) || ! $user->hasEnabledTotp()) {
            return null;
        }

        return UserCreationAuthorization::query()
            ->where('user_id', $user->id)
            ->where('session_hash', hash('sha256', $request->session()->getId()))
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function lockCurrentFor(Request $request, User $user): ?UserCreationAuthorization
    {
        $token = $request->session()->get('user_creation_token');

        if (! is_string($token) || ! $user->hasEnabledTotp()) {
            return null;
        }

        return UserCreationAuthorization::query()
            ->where('user_id', $user->id)
            ->where('session_hash', hash('sha256', $request->session()->getId()))
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->lockForUpdate()
            ->first();
    }
}
