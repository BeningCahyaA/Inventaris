<x-app-layout>
    <x-slot name="title">Arsip Aset</x-slot>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Arsip Aset</h2></x-slot>

    <div class="asset-subpage management-page archive-page">
        <div class="max-w-7xl mx-auto">
            <div class="management-intro">
                <div><h3>Arsip aset</h3><p>Aset yang sudah dijual beserta informasi dan bukti penjualannya. Pilih detail untuk melihat riwayat aset.</p></div>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm mb-6 border border-gray-100">
                <form method="GET" action="{{ route('assets.archive') }}" role="search" class="flex flex-col sm:flex-row items-stretch gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama aset atau tag..." aria-label="Cari arsip aset"
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-lg leading-5 bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm transition shadow-inner">
                    </div>
                    <button type="submit" class="px-5 py-2.5 bg-purple-600 text-sm font-semibold text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition">Cari</button>
                    @if(request()->filled('search'))
                        <a href="{{ route('assets.archive') }}" class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-50">Reset</a>
                    @endif
                </form>
            </div>

            <div class="management-panel bg-white overflow-hidden">
                <div class="management-panel-heading"><h3>Daftar aset dijual</h3><span>{{ $assets->firstItem() ?? 0 }}–{{ $assets->lastItem() ?? 0 }} dari {{ $assets->total() }} aset</span></div>
                @if($assets->isNotEmpty())
                    <div class="overflow-x-auto hidden md:block">
                        <table class="management-table w-full text-left border-collapse">
                            <caption class="sr-only">Arsip aset yang sudah dijual</caption>
                            <thead><tr class="text-gray-500 uppercase tracking-wider">
                                <th scope="col">Aset</th><th scope="col">Tanggal penjualan</th><th scope="col">Harga jual</th><th scope="col">Pembeli</th><th scope="col">Aksi</th>
                            </tr></thead>
                            <tbody>
                                @foreach($assets as $asset)
                                    <tr class="group transition-colors">
                                        <td><div class="font-bold text-gray-900 break-words group-hover:text-purple-600">{{ $asset->name }}</div><div class="text-xs text-gray-500 font-mono mt-0.5">{{ $asset->asset_tag }}</div></td>
                                        <td class="text-gray-600 whitespace-nowrap">{{ $asset->sale?->sold_on?->format('d M Y') ?? '-' }}</td>
                                        <td class="font-semibold text-gray-900 whitespace-nowrap">Rp {{ number_format($asset->sale?->amount ?? 0, 2, ',', '.') }}</td>
                                        <td class="text-gray-700 break-words">{{ $asset->sale?->buyer_name ?: '-' }}</td>
                                        <td><a href="{{ route('assets.show', $asset->id) }}" aria-label="Detail dan riwayat {{ $asset->name }}" class="inline-flex items-center min-h-11 font-semibold text-purple-700 whitespace-nowrap hover:text-purple-900 focus-visible:outline-purple-500">Detail &amp; riwayat →</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="md:hidden divide-y divide-gray-100">
                        @foreach($assets as $asset)
                            <article class="px-4 py-4">
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="min-w-0"><h4 class="font-semibold text-gray-900 text-sm break-words">{{ $asset->name }}</h4><p class="font-mono text-xs text-gray-500 mt-1 break-all">{{ $asset->asset_tag }}</p></div>
                                    <span class="management-badge bg-emerald-100 text-emerald-700">Dijual</span>
                                </div>
                                <dl class="grid grid-cols-2 gap-3 text-xs bg-gray-50 p-3 rounded-lg border border-gray-100">
                                    <div><dt class="text-gray-500 mb-1">Tanggal penjualan</dt><dd class="text-gray-700">{{ $asset->sale?->sold_on?->format('d M Y') ?? '-' }}</dd></div>
                                    <div class="text-right"><dt class="text-gray-500 mb-1">Harga jual</dt><dd class="text-gray-900 font-semibold break-words">Rp {{ number_format($asset->sale?->amount ?? 0, 2, ',', '.') }}</dd></div>
                                    <div class="col-span-2"><dt class="text-gray-500 mb-1">Pembeli</dt><dd class="text-gray-700 break-words">{{ $asset->sale?->buyer_name ?: '-' }}</dd></div>
                                </dl>
                                <a href="{{ route('assets.show', $asset->id) }}" aria-label="Detail dan riwayat {{ $asset->name }}" class="inline-flex items-center min-h-11 mt-2 text-sm font-semibold text-purple-700 hover:text-purple-900">Detail &amp; riwayat →</a>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="management-empty" role="status">
                        <strong>{{ request()->filled('search') ? 'Arsip aset tidak ditemukan' : 'Belum ada aset di arsip' }}</strong>
                        <p>{{ request()->filled('search') ? 'Coba kata kunci lain atau reset pencarian.' : 'Aset yang sudah dijual akan ditampilkan di sini.' }}</p>
                    </div>
                @endif
            </div>
            @if($assets->hasPages())
                <div class="mt-4 px-1">{{ $assets->links('pagination.custom') }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
