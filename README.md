# BlogVerse Pro — P8 Laravel 13 + Inertia + React

Aplikasi blog multi-tier sesuai Studi Kasus C pada modul Praktik Laravel 13, Inertia.js & React (Feature-First).

## Stack
- PHP 8.4+, Laravel 13, Inertia.js 2, React 19, Vite 7
- PostgreSQL 17+
- PhpSpreadsheet untuk impor/ekspor Excel
- TOAST UI Editor/Viewer untuk Markdown
- OpenAI Chat Completions API (opsional, perlu API key)

## Persyaratan lokal
PHP 8.4 dengan ekstensi `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `mbstring`, `openssl`, `pdo`, `pdo_pgsql`, `pdo_sqlite` (untuk test), `session`, `tokenizer`, `xml`, `zip`, `gd` (disarankan); Composer 2; Node.js 22+ dan npm; PostgreSQL 17+.

## Instalasi (Windows PowerShell)
1. Ekstrak ZIP, buka terminal di folder proyek.
2. Install dependensi:
   ```powershell
   composer install
   npm install
   ```
3. Salin `.env.example` menjadi `.env`, lalu sesuaikan koneksi PostgreSQL. Buat database:
   ```powershell
   psql -U postgres -c "CREATE DATABASE pabwe_p8_blog;"
   ```
4. Generate key dan siapkan database:
   ```powershell
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   ```
5. Jalankan aplikasi (dua terminal):
   ```powershell
   php artisan serve
   npm run dev
   ```
6. Buka `http://127.0.0.1:8000`.

Untuk deployment dan penilaian praktikum, atur `APP_URL` ke URL HTTPS wajib: `https://ifs24018-pabwe2026-sk-p8-laravel-inertia`. Pastikan URL publik dapat diakses dan jangan gunakan localhost/private IP untuk URL penilaian.

## Akun admin awal
Seeder membuat admin dari variabel `.env`:
- Username: nilai `ADMIN_USERNAME` (default `admin`)
- Email: nilai `ADMIN_EMAIL` (default `admin@example.com`)
- Password: nilai `ADMIN_PASSWORD` (default `ChangeMe123!`)

**Ganti password default sebelum digunakan.** Pendaftaran publik selalu membuat role `member`; role admin hanya dibuat oleh seeder atau diubah melalui akun admin yang sudah terautentikasi.

## OpenAI
Isi `OPENAI_API_KEY` dan (opsional) `OPENAI_MODEL=gpt-4o-mini` pada `.env`. Fitur inti tetap dapat dipakai tanpa API key; AI Generator menampilkan pesan konfigurasi bila key kosong.

## Format impor Excel
Baris pertama adalah header. Kolom berurutan: `Judul`, `Slug`, `Kategori`, `Ringkasan`, `Konten`, `Status`, `Penulis`, `Tanggal Publikasi`, `Dibuat`. Impor menggunakan Judul, Kategori, Ringkasan, Konten, dan Status; artikel hasil impor selalu dimiliki akun yang sedang login. Status hanya `Published` atau `Draft`.

## Pengujian dan pemeriksaan
```powershell
php artisan test
composer audit
vendor/bin/phpunit --coverage-text
```
Laporan coverage PHPUnit memerlukan driver PCOV atau Xdebug. Jenkins tetap menjalankan seluruh tes tanpa driver tersebut; jika driver tersedia, Jenkins juga membuat laporan coverage, tetapi coverage 100% bukan syarat build. Untuk mengejar coverage 100% pada `app/`, lengkapi tes untuk seluruh cabang controller/service/middleware dan jalankan PHPUnit dengan driver coverage. ZIP sumber ini menyertakan tes dasar fitur akses, publikasi, dan model; angka coverage harus diverifikasi, bukan diklaim dari proses pembuatan arsip.

## Struktur
Fitur backend berada di `app/Features/{Auth,Post,Admin,Ai,Profile}`. Route publik, member, dan admin dipisah middleware; kepemilikan artikel diverifikasi melalui Policy dan middleware. Halaman publik menggunakan layout tanpa sidebar, sedangkan workspace memakai sidebar.
