## Fitur Select All Across Pages - Asset Tracker

### Deskripsi
Fitur ini memungkinkan pengguna untuk memilih seluruh aset di semua halaman, bukan hanya di halaman yang sedang ditampilkan. Seleksi akan tetap tersimpan saat berpindah halaman.

### Komponen yang Diubah

#### 1. Backend - AssetController.php
**Method Baru: `getAllAssetIds()`**
- **Fungsi**: Mengambil semua ID aset tanpa pagination
- **Endpoint**: `/api/assets/all-ids`
- **Method**: GET
- **Parameter**: 
  - `search` (optional): Filter pencarian untuk nama, asset_tag, atau category
- **Response**:
```json
{
  "success": true,
  "total": 150,
  "ids": [1, 2, 3, ...]
}
```

#### 2. Routes - web.php
Route baru ditambahkan:
```php
Route::get('/api/assets/all-ids', [AssetController::class, 'getAllAssetIds'])->name('assets.all-ids');
```

#### 3. Frontend - print_preview.blade.php
**Perubahan JavaScript**:

1. **Fungsi `selectAllAcrossPages()`**
   - Memanggil API untuk mendapatkan semua ID aset
   - Menyimpan hasil ke localStorage
   - Mencentang semua checkbox yang ada di halaman saat ini
   - Menampilkan notifikasi sukses/error

2. **Perubahan `updateSelectAllButtonState()`**
   - Mendeteksi 3 state berbeda:
     - **Select All (putih)**: Belum semua data tercentang
     - **Deselect All (biru)**: Semua data di halaman saat ini tercentang
     - **Deselect All (ungu + counter)**: Semua data di semua halaman tercentang

3. **Logika Tombol Select All**:
   - Klik pertama: Pilih semua aset (across pages) via API
   - Klik kedua: Unselect semua aset

### Cara Kerja

1. **Inisialisasi**
   - Saat halaman dimuat, JavaScript membaca `selectedAssets` dari localStorage
   - Checkbox di halaman saat ini akan otomatis tercentang jika ID-nya ada di localStorage

2. **Select All Across Pages**
   - User klik tombol "Select All"
   - JavaScript memanggil API `/api/assets/all-ids` dengan parameter search (jika ada)
   - API mengembalikan array semua ID aset yang sesuai filter
   - ID disimpan ke localStorage
   - Checkbox di halaman saat ini dicentang

3. **Sinkronisasi UI Antar Halaman**
   - Saat user pindah halaman (via pagination atau search)
   - Function `initCheckboxListeners()` dipanggil
   - Checkbox akan otomatis tercentang jika ID-nya ada di localStorage

4. **State Management**
   - Seleksi disimpan di localStorage dengan key `selectedAssets`
   - Format: Array of string IDs `["1", "2", "3", ...]`
   - Persist sampai user melakukan unselect all atau clear browser data

### Visual Indicators

**Tombol Select All memiliki 3 state visual:**

1. **State Normal (Putih/Outline)**
   ```
   Text: "Select All"
   Color: bg-white border-gray-300
   Kondisi: Tidak semua data tercentang
   ```

2. **State Selected Current Page (Biru)**
   ```
   Text: "Deselect All"
   Color: bg-blue-600 text-white
   Kondisi: Semua data di halaman saat ini tercentang
   ```

3. **State Selected All Pages (Ungu + Counter)**
   ```
   Text: "Deselect All (150)"
   Color: bg-purple-600 text-white
   Kondisi: Semua data dari semua halaman tercentang
   ```

### Testing

#### Test Case 1: Select All Tanpa Filter
1. Buka halaman print preview
2. Klik "Select All"
3. Verifikasi: Semua checkbox tercentang
4. Verifikasi: Counter menampilkan total semua aset
5. Pindah ke halaman 2
6. Verifikasi: Checkbox tetap tercentang

#### Test Case 2: Select All Dengan Search Filter
1. Buka halaman print preview
2. Input search: "laptop"
3. Klik "Select All"
4. Verifikasi: Hanya aset yang match dengan "laptop" yang terseleksi
5. Clear search
6. Verifikasi: Seleksi hanya untuk aset yang match "laptop" tetap tersimpan

#### Test Case 3: Unselect All
1. Select all aset
2. Klik "Deselect All"
3. Verifikasi: Semua checkbox menjadi unchecked
4. Verifikasi: localStorage di-clear
5. Verifikasi: Counter hilang

#### Test Case 4: Manual Selection + Select All
1. Centang manual beberapa checkbox (misal 3 item)
2. Klik "Select All"
3. Verifikasi: Semua aset terseleksi (bukan hanya 3 item sebelumnya)
4. Verifikasi: Counter menampilkan total semua aset

#### Test Case 5: Print Selected
1. Select all aset
2. Klik "Print Selected"
3. Verifikasi: PDF generate dengan semua aset yang terseleksi

### Keuntungan Implementasi

✅ **User Experience**: User tidak perlu manual centang checkbox di setiap halaman
✅ **Efisiensi**: API hanya mengirim array ID, tidak seluruh data aset
✅ **Persistence**: Seleksi tersimpan saat pindah halaman atau search
✅ **Visual Feedback**: Jelas membedakan seleksi per-halaman vs semua halaman
✅ **Filter Support**: Mendukung kombinasi dengan fitur search
✅ **Clean Code**: Modular, reusable, dan mudah di-maintain

### Potensi Pengembangan

🔄 **Partial Selection**: Tambahkan fitur "Select All on This Page Only"
📊 **Bulk Actions**: Ekspansi untuk bulk edit/delete selain print
💾 **Backend Validation**: Validasi di backend untuk memastikan ID yang dikirim valid
🔔 **Progressive Loading**: Loading indicator untuk dataset yang sangat besar
