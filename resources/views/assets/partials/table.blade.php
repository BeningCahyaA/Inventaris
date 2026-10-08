@php
    $assetCategoryNames = \App\Models\Category::pluck('name', 'slug');
@endphp
<div class="category-panel asset-list-panel">
    <div class="category-panel-header"><h3>Daftar aset</h3><span>{{ $assets->total() ? $assets->firstItem() : 0 }}–{{ $assets->lastItem() ?? 0 }} dari {{ $assets->total() }} aset</span></div>
    @if($assets->count())
    <div class="asset-table-scroll">
    <table class="category-table asset-table">
        <caption class="sr-only">Daftar aset, penanggung jawab, lokasi, status, dan aksi</caption>
        <thead><tr>
            <th scope="col" class="asset-check-cell"><input type="checkbox" id="select-all-page" aria-label="Pilih semua aset di halaman ini" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"></th>
            <th scope="col">Aset</th><th scope="col">Penanggung jawab & lokasi</th><th scope="col">Kategori & status</th><th scope="col" class="category-actions-heading">Aksi</th>
        </tr></thead>
        <tbody>
            @foreach($assets as $asset)
            @php
                $statusLabel = match($asset->status) { 'available' => 'Tersedia', 'in_use' => 'Sedang digunakan', 'maintenance' => 'Dalam perawatan', 'broken' => 'Rusak', default => $asset->status };
                $conditionLabel = match($asset->condition) { 'baik' => 'Baik', 'rusak' => 'Rusak', 'rusak_total' => 'Rusak total', default => $asset->condition ?? 'Belum dicatat' };
            @endphp
            <tr>
                <td class="asset-check-cell"><input type="checkbox" class="asset-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" value="{{ $asset->id }}" data-name="{{ $asset->name }}" aria-label="Pilih aset {{ $asset->name }}"></td>
                <td class="asset-name-cell">
                    <a href="{{ route('assets.show', $asset->id) }}" class="category-name asset-name-link">{{ $asset->name }}</a>
                    <span class="asset-tag">{{ $asset->asset_tag }}</span>
                    <span class="category-slug">Kondisi: {{ $conditionLabel }}</span>
                </td>
                <td class="asset-details-cell" data-label="Penanggung jawab & lokasi">
                    <span class="asset-pic">{{ $asset->person_in_charge ?: 'Belum ditentukan' }}</span>
                    <span class="category-slug">Lokasi: {{ $asset->location ?: 'Belum dicatat' }}</span>
                    @if($asset->activeTransaction)
                    <span class="asset-loan">Peminjam: {{ $asset->activeTransaction->borrower_name }}@if($asset->activeTransaction->due_at)<br>Jatuh tempo: {{ $asset->activeTransaction->due_at->format('d/m/Y') }}@endif</span>
                    @endif
                </td>
                <td class="asset-status-cell" data-label="Kategori & status">
                    <span class="asset-category">{{ $assetCategoryNames[$asset->category] ?? $asset->category }}</span>
                    <span class="asset-status asset-status-{{ $asset->status }}">{{ $statusLabel }}</span>
                </td>
                <td class="category-actions-cell asset-actions-cell">
                    <div class="category-actions">
                        <a href="{{ route('assets.show', $asset->id) }}" class="category-icon-action category-icon-edit" title="Detail" aria-label="Detail aset {{ $asset->name }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z"></path></svg>
                        </a>
                        <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" class="form-delete">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="category-icon-action category-icon-delete" title="Hapus" aria-label="Hapus aset {{ $asset->name }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @else
    <div class="px-5 py-12 text-center"><p class="text-base font-semibold text-gray-900">Tidak ada aset untuk ditampilkan</p><p class="mt-2 text-sm text-gray-500">Coba kata kunci lain, reset filter, atau tambahkan aset baru.</p></div>
    @endif
</div>
@if($assets->hasPages())<div class="mt-2">{{ $assets->links('pagination.custom') }}</div>@endif
