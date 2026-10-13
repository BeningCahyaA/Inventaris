<div class="mb-5">
    <label for="depreciation_rate" class="block text-sm font-semibold text-gray-700 mb-1">Penyusutan per tahun (%)</label>
    <input type="number" name="depreciation_rate" id="depreciation_rate"
        value="{{ old('depreciation_rate', isset($asset) ? $asset->depreciation_rate : 0) }}"
        min="0" max="100" step="0.01"
        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
    <p class="text-xs text-gray-500 mt-2">Dihitung dari harga beli awal setiap ulang tahun tanggal pembelian. Contoh: Rp10.000.000 × 10% = Rp1.000.000 per tahun, sampai nilai tersisa Rp0. Isi 0 untuk tanpa penyusutan.</p>
    <p class="text-xs text-gray-500 mt-1">Saat pertama diaktifkan, tahun penuh sejak tanggal pembelian ikut dihitung. Perubahan persentase berikutnya berlaku untuk penyusutan yang belum tercatat.</p>
    @error('depreciation_rate') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
</div>
