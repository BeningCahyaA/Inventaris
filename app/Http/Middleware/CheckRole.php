<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    // Gunakan ...$roles (splat operator) agar bisa menerima banyak role yang dipisah koma
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Cek apakah role user sesuai (Super Admin / Admin)
        if (!in_array($request->user()->role, $roles)) {
            abort(403, 'ANDA TIDAK MEMILIKI AKSES KE HALAMAN INI.');
        }

        return $next($request);
    }
}