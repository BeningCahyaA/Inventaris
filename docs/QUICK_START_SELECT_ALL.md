# Panduan Quick Start - Select All Across Pages

## Untuk Developer

### 1. Setup Testing Environment
```bash
# Jalankan migrasi dan seeder (jika ada)
php artisan migrate:fresh

# Jalankan test
php artisan test --filter AssetControllerTest
```

### 2. Testing Manual di Browser

#### Persiapan Data:
1. Login ke aplikasi
2. Pastikan ada minimal 15-20 aset (lebih dari 1 halaman dengan pagination 10 item/page)
3. Buka halaman: `/assets/print`

#### Skenario 1: Select All Basic
1. Klik tombol **"Select All"**
2. Tunggu loading (akan muncul text "Loading...")
3. Akan muncul SweetAlert: "150 aset dipilih dari semua halaman" (sesuai jumlah data)
4. Perhatikan:
   - Tombol berubah menjadi **"Deselect All (150)"** dengan warna ungu
   - Counter menampilkan: "150 Selected"
   - Semua checkbox di halaman saat ini tercentang
5. Pindah ke halaman 2 (klik pagination)
6. Verifikasi: Semua checkbox di halaman 2 juga tercentang

#### Skenario 2: Select All dengan Search Filter
1. Ketik di search box: "laptop"
2. Tunggu live search selesai
3. Klik tombol **"Select All"**
4. Akan muncul notifikasi: "25 aset dipilih" (hanya yang match "laptop")
5. Clear search (hapus text di search box)
6. Verifikasi: Hanya 25 checkbox tercentang (aset yang match "laptop")

#### Skenario 3: Unselect All
1. Select all aset terlebih dahulu
2. Klik tombol **"Deselect All (150)"**
3. Verifikasi:
   - Semua checkbox menjadi unchecked
   - Tombol kembali ke **"Select All"** warna putih
   - Counter hilang
   - Tombol "Print Selected" menjadi disabled

#### Skenario 4: Persistence Across Navigation
1. Select all aset
2. Buka tab baru, navigasi ke halaman lain (misal: Dashboard)
3. Kembali ke `/assets/print`
4. Verifikasi: Seleksi masih tersimpan (checkbox masih tercentang)
5. Close browser dan buka lagi
6. Login dan buka `/assets/print`
7. Verifikasi: Seleksi hilang (karena new session)

#### Skenario 5: Print Selected
1. Select all aset (atau select beberapa saja)
2. Klik tombol **"Print Selected"**
3. Verifikasi: PDF terbuka di tab baru dengan semua aset yang terseleksi

### 3. Debugging

#### Cek localStorage:
Buka Browser Console (F12) → Console tab:
```javascript
// Lihat isi selectedAssets
localStorage.getItem('selectedAssets')

// Clear manual jika perlu
localStorage.removeItem('selectedAssets')
```

#### Cek API Response:
Buka Browser Console (F12) → Network tab:
1. Filter: XHR
2. Lakukan "Select All"
3. Cari request ke `/api/assets/all-ids`
4. Klik request tersebut → Preview/Response
5. Verifikasi struktur JSON:
```json
{
  "success": true,
  "total": 150,
  "ids": [1, 2, 3, ...]
}
```

## Untuk QA/Tester

### Checklist Testing:

**Functional Testing:**
- [ ] Select All berhasil memilih semua aset
- [ ] Deselect All berhasil menghapus semua seleksi
- [ ] Checkbox tetap tercentang saat pindah halaman
- [ ] Search filter bekerja dengan Select All
- [ ] Print Selected menghasilkan PDF yang benar
- [ ] Counter menampilkan jumlah yang akurat

**UI/UX Testing:**
- [ ] Tombol berubah warna sesuai state (putih/biru/ungu)
- [ ] Loading state muncul saat fetch API
- [ ] Notifikasi SweetAlert muncul dengan pesan yang jelas
- [ ] Tidak ada flicker atau lag saat centang/uncentang

**Edge Cases:**
- [ ] Select All saat tidak ada data → Tidak error
- [ ] Select All dengan search yang tidak match → Notifikasi 0 aset
- [ ] Centang manual beberapa item, lalu Select All → Semua terseleksi
- [ ] Select All, lalu uncheck manual 1 item → Count berkurang

**Browser Compatibility:**
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

**Performance:**
- [ ] Select All dengan 1000+ aset → Response time < 2 detik
- [ ] Tidak ada memory leak saat multi select/deselect
- [ ] LocalStorage tidak overflow (max ~5MB)

## Troubleshooting

### Issue: Checkbox tidak tercentang saat pindah halaman
**Solusi:**
1. Cek console browser untuk error JavaScript
2. Pastikan `initCheckboxListeners()` dipanggil setelah AJAX load
3. Verifikasi localStorage tidak di-block oleh browser

### Issue: API mengembalikan error 401 (Unauthorized)
**Solusi:**
1. Pastikan user sudah login
2. Cek session masih valid
3. Verifikasi route ada di dalam group auth middleware

### Issue: Select All tidak memanggil API
**Solusi:**
1. Cek console browser untuk error
2. Pastikan route 'assets.all-ids' terdaftar: `php artisan route:list | grep all-ids`
3. Verifikasi CSRF token valid

### Issue: PDF tidak generate dengan benar
**Solusi:**
1. Cek method `downloadPdf()` di AssetController
2. Pastikan parameter `selected_assets[]` diterima dengan benar
3. Verifikasi DomPDF library terinstall

## Notes untuk Deployment

1. **Sebelum Deploy:**
   - Run all tests: `php artisan test`
   - Clear cache: `php artisan cache:clear`
   - Optimize routes: `php artisan route:cache`

2. **Setelah Deploy:**
   - Test fitur di production environment
   - Monitor error logs untuk unexpected issues
   - Verifikasi performance dengan real data

3. **Rollback Plan:**
   - Jika ada issue critical, revert ke commit sebelumnya
   - File yang berubah:
     - `app/Http/Controllers/AssetController.php`
     - `routes/web.php`
     - `resources/views/assets/print_preview.blade.php`
