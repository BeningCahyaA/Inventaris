<div class="text-xs text-gray-700 space-y-1">
    <p class="font-semibold">Penjualan aset</p>
    <p>Tanggal: {{ $values['new']['sold_on'] ?? '-' }}</p>
    <p>Harga jual: Rp {{ number_format($values['new']['amount'] ?? 0, 2, ',', '.') }}</p>
    <p>Pembeli: {{ $values['new']['buyer_name'] ?? '-' }}</p>
    <a href="{{ route('assets.sale-request-proof', $log->id) }}" target="_blank" rel="noopener" class="inline-block font-semibold text-purple-700 underline">Lihat bukti penjualan</a>
</div>
