<x-app-layout>
    <x-slot name="title">Edit Aset</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Aset : ') }} <span class="text-purple-600">{{ $asset->name }}</span>
        </h2>
    </x-slot>

    <div class="asset-subpage asset-form-page" x-data="{ status: '{{ old('status', $asset->status) }}' }">
        <div class="max-w-4xl mx-auto">
            <div class="asset-form-intro"><a href="{{ route('assets.index') }}" class="asset-back-link">← Kembali ke daftar aset</a>
                <h1>Edit informasi aset</h1>
                <p>Periksa informasi dan lampiran sebelum menyimpan perubahan.</p>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-4 sm:p-7 bg-white">

                    <form class="asset-edit-form" action="{{ route('assets.update', $asset->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        @if ($errors->any())
                        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <p class="font-semibold mb-1">Perubahan belum tersimpan. Periksa isian berikut:</p>
                            <ul class="list-disc list-inside space-y-0.5">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <div class="mb-5">
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Aset</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $asset->name) }}"
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 shadow-sm"
                                placeholder="Masukkan nama aset">
                            @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-5">
                            <label for="person_in_charge" class="block text-sm font-semibold text-gray-700 mb-1">Penanggung Jawab</label>
                            <input type="text" name="person_in_charge" id="person_in_charge" value="{{ old('person_in_charge', $asset->person_in_charge) }}"
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 shadow-sm"
                                placeholder="Kosongkan jika tidak ada">
                            @error('person_in_charge') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                            <div>
                                <label for="location" class="block text-sm font-semibold text-gray-700 mb-1">Lokasi Aset</label>
                                <input type="text" name="location" id="location" value="{{ old('location', $asset->location) }}"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 shadow-sm"
                                    placeholder="Contoh: Gudang A / Site B">
                                @error('location') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="vendor" class="block text-sm font-semibold text-gray-700 mb-1">Vendor</label>
                                <input type="text" name="vendor" id="vendor" value="{{ old('vendor', $asset->vendor) }}"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 shadow-sm"
                                    placeholder="Contoh: PT Mitra Teknologi">
                                @error('vendor') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                            <div>
                                <label for="serial_number" class="block text-sm font-semibold text-gray-700 mb-1">Serial Number</label>
                                <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', $asset->serial_number) }}"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 shadow-sm"
                                    placeholder="SN-XXXX-XXXX">
                                @error('serial_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="warranty_expiry_date" class="block text-sm font-semibold text-gray-700 mb-1">Masa Garansi</label>
                                <input type="date" name="warranty_expiry_date" id="warranty_expiry_date" value="{{ old('warranty_expiry_date', optional($asset->warranty_expiry_date)->format('Y-m-d')) }}"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 shadow-sm">
                                @error('warranty_expiry_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                            <div>
                                <label for="category" class="block text-sm font-semibold text-gray-700 mb-1">Kategori Aset</label>
                                <select name="category" id="category"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 cursor-pointer shadow-sm">
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->slug }}" {{ old('category', $asset->category) == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                                <select name="status" id="status" required x-model="status"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 cursor-pointer shadow-sm">
                                    <option value="available" {{ (old('status', $asset->status) == 'available') ? 'selected' : '' }}>Tersedia</option>
                                    <option value="in_use" {{ (old('status', $asset->status) == 'in_use') ? 'selected' : '' }}>Sedang digunakan</option>
                                    <option value="borrowed" @selected(old('status', $asset->status) === 'borrowed')>Dipinjam</option>
                                    <option value="maintenance" {{ (old('status', $asset->status) == 'maintenance') ? 'selected' : '' }}>Dalam perawatan</option>
                                    <option value="broken" {{ (old('status', $asset->status) == 'broken') ? 'selected' : '' }}>Rusak</option>
                                    <option value="sold" {{ old('status', $asset->status) == 'sold' ? 'selected' : '' }}>Dijual</option>
                                </select>
                                @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                            <div>
                                <label for="condition" class="block text-sm font-semibold text-gray-700 mb-1">Kondisi Fisik</label>
                                <select name="condition" id="condition"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 cursor-pointer shadow-sm">
                                    <option value="baik" {{ (old('condition', $asset->condition) == 'baik') ? 'selected' : '' }}>Baik</option>
                                    <option value="rusak" {{ (old('condition', $asset->condition) == 'rusak') ? 'selected' : '' }}>Rusak</option>
                                    <option value="rusak_total" {{ (old('condition', $asset->condition) == 'rusak_total') ? 'selected' : '' }}>Rusak Total</option>
                                </select>
                                @error('condition') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="purchase_date" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Pembelian</label>
                                <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date', $asset->purchase_date->format('Y-m-d')) }}"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 shadow-sm">
                                @error('purchase_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Borrower / Peminjaman Fields (hanya muncul saat status Dipinjam) --}}
                        <div x-show="status === 'borrowed'" x-cloak x-transition class="mb-5 bg-blue-50 border border-blue-200 rounded-xl p-5">
                            <h4 class="text-sm font-bold text-blue-800 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Data Peminjam
                            </h4>
                            @if($asset->activeTransaction)
                            <div class="bg-white border border-blue-100 rounded-lg p-3 mb-3 text-xs text-gray-600">
                                <p><strong>Peminjam Saat Ini:</strong> {{ $asset->activeTransaction->borrower_name }}</p>
                                <p><strong>Dipinjam Sejak:</strong> {{ $asset->activeTransaction->borrowed_at->format('d M Y, H:i') }}</p>
                                @if($asset->activeTransaction->due_at)
                                <p><strong>Jatuh Tempo:</strong> {{ \Carbon\Carbon::parse($asset->activeTransaction->due_at)->format('d M Y') }}</p>
                                @endif
                            </div>
                            @endif
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="borrower_name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Peminjam*</label>
                                    <input type="text" name="borrower_name" id="borrower_name" value="{{ old('borrower_name', $asset->activeTransaction?->borrower_name) }}"
                                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm"
                                        placeholder="Nama peminjam" :required="status === 'borrowed'">
                                    @error('borrower_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="due_at" class="block text-sm font-semibold text-gray-700 mb-1">Jatuh Tempo</label>
                                    <input type="date" name="due_at" id="due_at" value="{{ old('due_at', $asset->activeTransaction?->due_at ? \Carbon\Carbon::parse($asset->activeTransaction->due_at)->format('Y-m-d') : '') }}"
                                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                                    @error('due_at') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="mt-3">
                                <label for="borrow_notes" class="block text-sm font-semibold text-gray-700 mb-1">Catatan Peminjaman</label>
                                <input type="text" name="borrow_notes" id="borrow_notes" value="{{ old('borrow_notes') }}"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm"
                                    placeholder="Opsional">
                            </div>
                        </div>

                        <div class="mb-5">
                            <label for="price" class="block text-sm font-semibold text-gray-700 mb-1">Nilai Aset / Harga (Rp)</label>
                            <input type="number" name="price" id="price" value="{{ old('price', $asset->price) }}" min="0"
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 shadow-sm"
                                placeholder="Contoh: 15000000 (Tanpa titik)">
                            @error('price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>


                        @include('assets.partials.sale-form')

                        <div class="mb-8">
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="description" id="description" rows="4"
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 shadow-sm"
                                placeholder="Tambahkan deskripsi atau keterangan detail mengenai aset ini...">{{ old('description', $asset->description) }}</textarea>
                            @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-5">
                            <label for="attachments" class="block text-sm font-semibold text-gray-700 mb-1">Tambah Lampiran / Foto</label>
                            <input type="file" name="attachments[]" id="attachments" multiple
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200 shadow-sm">
                            <p class="text-xs text-gray-500 mt-1">Maks 5MB/file. Format: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX.</p>
                            @error('attachments.*') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        @if($asset->attachments->count() > 0)
                        @php $isSuperAdmin = auth()->user()->role === 'super_admin'; @endphp
                        <div class="mb-8 bg-gray-50 border border-gray-100 rounded-lg p-4"
                            x-data="{
                                selected: [],
                                loading: false,
                                isSuperAdmin: @js($isSuperAdmin),
                                items: @js($asset->attachments->map(fn ($a) => [
                                    'id' => (string) $a->id,
                                    'url' => route('assets.attachments.destroy', [$asset->id, $a->id]),
                                ])->values()),
                                async removeSelected() {
                                    if (!this.selected.length || this.loading) return;
                                    const msg = this.isSuperAdmin
                                        ? 'Hapus ' + this.selected.length + ' lampiran terpilih?'
                                        : 'Ajukan penghapusan ' + this.selected.length + ' lampiran terpilih? Menunggu persetujuan Super Admin.';
                                    if (!confirm(msg)) return;

                                    this.loading = true;
                                    const targets = this.items.filter(i => this.selected.includes(i.id));
                                    const last = targets.pop();
                                    const token = document.querySelector('meta[name=csrf-token]')?.content
                                        ?? '{{ csrf_token() }}';

                                    try {
                                        for (const t of targets) {
                                            await fetch(t.url, {
                                                method: 'POST',
                                                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'text/html' },
                                                body: new URLSearchParams({ _method: 'DELETE' }),
                                                credentials: 'same-origin',
                                            });
                                        }
                                    } catch (e) {
                                        this.loading = false;
                                        alert('Gagal memproses sebagian lampiran. Silakan coba lagi.');
                                        return;
                                    }

                                    // Lampiran terakhir dikirim lewat form biasa agar pesan flash tampil
                                    const form = document.getElementById('bulk-remove-attachment');
                                    form.action = last.url;
                                    form.submit();
                                }
                            }">
                            <h4 class="text-sm font-bold text-gray-700 mb-2">Lampiran Saat Ini</h4>

                            <ul class="space-y-3 text-sm mt-3">
                                @foreach($asset->attachments as $attachment)
                                <li class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3 rounded border border-gray-200 shadow-sm">
                                    <div class="flex items-center gap-3 min-w-0">
                                        @if($attachment->isImage())
                                        <img src="{{ route('assets.attachments.show', [$asset->id, $attachment->id]) }}" alt="{{ $attachment->file_name }}" class="w-12 h-12 object-cover rounded border border-gray-200 shrink-0">
                                        @else
                                        <svg class="w-5 h-5 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        @endif
                                        <a href="{{ route('assets.attachments.show', [$asset->id, $attachment->id]) }}" target="_blank" class="text-purple-600 hover:text-purple-800 font-medium truncate">
                                            {{ $attachment->file_name }}
                                        </a>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3 shrink-0">
                                        <label for="delete_attachment_{{ $attachment->id }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-600 cursor-pointer whitespace-nowrap">
                                            <input type="checkbox" value="{{ $attachment->id }}" x-model="selected" id="delete_attachment_{{ $attachment->id }}" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                            Hapus
                                        </label>
                                    </div>
                                </li>
                                @endforeach
                            </ul>

                            {{-- Tombol hapus: muncul hanya jika ada lampiran yang dicentang --}}
                            <div x-show="selected.length > 0" x-cloak x-transition
                                class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-lg border border-red-200 bg-red-50 p-3">
                                <div class="text-xs text-red-700">
                                    <p class="font-semibold"><span x-text="selected.length"></span> lampiran dipilih</p>
                                    @if(!$isSuperAdmin)
                                    <p class="mt-0.5">Penghapusan akan berstatus <strong>menunggu persetujuan</strong> Super Admin.</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" @click="selected = []" :disabled="loading"
                                        class="text-xs font-semibold text-gray-600 hover:text-gray-800 px-3 py-1.5 rounded-lg border border-gray-200 bg-white cursor-pointer">
                                        Batal pilih
                                    </button>
                                    <button type="button" @click="removeSelected()" :disabled="loading"
                                        class="inline-flex items-center gap-1.5 whitespace-nowrap text-xs font-semibold text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 rounded-lg px-3 py-1.5 shadow-sm transition cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        <span x-text="loading ? 'Memproses...' : (isSuperAdmin ? 'Hapus lampiran' : 'Ajukan hapus')"></span>
                                    </button>
                                </div>
                            </div>

                            <p class="text-xs text-gray-500 mt-3 italic">* Centang lampiran yang ingin dihapus, lalu tombol hapus akan muncul di bawah daftar. Penghapusan berjalan terpisah dari perubahan isian aset.@if(!$isSuperAdmin) Untuk Admin, penghapusan menunggu persetujuan Super Admin.@endif</p>
                        </div>
                        @endif

                        <div class="asset-form-actions">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-2.5 bg-purple-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md cursor-pointer">
                                {{ auth()->user()->role === 'super_admin' ? 'Simpan perubahan' : 'Kirim permintaan perubahan' }}
                            </button>

                            <a href="{{ route('assets.index') }}"
                                class="inline-flex items-center px-6 py-2.5 bg-gray-500 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm cursor-pointer">
                                Batal
                            </a>
                        </div>

                    </form>

                    {{-- Form tersembunyi untuk hapus lampiran (action diisi lewat Alpine) --}}
                    @if($asset->attachments->count() > 0)
                    <form id="bulk-remove-attachment" action="#" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>