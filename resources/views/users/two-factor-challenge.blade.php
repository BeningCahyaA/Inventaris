<x-app-layout :sensitive="true">
    <x-slot name="title">Verifikasi Tambah User</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Verifikasi Google Authenticator</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <a href="{{ route('users.index') }}" class="text-sm font-medium text-purple-700 hover:text-purple-900">← Kembali ke Kelola User</a>

        <section class="mt-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            <h3 class="text-lg font-bold text-gray-900">Konfirmasi tindakan tambah user</h3>
            <p class="mt-2 text-sm text-gray-600">Masukkan kode enam digit dari Google Authenticator. Verifikasi berlaku untuk satu tindakan pembuatan user dan kedaluwarsa dalam dua menit.</p>

            <form action="{{ route('users.two-factor.authorize-creation') }}" method="POST" class="mt-6">
                @csrf
                <label for="code" class="block text-sm font-medium text-gray-700">Kode OTP</label>
                <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                       pattern="[0-9]{6}" maxlength="6" required autofocus
                       class="mt-2 block w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                @error('code') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                <button type="submit" class="mt-4 inline-flex items-center rounded-lg bg-purple-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-1">
                    Verifikasi dan lanjutkan
                </button>
            </form>
        </section>
    </div>
</x-app-layout>
