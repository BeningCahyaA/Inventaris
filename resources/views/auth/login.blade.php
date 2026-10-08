<x-guest-layout>
    <x-slot name="title">Login Panel</x-slot>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="text-center mb-8 mt-2">
        <h2 class="text-2xl font-bold bg-linear-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
            Asset Tracker Vodeco
        </h2>
        <p class="text-[13px] text-gray-500 mt-2">
            Masuk Menggunakan Email dan Password
        </p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-5">
            <label for="email" class="block text-[13px] font-bold text-gray-800 mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                placeholder="Masukkan Email"
                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 placeholder-gray-400">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mb-8">
            <label for="password" class="block text-[13px] font-bold text-gray-800 mb-1.5">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                placeholder="••••••••"
                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 placeholder-gray-400">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <button type="submit" 
            class="w-full py-3 px-4 rounded-lg text-white font-bold text-sm tracking-wide bg-linear-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 focus:ring-4 focus:ring-purple-300 transition-all shadow-md cursor-pointer">
            Sign In
        </button>
    </form>
</x-guest-layout>