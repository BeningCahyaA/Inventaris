<x-app-layout>
    <x-slot name="title">Kelola User</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola User') }}
        </h2>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="management-page users-page" x-data="{
        showModal: false,
        user: { name: '', email: '', role: '', joined: '', updated: '', edit_url: '' }
    }"
    @keydown.window.escape="showModal = false">
        <div class="max-w-7xl mx-auto">

            <div class="management-intro flex flex-wrap justify-between items-center gap-4 mb-5">
                <div><h3>Kelola pengguna</h3><p>Atur akun dan hak akses pengguna aplikasi.</p></div><a href="{{ route('users.create') }}"
                   class="inline-flex items-center px-4 py-2.5 bg-indigo-600 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition shadow-sm">
                   <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                   Tambah User
                </a>
            </div>

            <div class="management-panel bg-white overflow-hidden">
                {{-- Header --}}
                <div class="management-panel-heading">
                    <h3 class="text-base sm:text-lg font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Daftar User
                    </h3>
                    <span class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full">{{ $users->total() }} User</span>
                </div>

                {{-- Desktop Table (hidden on mobile) --}}
                <div class="overflow-x-auto hidden md:block">
                    <table class="management-table users-table w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white border-b border-gray-100 text-[11px] text-gray-400 uppercase tracking-widest">
                                <th class="px-6 py-4 font-bold text-center w-12">No</th>
                                <th class="px-6 py-4 font-bold">Nama pengguna</th>
                                <th class="px-6 py-4 font-bold">Email</th>
                                <th class="px-6 py-4 font-bold text-center">Hak akses</th>
                                <th class="px-6 py-4 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($users as $user)
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-6 py-4 text-center text-sm text-gray-400 font-medium">{{ ($users->firstItem() ?? 1) + $loop->index }}</td>
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
                                            title="Detail pengguna" aria-label="Detail pengguna">
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
                            @empty
                            <tr><td colspan="5" class="management-empty"><strong>Belum ada pengguna</strong><p>Tambahkan pengguna baru untuk memulai.</p></td></tr>
                            @endforelse
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
        <div x-show="showModal" x-cloak class="user-detail-overlay fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="user-detail-title">
            <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="showModal = false"></div>
            <div class="user-detail-position">
                <div x-show="showModal" x-transition class="user-detail-panel relative bg-white shadow-2xl">
                    <div class="user-detail-heading">
                        <div><h3 id="user-detail-title">Detail pengguna</h3><p>Informasi akun dan hak akses pengguna.</p></div>
                        <button type="button" @click="showModal = false" class="user-detail-close" aria-label="Tutup detail pengguna">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="user-detail-body">
                        <div class="user-detail-profile">
                            <div class="user-detail-avatar" aria-hidden="true" x-text="user.name.substring(0,1).toUpperCase()"></div>
                            <div class="user-detail-identity"><h4 x-text="user.name"></h4><span class="user-detail-role" :class="user.role === 'Super Admin' ? 'user-detail-role-super' : ''" x-text="user.role"></span></div>
                        </div>
                        <dl class="user-detail-info">
                            <div><dt>Nama pengguna</dt><dd x-text="user.name"></dd></div>
                            <div><dt>Email</dt><dd x-text="user.email"></dd></div>
                            <div><dt>Bergabung</dt><dd x-text="user.joined"></dd></div>
                            <div><dt>Terakhir diperbarui</dt><dd x-text="user.updated"></dd></div>
                        </dl>
                    </div>
                    <div class="user-form-actions">
                        <button @click="showModal = false" type="button" class="user-form-cancel">Tutup</button>
                        <a :href="user.edit_url" class="user-form-submit">Edit pengguna</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
