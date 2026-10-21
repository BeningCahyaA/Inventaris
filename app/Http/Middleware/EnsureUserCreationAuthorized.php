<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\UserCreationAuthorizationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCreationAuthorized
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->role !== 'super_admin') {
            abort(403, 'ANDA TIDAK MEMILIKI AKSES KE HALAMAN INI.');
        }

        if (! app(UserCreationAuthorizationService::class)->currentFor($request, $user)) {
            abort(403, 'Verifikasi Google Authenticator diperlukan untuk menambahkan user.');
        }

        return $next($request);
    }
}
