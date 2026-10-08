<div class="category-panel">
    <div class="category-panel-header"><h3>Daftar kategori</h3><span>{{ $categories->total() ? $categories->firstItem() : 0 }}–{{ $categories->lastItem() ?? 0 }} dari {{ $categories->total() }} kategori</span></div>
    @if($categories->count())
    <table class="category-table">
        <caption class="sr-only">Daftar kategori aset dan aksi pengelolaan</caption>
        <thead><tr><th scope="col" class="category-number-cell">No</th><th scope="col">Kategori</th><th scope="col">Kode tag</th><th scope="col">Jumlah aset</th><th scope="col" class="category-actions-heading">Aksi</th></tr></thead>
        <tbody>
            @foreach($categories as $category)
            <tr>
                <td class="category-number-cell" data-label="No">{{ $categories->firstItem() + $loop->index }}</td>
                <td class="category-name-cell"><span class="category-name">{{ $category->name }}</span><span class="category-slug">Kode kategori: {{ $category->slug }}</span></td>
                <td data-label="Kode tag"><span class="category-prefix-original">{{ $category->prefix }}</span></td>
                <td data-label="Jumlah aset"><a href="{{ route('assets.index', ['category' => $category->slug]) }}" class="category-asset-link" aria-label="Lihat aset kategori {{ $category->name }}">{{ $category->assets_count }} aset →</a></td>
                <td class="category-actions-cell">
                    <div class="category-actions">
                        <a href="{{ route('categories.edit', $category->id) }}" class="category-icon-action category-icon-edit" title="Edit" aria-label="Edit kategori {{ $category->name }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </a>
                        <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="form-delete">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="category-icon-action category-icon-delete" title="Hapus" aria-label="Hapus kategori {{ $category->name }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="px-5 py-12 text-center">
        <p class="text-base font-semibold text-gray-900">{{ request()->filled('search') ? 'Kategori tidak ditemukan' : 'Belum ada kategori' }}</p>
        <p class="mt-2 text-sm text-gray-500">{{ request()->filled('search') ? 'Coba kata kunci lain atau kosongkan pencarian.' : 'Tambahkan kategori untuk mengelompokkan aset Anda.' }}</p>
    </div>
    @endif
</div>
@if ($categories->hasPages())
<div class="mt-2">{{ $categories->links('pagination.custom') }}</div>
@endif
