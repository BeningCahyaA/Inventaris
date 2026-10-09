<div class="label-table-panel bg-white overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 px-5 py-4">
        <h3 class="text-sm font-semibold text-gray-900">Daftar aset</h3>
        <span class="text-xs text-gray-500">{{ $assets->firstItem() ?? 0 }}–{{ $assets->lastItem() ?? 0 }} dari {{ $assets->total() }} aset</span>
    </div>
    <div class="overflow-x-auto">
        <table class="label-asset-table w-full min-w-full text-left border-collapse">
            <caption class="sr-only">Pilih aset yang labelnya ingin dicetak</caption>
            
            <thead>
                <tr class="bg-gray-50/80 text-gray-500 text-[11px] font-extrabold uppercase tracking-wider border-b border-gray-100">
                    <th class="px-4 py-4 w-10 text-center">No</th>
                    <th class="px-4 py-4 whitespace-nowrap">ID Aset</th>
                    <th class="px-4 py-4">Nama Aset</th>
                    <th class="px-4 py-4 text-center">Kategori</th>
                    <th class="px-4 py-4 text-center">Kondisi</th>
                    <th class="px-4 py-4 text-center">Status</th>
                    <th class="px-4 py-4 text-center w-24">Pilih</th> 
                </tr>
            </thead>
            
            <tbody class="bg-white divide-y divide-gray-50">
                @if($assets->count() > 0)
                    @foreach($assets as $asset)
                    <tr class="hover:bg-blue-50/40 transition-colors duration-200 group">
                        
                        <td class="px-4 py-4 text-sm text-gray-400 font-medium text-center">
                            {{ ($assets->currentPage() - 1) * $assets->perPage() + $loop->iteration }}
                        </td>
                        
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="label-asset-tag">
                                {{ $asset->asset_tag ?? $asset->id }}
                            </span>
                        </td>
                        
                        <td class="px-4 py-4">
                            <div class="label-asset-name">{{ $asset->name }}</div>
                            <div class="label-asset-description" title="{{ $asset->description }}">{{ $asset->description ?: 'Belum ada deskripsi' }}</div>
                        </td>
                        
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            <span class="inline-block px-3 py-1 text-xs font-medium rounded-md bg-gray-100 text-gray-600">
                                {{ $asset->category }}
                            </span>
                        </td>

                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            @php
                                $conditionLabel = match($asset->condition) { 'baik' => 'Baik', 'rusak' => 'Rusak', 'rusak_total' => 'Rusak total', default => $asset->condition ?? 'Belum dicatat' };
                                $conditionClass = match($asset->condition) { 'baik' => 'asset-status-available', 'rusak', 'rusak_total' => 'asset-status-broken', default => '' };
                            @endphp
                            <span class="asset-status {{ $conditionClass }}">{{ $conditionLabel }}</span>
                        </td>

                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            @php
                                $statusLabel = match($asset->status) { 'available' => 'Tersedia', 'in_use' => 'Sedang digunakan', 'maintenance' => 'Dalam perawatan', 'broken' => 'Rusak', default => $asset->status };
                            @endphp
                            <span class="asset-status asset-status-{{ $asset->status }}">{{ $statusLabel }}</span>
                        </td>
                        
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            <input type="checkbox" aria-label="Pilih label {{ $asset->asset_tag ?? $asset->id }}" value="{{ $asset->id }}" class="asset-checkbox w-5 h-5 rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50 cursor-pointer">
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                            <p class="font-semibold text-gray-700">Tidak ada aset ditemukan</p>
                            <p class="mt-1 text-sm">Coba gunakan nama, ID, atau kategori aset lainnya.</p>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

@if ($assets->hasPages())
    <div class="mt-2 px-1">
        {{ $assets->links('pagination.custom') }} 
    </div>
@endif
