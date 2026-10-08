# Asset Tracker

Asset Tracker adalah aplikasi manajemen aset berbasis Laravel untuk pencatatan aset, approval perubahan data, export laporan, QR label, dan monitoring lifecycle aset.

## Fitur Utama

- CRUD aset, kategori, dan user berbasis role (`super_admin`, `admin`)
- Approval update/delete aset dan kategori
- Export laporan aset ke PDF dan CSV
- Generate dan cetak label QR code aset
- Riwayat transaksi aset, notifikasi, dan monitoring overdue
- Scheduler harian untuk ringkasan lifecycle aset

## Tech Stack

- Backend: Laravel 12, PHP 8.2+
- Frontend: Blade, Vite, Tailwind CSS 4, Alpine.js
- Database: SQLite atau MySQL 8+
- PDF / QR: `barryvdh/laravel-dompdf`, `simple-qrcode`
- Dev tooling: Laravel Breeze, Laravel Pail, Laravel Sail

## Kebutuhan Sistem

### Local development

- PHP 8.2 atau lebih baru
- Composer 2
- Node.js 20+ dan npm
- SQLite atau MySQL

### Docker development

- Docker Engine
- Docker Compose plugin
- Composer 2 untuk bootstrap dependency PHP pertama kali

## Catatan Environment

Beberapa default aplikasi penting untuk dipahami:

- `SESSION_DRIVER=database`
- `CACHE_STORE=database`
- `QUEUE_CONNECTION=database`
- `MAIL_MAILER=log` pada `.env.example`
- `APP_TIMEZONE=UTC` pada `.env.example`

Artinya:

- Jalankan migrasi agar tabel `sessions`, `cache`, `jobs`, dan `notifications` tersedia.
- Jika scheduler `assets:mark-overdue` harus berjalan pukul `07:00 WIB`, ubah `APP_TIMEZONE=Asia/Jakarta`.
- Untuk user non-seed yang harus verifikasi email, mailer production wajib dikonfigurasi.

## Setup Development

### Opsi A: Local tanpa Docker

1. Clone repository lalu masuk ke folder proyek.

```bash
git clone <repository-url>
cd asset-tracker
```

2. Install dependency backend dan frontend.

```bash
composer install
npm install
```

3. Buat file environment.

```bash
cp .env.example .env
```

4. Pilih database yang ingin dipakai.

Untuk SQLite:

```bash
touch database/database.sqlite
```

Lalu pastikan `.env` memakai konfigurasi berikut:

```env
APP_NAME="Asset Tracker"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=sqlite
```

Untuk MySQL, sesuaikan `.env` seperti ini:

```env
APP_NAME="Asset Tracker"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=asset_tracker
DB_USERNAME=root
DB_PASSWORD=
```

5. Generate app key, migrasi database, dan buat symbolic link storage.

```bash
php artisan key:generate
php artisan migrate
php artisan storage:link
```

6. Jika ingin akun awal dan sample data, jalankan seeder.

```bash
php artisan db:seed
```

Seeder local/testing akan membuat akun berikut:

- `super@vodeco.co.id` / `super-123`
- `admin@vodeco.co.id` / `admin-123`

Seeder juga mengisi kategori dan contoh data aset.

7. Jalankan environment development.

```bash
composer run dev
```

Perintah di atas menjalankan:

- `php artisan serve`
- `php artisan queue:listen --tries=1 --timeout=0`
- `php artisan pail --timeout=0`
- `npm run dev`

Jika ingin terpisah, Anda juga bisa menjalankan:

```bash
php artisan serve
npm run dev
```

8. Akses aplikasi di `http://127.0.0.1:8000`.

### Opsi B: Development dengan Laravel Sail

Mode ini cocok jika Anda ingin menjalankan aplikasi dan MySQL lewat Docker.

1. Install dependency PHP lalu buat file environment.

```bash
composer install
cp .env.example .env
```

2. Ubah `.env` agar memakai MySQL dari container Sail.

```env
APP_PORT=8000
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=asset_tracker
DB_USERNAME=sail
DB_PASSWORD=password
```

3. Jalankan container.

```bash
./vendor/bin/sail up -d
```

4. Install dependency frontend dari container lalu setup aplikasi.

```bash
./vendor/bin/sail npm install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan storage:link
```

5. Jika ingin sample data:

```bash
./vendor/bin/sail artisan db:seed
```

6. Jalankan Vite development server.

```bash
./vendor/bin/sail npm run dev
```

