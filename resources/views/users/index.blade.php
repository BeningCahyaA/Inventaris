<x-app-layout>
    <x-slot name="title">Kelola User</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola User') }}
        </h2>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="py-8" x-data="{ 
        showModal: false, 
        user: { name: '', email: '', role: '', joined: '', updated: '', edit_url: '' } 
    }"
    @keydown.window.escape="showModal = false">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex justify-end mb-5 px-4 sm:px-0">
                <a href="{{ route('users.create') }}" 
                   class="inline-flex items-center px-4 py-2.5 bg-indigo-600 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition shadow-sm">
                   <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                   Tambah User
                </a>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                {{-- Header --}}
                <div class="px-5 sm:px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-base sm:text-lg font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Daftar User
                    </h3>
                    <span class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full">{{ $users->total() }} User</span>
                </div>

                {{-- Desktop Table (hidden on mobile) --}}
                <div class="overflow-x-auto hidden md:block">
                    <table class="min-w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white border-b border-gray-100 text-[11px] text-gray-400 uppercase tracking-widest">
                                <th class="px-6 py-4 font-bold text-center w-12">No</th>
                                <th class="px-6 py-4 font-bold">Username</th>
                                <th class="px-6 py-4 font-bold">Email</th>
                                <th class="px-6 py-4 font-bold text-center">Role</th>
                                <th class="px-6 py-4 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($users as $user)
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-6 py-4 text-center text-sm text-gray-400 font-medium">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="shrink-0 w-8 h-8 rounded-full {{ $user->role == 'super_admin' ? 'bg-purple-100 text-purple-700' : 'bg-indigo-100 text-indigo-700' }} flex items-center justify-center text-xs font-bold">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <span class="text-sm font-bold text-gray-900">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 inline-flex text-[10px] leading-5 font-bold rounded-full {{ $user->role == 'super_admin' ? 'bg-purple-100 text-purple-700' : 'bg-indigo-50 text-indigo-700' }} uppercase">
                                        {{ $user->role == 'super_admin' ? 'Super Admin' : ($user->role == 'admin' ? 'Admin' : 'Staff') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button 
                                            @click="showModal = true; user = {
                                                name: '{{ addslashes($user->name) }}',
                                                email: '{{ $user->email }}',
                                                role: '{{ $user->role == 'super_admin' ? 'Super Admin' : 'Admin' }}',
                                                joined: '{{ $user->created_at->format('d M Y, H:i') }}',
                                                updated: '{{ $user->updated_at->format('d M Y, H:i') }}',
                                                edit_url: '{{ route('users.edit', $user->id) }}'
                                            }"
                                            class="p-2 text-blue-500 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-lg transition-all shadow-sm border border-blue-100 cursor-pointer" 
                                            title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        @if(auth()->id() !== $user->id)
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-red-500 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition-all shadow-sm cursor-pointer border border-red-100" title="Hapus User">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Card Layout --}}
                <div class="md:hidden divide-y divide-gray-100">
                    @forelse($users as $user)
                    <div class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            {{-- Avatar --}}
                            <div class="shrink-0 w-10 h-10 rounded-full {{ $user->role == 'super_admin' ? 'bg-purple-100 text-purple-700' : 'bg-indigo-100 text-indigo-700' }} flex items-center justify-center text-sm font-bold shadow-sm">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-bold text-gray-900 truncate">{{ $user->name }}</h4>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ $user->role == 'super_admin' ? 'bg-purple-100 text-purple-700' : 'bg-indigo-50 text-indigo-700' }} uppercase whitespace-nowrap">
                                        {{ $user->role == 'super_admin' ? 'Super Admin' : ($user->role == 'admin' ? 'Admin' : 'Staff') }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 truncate mt-0.5">{{ $user->email }}</p>
                            </div>
                            {{-- Actions --}}
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button 
                                    @click="showModal = true; user = {
                                        name: '{{ addslashes($user->name) }}',
                                        email: '{{ $user->email }}',
                                        role: '{{ $user->role == 'super_admin' ? 'Super Admin' : 'Admin' }}',
                                        joined: '{{ $user->created_at->format('d M Y, H:i') }}',
                                        updated: '{{ $user->updated_at->format('d M Y, H:i') }}',
                                        edit_url: '{{ route('users.edit', $user->id) }}'
                                    }"
                                    class="p-2 text-blue-500 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-lg transition-all shadow-sm border border-blue-100 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                @if(auth()->id() !== $user->id)
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block form-delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-500 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition-all shadow-sm cursor-pointer border border-red-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-6 py-16 text-center">
                        <h3 class="text-base font-bold text-gray-900 mb-1">Belum Ada User</h3>
                        <p class="text-sm text-gray-500">Tambahkan user baru untuk memulai.</p>
                    </div>
                    @endforelse
                </div>

                @if($users->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $users->links('pagination.custom') }}
                    </div>
                @endif
            </div>

        </div>

        {{-- User Detail Modal --}}
        <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm" @click="showModal = false"></div>
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <div x-show="showModal" x-transition class="relative bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md w-full border border-gray-100">
                    <div class="bg-gray-50 px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-extrabold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Detail User
                        </h3>
                        <button @click="showModal = false" class="text-gray-400 hover:text-red-600 transition-colors cursor-pointer">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="px-6 py-6">
                        <div class="flex justify-center mb-4">
                            <div class="h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-2xl shadow-inner">
                                <span x-text="user.name.substring(0,1).toUpperCase()"></span>
                            </div>
                        </div>
                        <div class="bg-white border border-gray-200 rounded-lg p-0 divide-y divide-gray-100">
                            <div class="flex justify-between px-4 py-3">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Username</span>
                                <span class="text-sm font-bold text-gray-900" x-text="user.name"></span>
                            </div>
                            <div class="flex justify-between px-4 py-3 bg-gray-50/30">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Email</span>
                                <span class="text-sm font-semibold text-gray-700" x-text="user.email"></span>
                            </div>
                            <div class="flex justify-between px-4 py-3">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Role</span>
                                <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-indigo-100 text-indigo-700 uppercase" x-text="user.role"></span>
                            </div>
                            <div class="flex justify-between px-4 py-3 bg-gray-50/30">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Joined</span>
                                <span class="text-sm font-semibold text-gray-700" x-text="user.joined"></span>
                            </div>
                            <div class="flex justify-between px-4 py-3">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Updated</span>
                                <span class="text-sm font-semibold text-gray-700" x-text="user.updated"></span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-gray-100">
                        <a :href="user.edit_url" class="inline-flex justify-center rounded-lg px-5 py-2.5 bg-indigo-600 text-sm font-bold text-white hover:bg-indigo-700 shadow-sm transition-colors">
                            Edit
                        </a>
                        <button @click="showModal = false" type="button" class="inline-flex justify-center rounded-lg border border-gray-300 px-5 py-2.5 bg-white text-sm font-bold text-gray-700 hover:bg-gray-50 transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>