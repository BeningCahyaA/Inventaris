<div x-show="status === 'sold'" x-cloak x-transition class="mb-5 bg-blue-50 border border-blue-200 rounded-xl p-5">
    <h4 class="text-sm font-bold text-blue-800 mb-3 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        Informasi Penjualan
    </h4>

    <div class="bg-white border border-blue-100 rounded-lg p-3 mb-3 text-xs text-gray-600">
        <p>Aset yang dijual akan masuk ke Arsip Aset. Bukti penjualan disimpan dalam riwayat.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="sale_date" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Penjualan*</label>
            <input type="date" id="sale_date" name="sale_date" value="{{ old('sale_date') }}"
                :required="status === 'sold'" :disabled="status !== 'sold'"
                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
            @error('sale_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>
        <div>
            <label for="sale_price" class="block text-sm font-semibold text-gray-700 mb-1">Harga Jual (Rp)*</label>
            <input type="number" id="sale_price" name="sale_price" value="{{ old('sale_price') }}" min="0" step="0.01"
                :required="status === 'sold'" :disabled="status !== 'sold'"
                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm"
                placeholder="Contoh: 5000000 (Tanpa titik)">
            @error('sale_price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="mt-3">
        <label for="sale_buyer" class="block text-sm font-semibold text-gray-700 mb-1">Nama Pembeli</label>
        <input type="text" id="sale_buyer" name="sale_buyer" value="{{ old('sale_buyer') }}"
            :disabled="status !== 'sold'"
            class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm"
            placeholder="Nama pembeli">
        @error('sale_buyer') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    </div>

    <div class="mt-3">
        <label for="sale_proof" class="block text-sm font-semibold text-gray-700 mb-1">Bukti Penjualan*</label>
        <input type="file" id="sale_proof" name="sale_proof" accept=".jpg,.jpeg,.png,.pdf"
            :required="status === 'sold'" :disabled="status !== 'sold'"
            class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-white text-gray-900 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
        <p class="text-xs text-gray-500 mt-1">JPG, PNG, atau PDF, maksimal 5 MB. Pilih ulang file jika formulir gagal divalidasi.</p>
        @error('sale_proof') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    </div>

    <div class="mt-3">
        <label for="sale_notes" class="block text-sm font-semibold text-gray-700 mb-1">Catatan Penjualan</label>
        <textarea id="sale_notes" name="sale_notes" rows="2"
            :disabled="status !== 'sold'"
            class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm"
            placeholder="Opsional">{{ old('sale_notes') }}</textarea>
        @error('sale_notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    </div>
</div>