<x-app-layout>
    <x-slot name="title">Tambah Aset</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Aset Baru') }}
        </h2>
    </x-slot>

    <div class="asset-subpage asset-form-page">
        <div class="max-w-4xl mx-auto"><div class="asset-form-intro"><a href="{{ route('assets.index') }}" class="asset-back-link">← Kembali ke daftar aset</a><h1>Tambah aset baru</h1><p>Isi informasi aset agar mudah dicari dan dipantau. Kode tag dibuat otomatis setelah disimpan.</p></div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-4 sm:p-7 bg-white">

                    <form class="asset-edit-form" action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-5">
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Aset*</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 shadow-sm">
                            @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-5">
                            <label for="person_in_charge" class="block text-sm font-semibold text-gray-700 mb-1">Penanggung Jawab</label>
                            <input type="text" name="person_in_charge" id="person_in_charge" value="{{ old('person_in_charge') }}"
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 shadow-sm"
                                placeholder="Kosongkan jika tidak ada">
                            @error('person_in_charge') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                            <div>
                                <label for="location" class="block text-sm font-semibold text-gray-700 mb-1">Lokasi Aset</label>
                                <input type="text" name="location" id="location" value="{{ old('location') }}"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 shadow-sm"
                                    placeholder="Contoh: Gudang A / Site B">
                                @error('location') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="vendor" class="block text-sm font-semibold text-gray-700 mb-1">Vendor</label>
                                <input type="text" name="vendor" id="vendor" value="{{ old('vendor') }}"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 shadow-sm"
                                    placeholder="Contoh: PT Mitra Teknologi">
                                @error('vendor') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                            <div>
                                <label for="serial_number" class="block text-sm font-semibold text-gray-700 mb-1">Serial Number</label>
                                <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number') }}"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 shadow-sm"
                                    placeholder="SN-XXXX-XXXX">
                                @error('serial_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="warranty_expiry_date" class="block text-sm font-semibold text-gray-700 mb-1">Masa Garansi</label>
                                <input type="date" name="warranty_expiry_date" id="warranty_expiry_date" value="{{ old('warranty_expiry_date') }}"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 shadow-sm">
                                @error('warranty_expiry_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                            <div>
                                <label for="category" class="block text-sm font-semibold text-gray-700 mb-1">Kategori Aset*</label>
                                <select name="category" id="category" required
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 cursor-pointer shadow-sm">
                                    <option value="" disabled {{ old('category') ? '' : 'selected' }}>Pilih Kategori</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->slug }}" {{ old('category') == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">Status*</label>
                                <select name="status" id="status" required
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 cursor-pointer shadow-sm">
                                    <option value="" disabled {{ old('status') ? '' : 'selected' }}>Pilih Status</option>
                                    <option value="available" {{ (old('status') == 'available') ? 'selected' : '' }}>Tersedia</option>
                                    <option value="in_use" {{ (old('status') == 'in_use') ? 'selected' : '' }}>Sedang digunakan</option>
                                    <option value="maintenance" {{ (old('status') == 'maintenance') ? 'selected' : '' }}>Dalam perawatan</option>
                                    <option value="broken" {{ (old('status') == 'broken') ? 'selected' : '' }}>Rusak</option>
                                </select>
                                @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                            <div>
                                <label for="condition" class="block text-sm font-semibold text-gray-700 mb-1">Kondisi Fisik*</label>
                                <select name="condition" id="condition" required
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 cursor-pointer shadow-sm">
                                    <option value="baik" {{ (old('condition') == 'baik') ? 'selected' : '' }}>Baik</option>
                                    <option value="rusak" {{ (old('condition') == 'rusak') ? 'selected' : '' }}>Rusak</option>
                                    <option value="rusak_total" {{ (old('condition') == 'rusak_total') ? 'selected' : '' }}>Rusak Total</option>
                                </select>
                                @error('condition') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

               
                            <div>
                                <label for="purchase_date" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Pembelian*</label>
                                <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date') }}" required
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 shadow-sm">
                                @error('purchase_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-5">
                            <label for="price" class="block text-sm font-semibold text-gray-700 mb-1">Nilai Aset / Harga (Rp)*</label>
                            <input type="number" name="price" id="price" value="{{ old('price') }}" min="0" required
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 shadow-sm"
                                placeholder="Contoh: 15000000 (Tanpa titik)">
                            @error('price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-8">
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-1">Keterangan*</label>
                            <textarea name="description" id="description" rows="4" required
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 shadow-sm"
                                placeholder="Tambahkan deskripsi atau keterangan detail mengenai aset ini...">{{ old('description') }}</textarea>
                            @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-8">
                            <label for="attachments" class="block text-sm font-semibold text-gray-700 mb-1">Lampiran / Foto (Opsional)</label>
                            <input type="file" name="attachments[]" id="attachments" multiple
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 shadow-sm">
                            <p class="text-xs text-gray-500 mt-1">Maks 5MB/file. Format: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX.</p>
                            @error('attachments.*') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="asset-form-actions">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-2.5 bg-indigo-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md cursor-pointer">
                                Simpan aset
                            </button>

                            <a href="{{ route('assets.index') }}"
                                class="inline-flex items-center px-6 py-2.5 bg-gray-500 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm cursor-pointer">
                                Batal
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>