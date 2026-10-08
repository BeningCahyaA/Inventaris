<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
                <div class="p-8 bg-white border-b border-gray-200">
                    
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf

                        <div class="mb-5">
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Username*</label>
                            <input type="text" name="name" id="name" required
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                                placeholder="Masukkan nama pengguna">
                            @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-5">
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email*</label>
                            <input type="email" name="email" id="email" required
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                                placeholder="contoh@email.com">
                            @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                            <div>
                                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password*</label>
                                <input type="password" name="password" id="password" required
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200">
                                @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">Confirm Password*</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" required
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200">
                            </div>
                        </div>

                        <div class="mb-8">
                            <label for="role" class="block text-sm font-semibold text-gray-700 mb-1">Role</label>
                            <select name="role" id="role"
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 cursor-pointer">
                                <option value="" disabled selected>Pilih Role</option>
                                <option value="super_admin">Super Admin</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="submit" 
                                class="inline-flex items-center px-6 py-2.5 bg-indigo-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md cursor-pointer">
                                Simpan
                            </button>

                            <a href="{{ route('users.index') }}" 
                                class="inline-flex items-center px-6 py-2.5 bg-gray-500 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                                Batal
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>