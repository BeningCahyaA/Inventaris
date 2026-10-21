<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserCreationAuthorization;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserTwoFactorController extends Controller
{
    public function setup(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasEnabledTotp()) {
            return redirect()->route('users.index');
        }

        $secret = $this->pendingSecret($request);
        $qrCode = null;

        if ($secret !== null) {
            $uri = app(TotpService::class)->provisioningUri(
                $secret,
                $user->email,
                (string) config('app.name')
            );
            $qrCode = QrCode::format('svg')->size(240)->margin(1)->generate($uri);
        }

        return response()
            ->view('users.two-factor-setup', compact('qrCode'))
            ->header('Cache-Control', 'no-store, private')
            ->header('Pragma', 'no-cache')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    public function beginSetup(Request $request): RedirectResponse
    {
        if ($request->user()->hasEnabledTotp()) {
            return redirect()->route('users.index');
        }

        $validated = $request->validate([
            'password' => ['required', 'current_password'],
            'replace' => ['sometimes', 'boolean'],
        ]);

        if (! ($validated['replace'] ?? false) && $this->pendingSecret($request) !== null) {
            return redirect()->route('users.two-factor.setup')
                ->with('status', 'QR aktivasi yang sedang tampil tetap berlaku. Gunakan QR tersebut atau pilih Ganti QR.');
        }

        $request->session()->put([
            'pending_totp_secret' => Crypt::encryptString(app(TotpService::class)->generateSecret()),
            'pending_totp_expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        return redirect()->route('users.two-factor.setup');
    }

    public function activate(Request $request): RedirectResponse
    {
        if ($request->user()->hasEnabledTotp()) {
            return redirect()->route('users.index');
        }

        $code = $this->validatedCode($request);

        if ($code instanceof RedirectResponse) {
            return $code;
        }

        $secret = $this->pendingSecret($request);

        if ($secret === null) {
            return redirect()->route('users.two-factor.setup')
                ->withErrors(['code' => 'Proses aktivasi telah kedaluwarsa. Mulai kembali aktivasi 2FA.']);
        }

        $counter = app(TotpService::class)->verify($secret, $code);

        if ($counter === null) {
            return back()
                ->withErrors(['code' => 'Kode tidak cocok dengan QR aktivasi pada sesi ini. Pastikan memindai QR terbaru yang tampil di halaman ini.'])
                ->withInput($request->except('code'));
        }

        DB::transaction(function () use ($request, $secret, $counter): void {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);

            if ($user->hasEnabledTotp()) {
                abort(409, 'Google Authenticator telah aktif untuk akun ini.');
            }

            $user->totp_secret = $secret;
            $user->totp_enabled_at = now();
            $user->totp_last_used_counter = $counter;
            $user->save();
        });

        $request->session()->forget(['pending_totp_secret', 'pending_totp_expires_at']);

        return redirect()->route('users.index')->with('success', 'Google Authenticator berhasil diaktifkan.');
    }

    public function challenge(Request $request): Response|RedirectResponse
    {
        if (! $request->user()->hasEnabledTotp()) {
            return redirect()->route('users.two-factor.setup')
                ->with('status', 'Aktifkan Google Authenticator sebelum menambahkan user.');
        }

        return response()
            ->view('users.two-factor-challenge')
            ->header('Cache-Control', 'no-store, private')
            ->header('Pragma', 'no-cache')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    public function authorizeCreation(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasEnabledTotp()) {
            return redirect()->route('users.two-factor.setup');
        }

        $code = $this->validatedCode($request);

        if ($code instanceof RedirectResponse) {
            return $code;
        }

        $token = bin2hex(random_bytes(32));

        $authorized = DB::transaction(function () use ($request, $user, $code, $token): bool {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if (! $lockedUser->hasEnabledTotp()) {
                return false;
            }

            $counter = app(TotpService::class)->verify($lockedUser->totp_secret, $code);
            $lastUsedCounter = $lockedUser->totp_last_used_counter;

            if ($counter === null || ($lastUsedCounter !== null && $counter <= $lastUsedCounter)) {
                return false;
            }

            $lockedUser->totp_last_used_counter = $counter;
            $lockedUser->save();

            $sessionHash = hash('sha256', $request->session()->getId());
            UserCreationAuthorization::query()
                ->where('user_id', $lockedUser->id)
                ->where('session_hash', $sessionHash)
                ->whereNull('consumed_at')
                ->delete();

            UserCreationAuthorization::create([
                'user_id' => $lockedUser->id,
                'session_hash' => $sessionHash,
                'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addMinutes(2),
            ]);

            return true;
        });

        if (! $authorized) {
            return back()
                ->withErrors(['code' => 'Kode Google Authenticator tidak valid, kedaluwarsa, atau sudah digunakan.'])
                ->withInput($request->except('code'));
        }

        $request->session()->put('user_creation_token', $token);

        return redirect()->route('users.create');
    }

    private function pendingSecret(Request $request): ?string
    {
        $encryptedSecret = $request->session()->get('pending_totp_secret');
        $expiresAt = $request->session()->get('pending_totp_expires_at');

        if (! is_string($encryptedSecret) || ! is_int($expiresAt) || $expiresAt <= now()->timestamp) {
            $request->session()->forget(['pending_totp_secret', 'pending_totp_expires_at']);

            return null;
        }

        return Crypt::decryptString($encryptedSecret);
    }

    private function validatedCode(Request $request): string|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'digits:6'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('code'));
        }

        return $validator->validated()['code'];
    }
}
