<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::orderByRaw("CASE WHEN role = 'super_admin' THEN 1 WHEN role = 'admin' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        Log::info("User (ID: " . Auth::id() . ") created user: " . $validated['email']);
        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan!');
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        // Cegah Super Admin menurunkan jabatannya sendiri
        if (Auth::id() == $user->id && $validated['role'] != 'super_admin') {
            return back()->with('error', 'Demi keamanan, Anda tidak bisa menurunkan jabatan sendiri. Silakan buat Super Admin lain dulu.');
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        // Update password HANYA JIKA diisi
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        Log::info("User (ID: " . Auth::id() . ") updated user: " . $user->email);
        return redirect()->route('users.index')->with('success', 'Data user diperbarui!');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (Auth::id() == $user->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun Super Admin!');
        }

        if ($user->role === 'super_admin') {
            $superAdminCount = User::where('role', 'super_admin')->count();
            if ($superAdminCount <= 1) {
                return back()->with('error', 'Tidak dapat menghapus Super Admin terakhir. Sistem harus memiliki minimal satu Super Admin!');
            }
        }

        $user->delete();

        Log::info("User (ID: " . Auth::id() . ") deleted user: " . $user->email);
        return back()->with('success', 'User berhasil dihapus.');
    }
}
