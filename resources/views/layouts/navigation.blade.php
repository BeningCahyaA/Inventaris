<aside class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 flex flex-col h-screen transition-transform duration-300 ease-in-out transform"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

    <div class="h-20 flex items-center justify-between px-6 border-b border-gray-100">
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset('img/Logo-Vodeco-Media-Group-300x87.png') }}" alt="Logo Perusahaan" class="h-10 w-auto object-contain">
        </a>
        {{-- Mobile close button --}}
        <button @click="sidebarOpen = false" class="lg:hidden p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1">

        <a href="{{ route('dashboard') }}" @click="sidebarOpen = false"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 group {{ request()->routeIs('dashboard') ? 'bg-purple-50 text-purple-700 font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-purple-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('assets.index') }}" @click="sidebarOpen = false"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 group {{ request()->routeIs('assets.*') && !request()->routeIs('assets.print*') && !request()->routeIs('assets.history') && !request()->routeIs('assets.archive') ? 'bg-purple-50 text-purple-700 font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="w-5 h-5 {{ request()->routeIs('assets.*') && !request()->routeIs('assets.print*') && !request()->routeIs('assets.history') && !request()->routeIs('assets.archive') ? 'text-purple-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <span>Aset</span>
        </a>

        <a href="{{ route('scanner.index') }}" @click="sidebarOpen = false"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 group {{ request()->routeIs('scanner.*') ? 'bg-purple-50 text-purple-700 font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="w-5 h-5 {{ request()->routeIs('scanner.*') ? 'text-purple-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7V4h3m10 0h3v3M4 17v3h3m10 0h3v-3M7 8v8m3-8v8m4-8v8m3-8v8"></path>
            </svg>
            <span>Barcode Scanner</span>
        </a>

        @if(Auth::user()->role === 'super_admin')

        @php $pendingCount = \App\Models\AssetLog::where('status', 'pending')->count() + \App\Models\CategoryLog::where('status', 'pending')->count(); @endphp
        <a href="{{ route('approvals.index') }}" @click="sidebarOpen = false"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 group {{ request()->routeIs('approvals.*') ? 'bg-purple-50 text-purple-700 font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="w-5 h-5 {{ request()->routeIs('approvals.*') ? 'text-purple-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>Persetujuan</span>
            <span id="nav-approval-badge" class="ml-auto {{ $pendingCount > 0 ? 'inline-flex' : 'hidden' }} items-center justify-center h-5 min-w-[1.25rem] px-1.5 rounded-full bg-red-500 text-white text-[10px] font-bold shadow-sm">{{ $pendingCount }}</span>
        </a>

        @endif

        @if(Auth::user()->role === 'admin')
        @php $myPendingCount = \App\Models\AssetLog::where('user_id', Auth::id())->where('status', 'pending')->count() + \App\Models\CategoryLog::where('user_id', Auth::id())->where('status', 'pending')->count(); @endphp
        <a href="{{ route('approvals.my-tickets') }}" @click="sidebarOpen = false"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 group {{ request()->routeIs('approvals.my-tickets') ? 'bg-purple-50 text-purple-700 font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="w-5 h-5 {{ request()->routeIs('approvals.my-tickets') ? 'text-purple-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
            <span>Tiket Saya</span>
            <span id="nav-tickets-badge" class="ml-auto {{ $myPendingCount > 0 ? 'inline-flex' : 'hidden' }} items-center justify-center h-5 min-w-[1.25rem] px-1.5 rounded-full bg-amber-500 text-white text-[10px] font-bold shadow-sm">{{ $myPendingCount }}</span>
        </a>
        @endif

        <a href="{{ route('assets.history') }}" @click="sidebarOpen = false"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 group {{ request()->routeIs('assets.history') ? 'bg-purple-50 text-purple-700 font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="w-5 h-5 {{ request()->routeIs('assets.history') ? 'text-purple-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>Riwayat</span>
        </a>

        <a href="{{ route('assets.print') }}" @click="sidebarOpen = false"
            class="hidden lg:flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 group {{ request()->routeIs('assets.print*') ? 'bg-purple-50 text-purple-700 font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="w-5 h-5 {{ request()->routeIs('assets.print*') ? 'text-purple-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            <span>Cetak Label</span>
        </a>

        <a href="{{ route('assets.archive') }}" @click="sidebarOpen = false"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('assets.archive') ? 'bg-purple-50 text-purple-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18v4H3zM5 8v12h14V8M10 12h4" /></svg>
            <span>Arsip Aset</span>
        </a>

        <a href="{{ route('categories.index') }}" @click="sidebarOpen = false"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 group {{ request()->routeIs('categories.*') ? 'bg-purple-50 text-purple-700 font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="w-5 h-5 {{ request()->routeIs('categories.*') ? 'text-purple-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
            </svg>
            <span>Kategori</span>
        </a>

        @if(Auth::user()->role === 'super_admin')

        <div class="mt-4 pt-4 border-t border-gray-100">

        <a href="{{ route('users.index') }}" @click="sidebarOpen = false"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 group {{ request()->routeIs('users.*') ? 'bg-purple-50 text-purple-700 font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="w-5 h-5 {{ request()->routeIs('users.*') ? 'text-purple-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <span>User</span>
        </a>

        </div>

        @endif
    </nav>

    <div class="border-t border-gray-200 p-4 bg-gray-50/50">
        <div class="flex items-center gap-3 mb-4">
            <div class="h-10 w-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold shadow-md shrink-0">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>

            <div class="overflow-hidden">
                <div class="text-sm font-bold text-gray-900 truncate">{{ Auth::user()->name }}</div>
                <div class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="form-logout">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all cursor-pointer">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Logout
            </button>
        </form>
    </div>

</aside>