7. Akses aplikasi di `http://localhost:8000`.

## Command yang Sering Dipakai

```bash
composer run dev
composer run test
npm run build
php artisan optimize:clear
php artisan pail
```

## Testing

Untuk menjalankan test suite:

```bash
composer run test
```

atau:

```bash
php artisan test
```

## Build untuk Production

Jika asset frontend dibuild di server atau pipeline CI:

```bash
npm ci
npm run build
```

Output hasil build akan tersedia di `public/build`.

## Deployment Production

Bagian ini mengasumsikan Anda deploy ke Linux dengan Nginx + PHP-FPM.

### 1. Requirement server

- PHP 8.2+ beserta extension standar Laravel
- Composer 2
- MySQL 8+ / MariaDB
- Nginx atau Apache
- Node.js 20+ hanya jika build asset dilakukan di server

### 2. Clone source code

```bash
git clone <repository-url> /var/www/asset-tracker
cd /var/www/asset-tracker
```

### 3. Install dependency production

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Jika build asset dilakukan di CI/CD, Anda bisa deploy hasil `public/build` dan tidak perlu Node.js di server aplikasi.

### 4. Siapkan file `.env`

Minimal sesuaikan variabel berikut:

```env
APP_NAME="Asset Tracker"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://asset.example.com
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=asset_tracker
DB_USERNAME=asset_tracker
DB_PASSWORD=strong-password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_SECURE_COOKIE=true

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-user
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

Catatan:

- `APP_TIMEZONE=Asia/Jakarta` penting jika scheduler harus benar-benar berjalan pukul `07:00 WIB`.
- Mailer perlu aktif jika user baru harus melakukan verifikasi email.

### 5. Generate key, migrasi, dan storage link

```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
```

`php artisan key:generate` cukup dijalankan saat deployment pertama atau jika `APP_KEY` belum ada.

### 6. Permission folder writable

Pastikan web server dapat menulis ke `storage/` dan `bootstrap/cache/`.

Contoh umum:

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
```

### 7. Optimasi cache Laravel

```bash
php artisan optimize
```

Jika perlu reset cache saat troubleshooting:

```bash
php artisan optimize:clear
```

### 8. Web server harus mengarah ke folder `public`

Contoh konfigurasi minimal Nginx:

```nginx
server {
    listen 80;
    server_name asset.example.com;
    root /var/www/asset-tracker/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Sesuaikan versi PHP-FPM socket dengan server Anda.

### 9. Scheduler wajib diaktifkan

Aplikasi memiliki scheduler:

- `assets:mark-overdue` dijalankan setiap hari jam `07:00`

Tambahkan cron berikut:

```bash
* * * * * cd /var/www/asset-tracker && php artisan schedule:run >> /dev/null 2>&1
```

### 10. Queue worker opsional

Saat ini fitur inti aplikasi tidak bergantung pada queued job untuk berjalan normal, tetapi environment queue database sudah disiapkan. Jika nanti ada job/notifikasi yang di-queue, jalankan worker dengan process manager seperti `systemd` atau `supervisor`.

Contoh manual:

```bash
php artisan queue:work --tries=3 --timeout=90
```

### 11. Seeder di production

Jangan jalankan `php artisan db:seed --force` di production kecuali Anda memang ingin:

- membuat akun default admin
- mengisi kategori awal
- mengisi sample aset

Untuk environment non-local/testing, seeder mewajibkan:

```env
SEED_SUPER_ADMIN_PASSWORD=strong-super-password
SEED_ADMIN_PASSWORD=strong-admin-password
```

Karena `DatabaseSeeder` juga menambahkan sample aset, seeding penuh lebih cocok untuk bootstrap awal, demo, atau staging.

### 12. Checklist verifikasi setelah deploy

- Halaman login dapat diakses
- Login `super_admin` berhasil
- Upload file aset tersimpan dan bisa diakses lewat `storage`
- Export PDF / CSV berjalan
- Scheduler aktif
- Mail verifikasi email terkirim jika fitur itu digunakan

## Shortcut Bootstrap

Project ini memiliki script Composer berikut:

```bash
composer run setup
```

Script tersebut akan menjalankan `composer install`, membuat `.env` jika belum ada, generate key, migrate, `npm install`, dan `npm run build`.

Gunakan hanya setelah konfigurasi database di `.env` sudah benar. Jika memakai SQLite, buat dulu file `database/database.sqlite`.

## Dokumentasi Tambahan

Dokumen teknis tambahan tersedia di folder `docs/`.
