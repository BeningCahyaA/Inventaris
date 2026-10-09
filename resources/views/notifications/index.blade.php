<x-app-layout>
    <x-slot name="title">Pusat Notifikasi</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pusat Notifikasi</h2>
    </x-slot>

    <div class="notifications-page">
        <div class="max-w-7xl mx-auto space-y-4">

            <div class="management-intro"><div><h3>Notifikasi Anda</h3><p>Pantau pembaruan aset, pengajuan, dan hasil persetujuan.</p></div></div>
            {{-- Header bar --}}
            <div class="notification-toolbar flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-center gap-2 overflow-x-auto pb-2 sm:pb-0 hide-scrollbar scroll-smooth">
                    <a href="{{ route('notifications.index', ['tab' => 'unread']) }}"
                       class="whitespace-nowrap px-4 py-2 rounded-lg text-sm font-semibold transition
                              {{ $tab === 'unread' ? 'bg-indigo-600 text-white shadow' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                        Belum Dibaca
                        @if($unreadCount > 0)
                            <span class="ml-1 inline-flex items-center justify-center h-4 min-w-[1rem] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('notifications.index', ['tab' => 'read']) }}"
                       class="whitespace-nowrap px-4 py-2 rounded-lg text-sm font-semibold transition
                              {{ $tab === 'read' ? 'bg-indigo-600 text-white shadow' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                        Sudah Dibaca
                    </a>
                    <a href="{{ route('notifications.index', ['tab' => 'all']) }}"
                       class="whitespace-nowrap px-4 py-2 rounded-lg text-sm font-semibold transition
                              {{ $tab === 'all' ? 'bg-indigo-600 text-white shadow' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                        Semua
                    </a>
                </div>

                <div class="notification-toolbar-actions flex flex-wrap items-center gap-3">
                    @if($unreadCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            <button type="submit"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold transition cursor-pointer flex items-center gap-1">
                                ✓ Tandai semua dibaca
                            </button>
                        </form>
                    @endif

                    @if($notifications->total() > 0)
                        @if($unreadCount > 0)
                        <span class="text-gray-300">|</span>
                        @endif
                        <form method="POST" action="{{ route('notifications.delete-all') }}" id="delete-all-form">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    onclick="confirmDeleteAll()"
                                    class="text-xs text-red-600 hover:text-red-800 font-semibold transition cursor-pointer flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Hapus semua
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- List --}}
            @if($notifications->total() > 0)
                <div class="notification-feed management-panel">
                    @foreach($notifications as $notif)
                        @php
                            $data   = $notif->data;
                            $isRead = !is_null($notif->read_at);
                            $type   = $data['type'] ?? '';

                            $iconBg   = 'bg-gray-100';
                            $iconColor= 'text-gray-400';

                            if ($type === 'approval_submitted') {
                                $iconBg    = 'bg-red-100';
                                $iconColor = 'text-red-600';
                            } elseif ($type === 'approval_resolved') {
                                $resolution = $data['resolution'] ?? '';
                                $iconBg     = $resolution === 'approved' ? 'bg-emerald-100' : 'bg-red-100';
                                $iconColor  = $resolution === 'approved' ? 'text-emerald-600' : 'text-red-600';
                            } elseif ($type === 'daily_alert_summary') {
                                $iconBg    = 'bg-blue-100';
                                $iconColor = 'text-blue-600';
                            }
                        @endphp

                        <div class="notification-card flex items-start gap-4 transition
                                    {{ $isRead ? 'notification-read' : 'notification-unread' }}">

                            {{-- Icon --}}
                            <div class="shrink-0 w-9 h-9 rounded-full {{ $iconBg }} flex items-center justify-center mt-0.5">
                                @if($type === 'approval_submitted')
                                    <svg class="w-4 h-4 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                @elseif($type === 'approval_resolved')
                                    @if(($data['resolution'] ?? '') === 'approved')
                                        <svg class="w-4 h-4 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @else
                                        <svg class="w-4 h-4 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                @else
                                    <svg class="w-4 h-4 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-800 leading-snug {{ $isRead ? '' : 'font-semibold' }}">
                                    {{ $data['message'] ?? '-' }}
                                </p>
                                @if(!empty($data['rejection_note']) && ($data['resolution'] ?? '') === 'rejected')
                                    <p class="text-xs text-red-500 mt-1 italic">Catatan: {{ $data['rejection_note'] }}</p>
                                @endif
                                <p class="text-[11px] text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                            </div>

                            {{-- Action --}}
                            <div class="notification-actions shrink-0 flex items-center gap-2">
                                @if(!$isRead)
                                    <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                                        @csrf
                                        <button type="submit"
                                                title="Tandai sudah dibaca" aria-label="Tandai sudah dibaca"
                                                class="text-indigo-500 hover:text-indigo-700 transition cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    </form>
                                @endif
                                @if(!empty($data['link']))
                                    <a href="{{ $data['link'] }}"
                                       class="text-gray-400 hover:text-gray-600 transition"
                                       title="Buka halaman terkait" aria-label="Buka halaman terkait">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('notifications.destroy', $notif->id) }}" id="delete-notif-{{ $notif->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete('{{ $notif->id }}')" class="text-red-400 hover:text-red-600 transition cursor-pointer" title="Hapus notifikasi" aria-label="Hapus notifikasi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $notifications->links('pagination.custom') }}
                </div>
            @else
                <div class="bg-white border border-gray-200 rounded-xl p-12 flex flex-col items-center text-gray-400 shadow-sm">
                    <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <p class="text-sm">Tidak ada notifikasi.</p>
                </div>
            @endif

        </div>
    </div>

    @push('scripts')
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Hapus Notifikasi?',
                text: "Notifikasi ini akan dihapus permanen dari sistem.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-notif-' + id).submit();
                }
            });
        }
        
        function confirmDeleteAll() {
            Swal.fire({
                title: 'Hapus Semua Notifikasi?',
                text: "Penghapusan ini bersifat permanen dan seluruh data notifikasi milik Anda di database akan hilang.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Hapus semua',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-all-form').submit();
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
