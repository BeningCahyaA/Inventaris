<x-guest-layout>
    <x-slot name="title">Verifikasi Email</x-slot>

    <div class="mb-4 text-sm text-gray-600">
        Terima kasih sudah mendaftar. Sebelum melanjutkan, silakan verifikasi email Anda melalui link yang sudah dikirim.
        Jika belum menerima email, klik tombol di bawah untuk kirim ulang.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 text-sm font-medium text-green-600">
            Link verifikasi baru sudah dikirim ke email Anda.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <x-primary-button>
                Kirim Ulang Email Verifikasi
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Logout
            </button>
        </form>
    </div>
</x-guest-layout>
