<x-app-layout>
    <x-slot name="title">Edit User</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit User : {{ $user->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
                <div class="p-8 bg-white border-b border-gray-200">

                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf @method('PUT')
                        
                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Username*</label>
                            <input type="text" name="name" value="{{ $user->name }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200" required>
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Email*</label>
                            <input type="email" name="email" value="{{ $user->email }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200" required>
                        </div>

                        <div class="mb-4 bg-yellow-50 p-4 rounded border border-yellow-200">
                            <label class="block text-sm font-semibold text-yellow-800 mb-1">Ubah Password</label>
                            <div class="grid grid-cols-2 gap-4">
                                <input type="password" name="password" placeholder="Password Baru" class="w-full border rounded p-2">
                                <input type="password" name="password_confirmation" placeholder="Konfirmasi Password Baru" class="w-full border rounded p-2">
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block font-bold mb-1">Role</label>
                            <select name="role" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 cursor-pointer">
                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="super_admin" {{ $user->role == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            </select>
                        </div>

                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md cursor-pointer">Perbarui</button>
                        <a href="{{ route('users.index') }}" class="inline-flex items-center px-6 py-2.5 bg-gray-500 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm cursor-pointer">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>