<div x-show="activeTab === 'depreciation'" x-cloak>
    <h3 class="font-bold text-gray-900 mb-2">Riwayat penyusutan tahunan</h3>
    <p class="text-sm text-gray-500 mb-4">Penyusutan menggunakan harga beli awal. Riwayat yang sudah tercatat menyimpan persentase dan nilai pada saat perhitungan.</p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Harga beli awal</p><p class="font-semibold text-gray-900 mt-1">Rp {{ number_format($asset->price, 2, ',', '.') }}</p></div>
        <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Total penyusutan</p><p class="font-semibold text-gray-900 mt-1">Rp {{ number_format($asset->depreciations->sum('amount'), 2, ',', '.') }}</p></div>
        <div class="rounded-lg bg-purple-50 p-3"><p class="text-xs text-purple-700">Nilai aset saat ini</p><p class="font-bold text-purple-800 mt-1">Rp {{ number_format($depreciatedValue, 2, ',', '.') }}</p></div>
    </div>
    @if($asset->depreciations->isEmpty())
        <p class="text-sm text-gray-500 rounded-lg border border-gray-200 p-4">Belum ada penyusutan tercatat. Penyusutan dimulai setelah satu tahun penuh dari tanggal pembelian jika persentasenya lebih dari 0%.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-xs text-gray-500"><tr>
                    <th class="p-3">Tahun ke</th><th class="p-3">Tanggal</th><th class="p-3">Persentase</th><th class="p-3 text-right">Nilai sebelum</th><th class="p-3 text-right">Penyusutan</th><th class="p-3 text-right">Nilai setelah</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($asset->depreciations as $entry)
                        <tr>
                            <td class="p-3">{{ $entry->year_number }}</td>
                            <td class="p-3 whitespace-nowrap">{{ $entry->depreciated_on->format('d/m/Y') }}</td>
                            <td class="p-3">{{ number_format($entry->rate, 2, ',', '.') }}%</td>
                            <td class="p-3 text-right whitespace-nowrap">Rp {{ number_format($entry->opening_value, 2, ',', '.') }}</td>
                            <td class="p-3 text-right whitespace-nowrap">Rp {{ number_format($entry->amount, 2, ',', '.') }}</td>
                            <td class="p-3 text-right whitespace-nowrap font-semibold text-purple-700">Rp {{ number_format($entry->closing_value, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
