<x-app-layout :sensitive="true">
    <x-slot name="title">Aktivasi Google Authenticator</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Aktivasi Google Authenticator</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <a href="{{ route('users.index') }}" class="text-sm font-medium text-purple-700 hover:text-purple-900">← Kembali ke Kelola User</a>

        <section class="mt-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            <h3 class="text-lg font-bold text-gray-900">Petunjuk aktivasi dan penggunaan</h3>
            <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-gray-600">
                <li>Aktivasi awal hanya dilakukan sekali oleh pemilik akun Super Admin.</li>
                <li>Masukkan password Super Admin untuk menampilkan QR, lalu pindai QR menggunakan aplikasi Google Authenticator.</li>
                <li>Masukkan kode enam digit dari aplikasi untuk menyelesaikan aktivasi. QR aktivasi berlaku selama 10 menit.</li>
                <li>Setelah aktif, Anda tidak perlu memindai QR lagi. Setiap akan menambah user, masukkan kode terbaru dari Google Authenticator pada halaman verifikasi.</li>
            </ol>

            @if (session('status'))
                <p class="mt-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-800">{{ session('status') }}</p>
            @endif

            @if ($qrCode)
                <div class="mt-6 rounded-lg bg-gray-50 p-5">
                    <p class="text-sm font-semibold text-gray-800">1. Pindai QR ini menggunakan Google Authenticator</p>
                    <div class="mt-4 flex justify-center" aria-label="QR aktivasi Google Authenticator">
                        {!! $qrCode !!}
                    </div>
                    <p class="mt-4 text-sm text-gray-600">QR berlaku selama 10 menit.</p>

                    <form action="{{ route('users.two-factor.activate') }}" method="POST" class="mt-6">
                        @csrf
                        <label for="code" class="block text-sm font-medium text-gray-700">2. Masukkan kode enam digit untuk mengaktifkan</label>
                        <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                               pattern="[0-9]{6}" maxlength="6" required
                               class="mt-2 block w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        @error('code') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        <button type="submit" class="mt-4 inline-flex items-center rounded-lg bg-purple-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-1">
                            Aktifkan 2FA
                        </button>
                    </form>
                    <form action="{{ route('users.two-factor.begin-setup') }}" method="POST" class="mt-5 border-t border-gray-200 pt-5">
                        @csrf
                        <input type="hidden" name="replace" value="1">
                        <label for="replace-password" class="block text-sm font-medium text-gray-700">Ganti QR jika diperlukan. QR sebelumnya akan langsung tidak berlaku.</label>
                        <p class="mt-1 text-sm text-gray-600">Masukkan password Super Admin untuk mengganti QR.</p>
                        <input id="replace-password" name="password" type="password" autocomplete="current-password" required
                               class="mt-3 block w-full max-w-md rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        <button type="submit" class="mt-3 inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-1">
                            Ganti QR
                        </button>
                    </form>
                </div>
            @else
                <form action="{{ route('users.two-factor.begin-setup') }}" method="POST" class="mt-6">
                    @csrf
                    <label for="password" class="block text-sm font-medium text-gray-700">Konfirmasi password Super Admin</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="mt-2 block w-full max-w-md rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    <button type="submit" class="mt-4 inline-flex items-center rounded-lg bg-purple-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-1">
                        Mulai aktivasi
                    </button>
                </form>
            @endif
        </section>
    </div>
</x-app-layout>
