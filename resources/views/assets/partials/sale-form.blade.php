<div x-show="status === 'sold'" x-cloak class="mb-6 rounded-xl border border-purple-200 bg-purple-50 p-4 space-y-3">
    <h3 class="font-semibold text-gray-900">Informasi penjualan</h3>
    <p class="text-xs text-gray-600">Aset yang dijual akan masuk ke Arsip Aset. Bukti penjualan disimpan dalam riwayat.</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div><label for="sale_date" class="block text-sm font-medium mb-1">Tanggal penjualan*</label>
            <input type="date" id="sale_date" name="sale_date" value="{{ old('sale_date') }}" :required="status === 'sold'" :disabled="status !== 'sold'" class="w-full border-gray-300 rounded-lg focus:ring-purple-500">
            @error('sale_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div><label for="sale_price" class="block text-sm font-medium mb-1">Harga jual (Rp)*</label>
            <input type="number" id="sale_price" name="sale_price" value="{{ old('sale_price') }}" min="0" step="0.01" :required="status === 'sold'" :disabled="status !== 'sold'" class="w-full border-gray-300 rounded-lg focus:ring-purple-500">
            @error('sale_price') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>
    <div><label for="sale_buyer" class="block text-sm font-medium mb-1">Nama pembeli</label>
        <input type="text" id="sale_buyer" name="sale_buyer" value="{{ old('sale_buyer') }}" :disabled="status !== 'sold'" class="w-full border-gray-300 rounded-lg focus:ring-purple-500">
        @error('sale_buyer') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
    <div><label for="sale_proof" class="block text-sm font-medium mb-1">Bukti penjualan*</label>
        <input type="file" id="sale_proof" name="sale_proof" accept=".jpg,.jpeg,.png,.pdf" :required="status === 'sold'" :disabled="status !== 'sold'" class="w-full text-sm">
        <p class="text-xs text-gray-500 mt-1">JPG, PNG, atau PDF, maksimal 5 MB. Pilih ulang file jika formulir gagal divalidasi.</p>
        @error('sale_proof') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
    <div><label for="sale_notes" class="block text-sm font-medium mb-1">Catatan penjualan</label>
        <textarea id="sale_notes" name="sale_notes" rows="2" :disabled="status !== 'sold'" class="w-full border-gray-300 rounded-lg focus:ring-purple-500">{{ old('sale_notes') }}</textarea>
        @error('sale_notes') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
