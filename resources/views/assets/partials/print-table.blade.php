<div class="bg-white overflow-hidden sm:rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100">
    <div class="overflow-x-auto">
        <table class="min-w-full text-left border-collapse">
            
            <thead>
                <tr class="bg-gray-50/80 text-gray-500 text-[11px] font-extrabold uppercase tracking-wider border-b border-gray-100">
                    <th class="px-4 py-4 w-10 text-center">No</th>
                    <th class="px-4 py-4 whitespace-nowrap">ID Aset</th>
                    <th class="px-4 py-4">Nama Aset</th>
                    <th class="px-4 py-4 text-center">Kategori</th>
                    <th class="px-4 py-4 text-center">Kondisi</th>
                    <th class="px-4 py-4 text-center">Status</th>
                    <th class="px-4 py-4">Deskripsi</th>
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
                            <span class="font-mono font-bold text-gray-800 bg-gray-100 px-2 py-1 rounded-md text-sm group-hover:bg-white transition">
                                {{ $asset->asset_tag ?? $asset->id }}
                            </span>
                        </td>
                        
                        <td class="px-4 py-4">
                            <div class="font-bold text-gray-900 text-sm">{{ $asset->name }}</div>
                        </td>
                        
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            <span class="inline-block px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-linear-to-r from-blue-500 to-purple-500 text-white shadow-sm">
                                {{ $asset->category }}
                            </span>
                        </td>

                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            @php
                                $condClass = match($asset->condition) {
                                    'baik' => 'bg-teal-50 text-teal-600 border border-teal-100',
                                    'rusak' => 'bg-orange-50 text-orange-600 border border-orange-100',
                                    'rusak_total' => 'bg-red-100 text-red-700 border border-red-200',
                                    default => 'bg-gray-50 text-gray-600 border border-gray-100',
                                };
                                $condLabel = match($asset->condition) { 'baik' => 'Baik', 'rusak' => 'Rusak', 'rusak_total' => 'Rusak Total', default => ucfirst($asset->condition ?? '-') };
                            @endphp
                            <span class="px-3 py-1 inline-flex text-[10px] leading-5 font-bold rounded-full uppercase {{ $condClass }}">
                                {{ $condLabel }}
                            </span>
                        </td>
                        
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            @php
                                $statusKey = strtolower(str_replace(' ', '_', $asset->status));
                                $colorClass = 'bg-gray-100 text-gray-700'; 
                                if (str_contains($statusKey, 'in_use')) { $colorClass = 'bg-blue-100 text-blue-700 border border-blue-200'; } 
                                elseif (str_contains($statusKey, 'available')) { $colorClass = 'bg-green-100 text-green-700 border border-green-200'; } 
                                elseif (str_contains($statusKey, 'maintenance')) { $colorClass = 'bg-amber-100 text-amber-700 border border-amber-200'; } 
                                elseif (str_contains($statusKey, 'broken')) { $colorClass = 'bg-red-100 text-red-700 border border-red-200'; }
                            @endphp
                            <span class="inline-block px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider rounded-full {{ $colorClass }}">
                                {{ str_replace('_', ' ', $asset->status) }}
                            </span>
                        </td>
                        
                        <td class="px-4 py-4 text-sm text-gray-600 truncate max-w-37.5" title="{{ $asset->description }}">
                            {{ $asset->description ?? '-' }}
                        </td>
                        
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            <input type="checkbox" value="{{ $asset->id }}" class="asset-checkbox w-5 h-5 rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50 cursor-pointer transition-transform hover:scale-110">
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                            Data aset tidak ditemukan.
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