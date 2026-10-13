@if($asset->sale)
<div x-show="activeTab === 'sale'" x-cloak>
    <h3 class="font-bold text-gray-900 mb-4">Riwayat penjualan</h3>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div><dt class="text-gray-500">Tanggal penjualan</dt><dd class="font-semibold mt-1">{{ $asset->sale->sold_on->format('d/m/Y') }}</dd></div>
        <div><dt class="text-gray-500">Harga jual</dt><dd class="font-semibold mt-1">Rp {{ number_format($asset->sale->amount, 2, ',', '.') }}</dd></div>
        <div><dt class="text-gray-500">Pembeli</dt><dd class="font-semibold mt-1">{{ $asset->sale->buyer_name ?: '-' }}</dd></div>
        <div><dt class="text-gray-500">Dicatat pada</dt><dd class="font-semibold mt-1">{{ $asset->sale->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</dd></div>
    </dl>
    @if($asset->sale->notes)<p class="text-sm text-gray-700 mt-4 whitespace-pre-line">{{ $asset->sale->notes }}</p>@endif
    <a href="{{ route('assets.sale-proof', $asset->id) }}" target="_blank" rel="noopener" class="inline-flex mt-5 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">Lihat bukti penjualan</a>
    <p class="text-xs text-gray-500 mt-2">{{ $asset->sale->proof_name }}</p>
</div>
@endif
