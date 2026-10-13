<x-app-layout>
    <x-slot name="title">Arsip Aset</x-slot>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Arsip Aset</h2></x-slot>
    <div class="asset-subpage max-w-7xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900">Aset yang sudah dijual</h1>
        <p class="mt-2 text-sm text-gray-500">Informasi aset dan bukti penjualan tetap tersimpan. Pilih Detail untuk melihat riwayatnya.</p>
        <form method="GET" class="flex gap-2 my-5">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode aset..." aria-label="Cari arsip aset" class="min-w-0 flex-1 border-gray-300 rounded-lg focus:ring-purple-500">
            <button class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">Cari</button>
        </form>
        <div class="bg-white border border-gray-200 rounded-xl overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500"><tr><th class="p-4">Aset</th><th class="p-4">Tanggal penjualan</th><th class="p-4">Harga jual</th><th class="p-4">Pembeli</th><th class="p-4">Aksi</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($assets as $asset)
                    <tr><td class="p-4"><p class="font-semibold">{{ $asset->name }}</p><p class="text-xs text-gray-500 mt-1">{{ $asset->asset_tag }}</p></td>
                        <td class="p-4 whitespace-nowrap">{{ $asset->sale?->sold_on?->format('d/m/Y') ?? '-' }}</td>
                        <td class="p-4 whitespace-nowrap">Rp {{ number_format($asset->sale?->amount ?? 0, 2, ',', '.') }}</td>
                        <td class="p-4">{{ $asset->sale?->buyer_name ?: '-' }}</td>
                        <td class="p-4"><a href="{{ route('assets.show', $asset->id) }}" class="font-semibold text-purple-700">Detail & riwayat</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-8 text-center text-gray-500">Belum ada aset dijual yang sesuai pencarian.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $assets->links('pagination.custom') }}</div>
    </div>
</x-app-layout>
