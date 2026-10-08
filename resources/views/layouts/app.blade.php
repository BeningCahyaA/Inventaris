<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Aplikasi' }} - {{ config('app.name') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    </head>
    <body class="font-sans antialiased bg-gray-50">
        
        <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
            
            {{-- Mobile sidebar overlay --}}
            <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden" @click="sidebarOpen = false" style="display:none;"></div>

            @include('layouts.navigation')

            <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden lg:ml-64 transition-all duration-200">
                
                @if (isset($header))
                    <header class="bg-white shadow-sm sticky top-0 z-20 px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between border-b border-gray-200 overflow-visible">
                        
                        <div class="flex items-center gap-3">
                            {{-- Mobile hamburger --}}
                            <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-1 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition cursor-pointer">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            </button>
                            <div class="font-bold text-lg sm:text-xl text-gray-800 leading-tight">
                                {{ $header }}
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            {{-- Notification Bell Dropdown --}}
                            @php
                                $headerUnreadCount = Auth::user()->unreadNotifications()->count();
                                $latestNotifications = Auth::user()->unreadNotifications()->latest()->take(10)->get();
                            @endphp
                            <div class="relative" x-data="{ notifOpen: false }" @click.outside="notifOpen = false">
                                {{-- Bell Button --}}
                                <button @click="notifOpen = !notifOpen" x-ref="notifBtn"
                                        class="relative p-2 rounded-lg transition-colors duration-200 cursor-pointer"
                                        :class="notifOpen ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700'"
                                        title="Notifikasi">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                    <span id="header-notif-badge" class="{{ $headerUnreadCount > 0 ? 'inline-flex' : 'hidden' }} absolute -top-0.5 -right-0.5 items-center justify-center h-4 min-w-4 px-1 rounded-full bg-red-500 text-white text-[9px] font-bold shadow-sm ring-2 ring-white">{{ $headerUnreadCount > 99 ? '99+' : $headerUnreadCount }}</span>
                                </button>

                                {{-- Dropdown Panel --}}
                                <div x-show="notifOpen"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                     class="fixed w-80 sm:w-96 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden z-50"
                                     x-bind:style="(() => { const r = $refs.notifBtn.getBoundingClientRect(); return 'top: ' + (r.bottom + 8) + 'px; right: ' + (window.innerWidth - r.right) + 'px;'; })()"
                                     style="display: none;">

                                    {{-- Dropdown Header --}}
                                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-sm font-bold text-gray-800">Notifikasi</h3>
                                            <span id="dropdown-notif-badge" class="{{ $headerUnreadCount > 0 ? 'inline-flex' : 'hidden' }} items-center justify-center h-5 min-w-5 px-1.5 rounded-full bg-red-500 text-white text-[10px] font-bold">{{ $headerUnreadCount }}</span>
                                        </div>
                                        <form id="mark-all-read-form" method="POST" action="{{ route('notifications.read-all') }}" class="{{ $headerUnreadCount > 0 ? 'block' : 'hidden' }}">
                                            @csrf
                                            <button type="submit" class="text-[11px] text-indigo-600 hover:text-indigo-800 font-semibold transition cursor-pointer whitespace-nowrap">
                                                ✓ Tandai semua dibaca
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Notification List --}}
                                    <div id="notification-list" class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                                        @forelse($latestNotifications as $notif)
                                            @php
                                                $data   = $notif->data;
                                                $type   = $data['type'] ?? '';

                                                $iconBg    = 'bg-gray-100';
                                                $iconColor = 'text-gray-400';

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

                                            <div class="flex items-start gap-3 px-4 py-3 hover:bg-indigo-50/40 transition-colors group">
                                                {{-- Icon --}}
                                                <div class="shrink-0 w-8 h-8 rounded-full {{ $iconBg }} flex items-center justify-center mt-0.5">
                                                    @if($type === 'approval_submitted')
                                                        <svg class="w-3.5 h-3.5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                                    @elseif($type === 'approval_resolved')
                                                        @if(($data['resolution'] ?? '') === 'approved')
                                                            <svg class="w-3.5 h-3.5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        @else
                                                            <svg class="w-3.5 h-3.5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        @endif
                                                    @elseif($type === 'daily_alert_summary')
                                                        <svg class="w-3.5 h-3.5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    @else
                                                        <svg class="w-3.5 h-3.5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    @endif
                                                </div>

                                                {{-- Content --}}
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs text-gray-800 leading-snug font-semibold line-clamp-2">
                                                        {{ $data['message'] ?? '-' }}
                                                    </p>
                                                    @if(!empty($data['rejection_note']) && ($data['resolution'] ?? '') === 'rejected')
                                                        <p class="text-[10px] text-red-500 mt-0.5 italic truncate">Catatan: {{ $data['rejection_note'] }}</p>
                                                    @endif
                                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $notif->created_at->diffForHumans() }}</p>
                                                </div>

                                                {{-- Actions --}}
                                                <div class="shrink-0 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                                                        @csrf
                                                        <button type="submit" title="Tandai sudah dibaca" class="p-1 rounded text-indigo-500 hover:bg-indigo-100 hover:text-indigo-700 transition cursor-pointer">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        </button>
                                                    </form>
                                                    @if(!empty($data['link']))
                                                        <a href="{{ $data['link'] }}" title="Buka halaman terkait" class="p-1 rounded text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <div id="empty-notification-placeholder" class="px-4 py-10 text-center">
                                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                                </div>
                                                <p class="text-xs text-gray-400 font-medium">Tidak ada notifikasi baru</p>
                                            </div>
                                        @endforelse
                                    </div>

                                    {{-- Dropdown Footer --}}
                                    <div class="border-t border-gray-100 bg-gray-50">
                                        <a href="{{ route('notifications.index') }}" class="block text-center px-4 py-2.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 transition-colors">
                                            Lihat Semua Notifikasi →
                                        </a>
                                    </div>
                                </div>
                            </div>

                            {{-- Clock --}}
                            <div class="flex flex-col items-end shrink-0">
                                <div id="realtime-date" class="hidden sm:block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">
                                    Memuat tanggal…
                                </div>
                                <div class="flex items-center bg-indigo-50 text-indigo-700 font-bold px-2 sm:px-3 py-2 sm:py-3 rounded-lg">
                                    <svg class="w-3 h-3 text-indigo-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span id="realtime-time" class="text-sm sm:text-lg font-mono font-bold tabular-nums leading-none">00:00:00</span><span class="ml-1.5 text-[10px] font-semibold">WIB</span>
                                </div>
                            </div>
                        </div>
                    </header>
                @endif

                <main class="p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <input type="hidden" id="global-flash-success" value="{{ session('success') }}">
        <input type="hidden" id="global-flash-error" value="{{ session('error') }}">

        <script>
            function updateClock() {
                const now = new Date();
                
                const dateOptions = { 
                    weekday: 'long', 
                    day: 'numeric', 
                    month: 'short', 
                    year: 'numeric',
                    timeZone: 'Asia/Jakarta'
                };
                const dateString = now.toLocaleDateString('id-ID', dateOptions);

                const timeOptions = { 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    second: '2-digit',
                    hour12: false,
                    timeZone: 'Asia/Jakarta'
                };
                const timeString = now.toLocaleTimeString('en-GB', timeOptions);

                const dateEl = document.getElementById('realtime-date');
                const timeEl = document.getElementById('realtime-time');

                if (dateEl) dateEl.innerText = dateString;
                if (timeEl) timeEl.innerText = timeString; 
            }

            setInterval(updateClock, 1000);
            updateClock(); 

            document.addEventListener('DOMContentLoaded', function () {
                // Real-time Notifications with Echo
                if (window.Echo) {
                    window.Echo.private(`App.Models.User.{{ Auth::id() }}`)
                        .notification((notification) => {
                            updateNotificationUI(notification);
                            
                            // Play a subtle sound or show a toast
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 5000,
                                timerProgressBar: true,
                                didOpen: (toast) => {
                                    toast.addEventListener('mouseenter', Swal.stopTimer)
                                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                                }
                            });

                            Toast.fire({
                                icon: 'info',
                                title: 'Notifikasi Baru',
                                text: notification.message
                            });
                        });
                }

                function updateNotificationUI(notif) {
                    const notifBadge = document.getElementById('header-notif-badge');
                    const dropdownNotifBadge = document.getElementById('dropdown-notif-badge');
                    const markAllForm = document.getElementById('mark-all-read-form');
                    const notifList = document.getElementById('notification-list');
                    const emptyPlaceholder = document.getElementById('empty-notification-placeholder');

                    // Fetch current count from API to be accurate
                    fetch('/api/notifications/unread-count')
                        .then(r => r.json())
                        .then(data => {
                            if (data.count > 0) {
                                if (notifBadge) {
                                    notifBadge.classList.remove('hidden');
                                    notifBadge.innerText = data.count > 99 ? '99+' : data.count;
                                }
                                if (dropdownNotifBadge) {
                                    dropdownNotifBadge.classList.remove('hidden');
                                    dropdownNotifBadge.innerText = data.count;
                                }
                                if (markAllForm) {
                                    markAllForm.classList.remove('hidden');
                                }
                            }

                            // Update Nav Badges if present
                            const approvalBadge = document.getElementById('nav-approval-badge');
                            if (approvalBadge && data.approvals_count > 0) {
                                approvalBadge.classList.remove('hidden');
                                approvalBadge.innerText = data.approvals_count;
                            }
                        });

                    // Add the new notification to the list
                    if (notifList) {
                        if (emptyPlaceholder) emptyPlaceholder.remove();

                        const item = document.createElement('div');
                        item.className = 'flex items-start gap-3 px-4 py-3 hover:bg-indigo-50/40 transition-colors group opacity-0';
                        
                        let iconBg = 'bg-gray-100';
                        let iconColor = 'text-gray-400';
                        let svgContent = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';

                        if (notif.type === 'approval_submitted') {
                            iconBg = 'bg-red-100';
                            iconColor = 'text-red-600';
                            svgContent = '<svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>';
                        } else if (notif.type === 'approval_resolved') {
                            const isApproved = notif.resolution === 'approved';
                            iconBg = isApproved ? 'bg-emerald-100' : 'bg-red-100';
                            iconColor = isApproved ? 'text-emerald-600' : 'text-red-600';
                            if (isApproved) {
                                svgContent = '<svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                            } else {
                                svgContent = '<svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                            }
                        } else if (notif.type === 'daily_alert_summary') {
                            iconBg = 'bg-blue-100';
                            iconColor = 'text-blue-600';
                            svgContent = '<svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                        }

                        item.innerHTML = `
                            <div class="shrink-0 w-8 h-8 rounded-full ${iconBg} flex items-center justify-center mt-0.5">
                                ${svgContent}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-800 leading-snug font-semibold line-clamp-2">
                                    ${notif.message || '-'}
                                </p>
                                ${notif.rejection_note && notif.resolution === 'rejected' ? `<p class="text-[10px] text-red-500 mt-0.5 italic truncate">Catatan: ${notif.rejection_note}</p>` : ''}
                                <p class="text-[10px] text-gray-400 mt-0.5">Baru saja</p>
                            </div>
                            <div class="shrink-0 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <form method="POST" action="/notifications/${notif.id}/read">
                                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                                    <button type="submit" title="Tandai sudah dibaca" class="p-1 rounded text-indigo-500 hover:bg-indigo-100 hover:text-indigo-700 transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                                ${notif.link ? `
                                    <a href="${notif.link}" title="Buka halaman terkait" class="p-1 rounded text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                ` : ''}
                            </div>
                        `;

                        notifList.prepend(item);
                        
                        // GSAP Animation for smooth entry
                        if (window.gsap) {
                            gsap.to(item, { opacity: 1, y: 0, startAt: { y: -10 }, duration: 0.5, ease: 'back.out' });
                        } else {
                            item.classList.remove('opacity-0');
                        }
                    }
                }

                
                const successMsg = document.getElementById('global-flash-success')?.value;
                const errorMsg = document.getElementById('global-flash-error')?.value;

                if (successMsg) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: successMsg,
                        showConfirmButton: false,
                        timer: 2000
                    });
                }

                if (errorMsg) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: errorMsg,
                        showConfirmButton: false,
                        timer: 2000
                    });
                }

                document.addEventListener('submit', function(e) {
                    
                    if (e.target && e.target.classList.contains('form-logout')) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Konfirmasi Logout',
                            text: "Pastikan aktivitas Anda sudah disimpan!",
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#4f46e5', 
                            cancelButtonColor: '#9ca3af',  
                            confirmButtonText: 'Logout',
                            cancelButtonText: 'Batal',
                            reverseButtons: true,
                            focusCancel: true,
                            showClass: {
                                popup: 'animate__animated animate__zoomIn faster' 
                            },
                            hideClass: {
                                popup: 'animate__animated animate__zoomOut faster'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                localStorage.removeItem('selectedAssets');
                                e.target.submit();
                            }
                        });
                    }                    if (e.target && e.target.classList.contains('form-approve')) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Konfirmasi Persetujuan',
                            text: 'Anda yakin ingin MENYETUJUI dan menerapkan perubahan ini ke database utama?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#059669', 
                            cancelButtonColor: '#9ca3af',  
                            confirmButtonText: 'Setuju',
                            cancelButtonText: 'Batal',
                            reverseButtons: true,
                            focusCancel: true,
                            showClass: {
                                popup: 'animate__animated animate__zoomIn faster' 
                            },
                            hideClass: {
                                popup: 'animate__animated animate__zoomOut faster'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Swal.fire({
                                    title: 'Sedang Memproses...',
                                    timer: 2000,
                                    timerProgressBar: true,
                                    didOpen: () => {
                                        Swal.showLoading()
                                    }
                                });
                                e.target.submit();
                            }
                        });
                    }

                    if (e.target && e.target.classList.contains('form-delete')) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Konfirmasi Hapus',
                            text: "Data akan dihapus secara permanen!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#ef4444',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: 'Hapus',
                            cancelButtonText: 'Batal',
                            reverseButtons: true,
                            focusCancel: true,
                            showClass: {
                                popup: 'animate__animated animate__fadeInDown'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOutUp'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Swal.fire({
                                    title: 'Sedang Menghapus...',
                                    timer: 2000,
                                    timerProgressBar: true,
                                    didOpen: () => {
                                        Swal.showLoading()
                                    }
                                });
                                e.target.submit();
                            }
                        });
                    }
                });
            });
        </script>
        @stack('scripts')
    </body>
</html>
