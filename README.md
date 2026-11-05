# Bandung Care Volunteer Recruitment Platform

Website open recruitment volunteer untuk komunitas sosial **Bandung Care**. Pengunjung dapat melihat event, membaca detail kegiatan, dan mendaftar sebagai volunteer tanpa membuat akun. Admin mengelola event, kategori, formulir dinamis per event, data pendaftar, status seleksi, ekspor CSV, dan informasi website melalui dashboard yang terlindungi.

## Fitur Utama

### Halaman publik

- Homepage dengan hero, pengenalan komunitas, event yang sedang membuka rekrutmen, alur menjadi volunteer, dan kontak.
- Daftar event dengan pencarian, filter kategori, filter status rekrutmen, sorting tanggal/nama, dan pagination.
- Detail event dengan poster, deskripsi, persyaratan, jadwal, kuota, status rekrutmen, serta daftar pertanyaan formulir.
- Formulir pendaftaran dinamis tanpa login, divalidasi di frontend dan backend.
- Pencegahan pendaftaran ganda berdasarkan kombinasi event dan email.
- Kode pendaftaran unik dan halaman cek status dengan URL bertanda tangan (*signed URL*).
- Status rekrutmen: `DRAFT`, `SCHEDULED`, `OPEN`, `CLOSED`, `COMPLETED`.

### Dashboard admin

- Login admin dengan rate limiting, session regeneration, dan logout yang menginvalidasi session.
- Middleware role + status aktif untuk seluruh route admin.
- Dashboard statistik: total event, event open recruitment, event akan datang, total pendaftar, pendaftar baru, pendaftar menunggu review, grafik pendaftar per event, dan event terbaru.
- CRUD event termasuk upload poster, pengaturan publikasi, jadwal, kuota, dan soft delete.
- CRUD kategori dengan proteksi kategori yang masih dipakai event.
- CRUD pertanyaan formulir per event: tipe input, pilihan jawaban, placeholder, bantuan, wajib/opsional, aktif/nonaktif, urutan naik/turun, batas panjang, dan preview formulir.
- Manajemen pendaftar: pencarian, filter event/status, detail jawaban, riwayat status, dan perubahan status.
- Ekspor CSV mengikuti filter aktif dengan proteksi formula injection.
- Pengaturan informasi website (nama situs, hero, tentang kami, kontak, footer) yang langsung tampil di halaman publik.
- Notifikasi email perubahan status pendaftaran melalui Laravel Mail.

### Keamanan

- CSRF protection pada semua form.
- Validasi server-side melalui Form Request dan aturan validasi dinamis yang dibatasi.
- Password hashing bawaan Laravel.
- Rate limiting login, pendaftaran, dan pencarian kode pendaftaran.
- Otorisasi route admin melalui middleware `auth` + `admin`.
- Escape output Blade untuk mencegah XSS.
- Upload poster dibatasi tipe gambar dan ukuran 2 MB, disimpan di disk `public`.
- Halaman status pendaftaran memakai *signed URL* dan hanya menampilkan informasi minimum (tanpa jawaban formulir, kontak, atau catatan internal).
- Kuota diperiksa di dalam transaksi database dengan `lockForUpdate` agar request bersamaan tidak melewati kuota.
- Unique constraint `(event_id, email)` mencegah duplikasi walaupun ada request bersamaan.
- Akses data pendaftar dan ekspor hanya untuk admin.

## Teknologi

| Komponen | Versi / Keterangan |
| --- | --- |
| PHP | 8.3+ (lingkungan pengembangan: PHP 8.4) |
| Laravel | 13.x |
| Database | MySQL 8+ untuk produksi, SQLite untuk pengembangan dan test |
| Template | Laravel Blade |
| CSS | Bootstrap 5.3 + Bootstrap Icons + kustomisasi CSS variables |
| Build tool | Vite 8 |
| Testing | PHPUnit 12 |
| Formatter | Laravel Pint |

> Proyek ini tidak memakai framework JavaScript tambahan. Interaksi menggunakan JavaScript/Bootstrap bawaan. Tailwind CSS masih terpasang di `devDependencies` tetapi belum dipakai; styling sepenuhnya menggunakan Bootstrap dan CSS kustom di `resources/css/app.css`.

## Persyaratan Sistem

- PHP 8.3 atau lebih baru dengan ekstensi `pdo`, `mbstring`, `openssl`, `fileinfo`, `json`, `tokenizer`, `xml`, `curl`.
- Composer 2.
- Node.js 20+ dan npm.
- MySQL 8+ (produksi) atau SQLite (pengembangan).
- Web server (Nginx/Apache) atau `php artisan serve` untuk pengembangan.

## Instalasi

```bash
git clone <repository-url> openVolunteer
cd openVolunteer

composer install
cp .env.example .env        # Windows: copy .env.example .env
php artisan key:generate

# sesuaikan konfigurasi database pada .env (lihat bagian berikut)
php artisan migrate --seed

npm install
npm run build

php artisan serve
```

Aplikasi tersedia di `http://127.0.0.1:8000`.

### Menggunakan SQLite (cepat untuk pengembangan)

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/ke/database/database.sqlite
```

Buat file database jika belum ada:

```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
```

### Menggunakan MySQL

Buat database terlebih dahulu:

```sql
CREATE DATABASE bandung_care CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Lalu atur `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bandung_care
DB_USERNAME=bandung_care
DB_PASSWORD=password_yang_kuat
```

Jalankan migrasi dan seeder:

```bash
php artisan config:clear
php artisan migrate --seed
```

## Environment

Contoh variabel penting (tanpa kredensial produksi):

```env
APP_NAME="Bandung Care"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://volunteer.bandungcare.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bandung_care
DB_USERNAME=bandung_care
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="volunteer@bandungcare.id"
MAIL_FROM_NAME="${APP_NAME}"

ADMIN_NAME="Admin Bandung Care"
ADMIN_EMAIL=admin@bandungcare.id
ADMIN_PASSWORD=

FILESYSTEM_DISK=local
```

Jangan commit file `.env`. Untuk produksi gunakan `APP_DEBUG=false`, HTTPS, dan password acak yang kuat.

## Membuat Akun Admin Pertama

Registrasi publik tidak tersedia. Admin pertama dapat dibuat dengan perintah Artisan atau seeder. Keduanya tidak menyimpan password plaintext di source code.

### Opsi 1 — Perintah Artisan (disarankan)

```bash
php artisan admin:password
```

Perintah interaktif akan meminta email, nama, dan password (input password tersembunyi). Perintah ini:

- Membuat akun admin baru jika email belum terdaftar.
- Mengatur ulang password jika akun sudah ada.
- Memastikan `role = admin` dan `is_active = true` sehingga akun langsung bisa dipakai login.

Mode non-interaktif untuk deployment/CI:

```bash
php artisan admin:password \
  --email=admin@bandungcare.id \
  --name="Admin Bandung Care" \
  --password="password-yang-kuat"
```

Jika `--password` dikosongkan pada mode non-interaktif, perintah membuat password acak dan menampilkannya sekali di terminal.

### Opsi 2 — Seeder

```bash
# isi ADMIN_NAME, ADMIN_EMAIL, dan ADMIN_PASSWORD di .env terlebih dahulu
php artisan config:clear
php artisan db:seed --class=AdminUserSeeder
```

Jika `ADMIN_PASSWORD` kosong, seeder membuat password acak dan menampilkannya sekali di terminal. Segera simpan atau ganti password tersebut.

Admin lain dapat ditambahkan dari menu **Akun Admin** pada dashboard.

### Tidak bisa login admin?

1. **Pastikan akun ada, berstatus admin, dan aktif.**

   ```bash
   php artisan admin:password --email=admin@bandungcare.id --password="password-baru-yang-kuat"
   ```

   Perintah ini sekaligus mengaktifkan kembali akun yang tidak sengaja dinonaktifkan.

2. **Buka halaman login yang benar:** `http://127.0.0.1:8000/admin/login` (bukan `/login`).

3. **Bersihkan cache konfigurasi/session setelah mengubah `.env`.**

   ```bash
   php artisan optimize:clear
   ```

4. **Cek pesan error di layar dan log aplikasi** pada `storage/logs/laravel.log`.

5. **Error *Vite manifest* pada halaman login** berarti aset frontend belum dibangun. Jalankan `npm run build` atau `npm run dev`.

6. **Error 419 Page Expired** biasanya karena session/cookie tidak valid. Pastikan `APP_KEY` sudah diisi (`php artisan key:generate`) dan `SESSION_DRIVER=database` sudah dimigrasikan.

7. **Error 403 setelah login** berarti `role` bukan `admin` atau `is_active = false`. Jalankan perintah pada langkah 1.

### Data demo (opsional)

Untuk mengisi dashboard dan halaman publik dengan contoh data saat pengembangan:

```bash
# Kategori dan event contoh
php artisan db:seed --class=EventSeeder

# Pendaftar contoh, pertanyaan formulir, jawaban, dan riwayat status
php artisan db:seed --class=RegistrationAnswerSeeder
php artisan db:seed --class=RegistrationStatusHistorySeeder
```

Semua seeder demo bersifat idempotent (memakai `updateOrCreate`) sehingga aman dijalankan berulang. Jangan menjalankan seeder demo pada database produksi.

## Menjalankan Aplikasi

Pengembangan:

```bash
php artisan serve
npm run dev        # terminal terpisah, atau gunakan composer run dev
```

Jika perubahan CSS/JS tidak muncul atau muncul error *Vite manifest*, jalankan:

```bash
npm run build
```

Produksi:

```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Menjalankan Test

```bash
php artisan test
```

Test menggunakan SQLite in-memory (lihat `phpunit.xml`) sehingga tidak menyentuh database pengembangan/produksi.

Menjalankan test tertentu:

```bash
php artisan test tests/Feature/EventRegistrationTest.php
php artisan test --filter=test_submission_is_rejected_when_quota_is_full
```

Format kode:

```bash
vendor/bin/pint --dirty
```

## Struktur Database

| Tabel | Fungsi |
| --- | --- |
| `users` | Akun admin (`role`, `is_active`, password hash). |
| `categories` | Kategori event, slug unik. |
| `events` | Data event, jadwal, kuota, status rekrutmen, publikasi, poster, soft delete. |
| `event_questions` | Pertanyaan formulir dinamis per event, opsi JSON, aturan validasi aman, urutan, status aktif. |
| `registrations` | Data utama pendaftar; unique `registration_code` dan `(event_id, email)`. |
| `registration_answers` | Jawaban dinamis dengan snapshot label, key, dan tipe pertanyaan. |
| `registration_status_histories` | Riwayat perubahan status beserta admin dan catatan. |
| `site_settings` | Informasi website yang dapat diedit admin. |
| `sessions`, `cache`, `jobs` | Tabel bawaan Laravel untuk session, cache, dan queue. |

Relasi utama:

- `Category` hasMany `Event`; `Event` belongsTo `Category`.
- `Event` belongsTo `User` (`created_by`); `Event` hasMany `EventQuestion` dan `Registration`.
- `Registration` belongsTo `Event`; hasMany `RegistrationAnswer` dan `RegistrationStatusHistory`.
- `RegistrationAnswer` belongsTo `EventQuestion` (nullable, tetap menyimpan snapshot historis).

## Route Utama

### Publik

| Method | URI | Name |
| --- | --- | --- |
| GET | `/` | `home` |
| GET | `/events` | `events.index` |
| GET | `/events/{event:slug}` | `events.show` |
| GET | `/events/{event:slug}/daftar` | `events.register` |
| POST | `/events/{event:slug}/daftar` | `events.register.store` |
| GET | `/pendaftaran` | `registrations.track` |
| POST | `/pendaftaran` | `registrations.lookup` |
| GET | `/pendaftaran/{registration}` (signed) | `registrations.show` |

### Autentikasi admin

| Method | URI | Name |
| --- | --- | --- |
| GET | `/admin/login` | `admin.login` |
| POST | `/admin/login` | `admin.login` |
| POST | `/admin/logout` | `admin.logout` |

### Dashboard (middleware `auth` + `admin`)

- `GET /admin/dashboard`
- Resource `/admin/events`
- `GET /admin/events/{event}/questions/preview`
- `POST /admin/events/{event}/questions/{question}/move`
- Resource `/admin/events/{event}/questions`
- Resource `/admin/categories`
- Resource `/admin/admins`
- `GET|PUT /admin/settings`
- `GET /admin/registrations`
- `GET /admin/registrations/export`
- `GET /admin/registrations/{registration}`
- `PATCH /admin/registrations/{registration}/status`

## Panduan Dashboard Admin

1. Login melalui `/admin/login` menggunakan akun admin yang aktif.
2. **Manajemen Event** — buat event, isi jadwal kegiatan dan periode pendaftaran, unggah poster, atur kuota, lalu tayangkan event.
3. **Pertanyaan Formulir** — tambahkan pertanyaan khusus event, atur tipe input, pilihan jawaban, wajib/opsional, urutan, dan batas panjang. Gunakan **Preview Formulir** untuk memeriksa tampilan sebelum publikasi.
4. **Data Pendaftar** — cari/filter pendaftar, buka detail untuk melihat jawaban, lalu ubah status (`PENDING`, `REVIEWED`, `ACCEPTED`, `REJECTED`, `WITHDRAWN`).
5. **Ekspor CSV** — ekspor mengikuti filter yang sedang aktif; jawaban dinamis dan snapshot label disertakan.
6. **Kategori Event** — kelola kategori; kategori yang masih dipakai tidak dapat dihapus.
7. **Pengaturan** — ubah identitas situs, hero, teks tentang kami, kontak, dan footer.
8. **Akun Admin** — tambah/nonaktifkan admin lain. Akun nonaktif langsung kehilangan akses dashboard.

## Catatan Status Rekrutmen

Pendaftaran hanya diterima jika seluruh syarat berikut terpenuhi:

- Event sudah dipublikasikan (`is_published = true`).
- Status rekrutmen `OPEN`.
- Waktu sekarang berada di antara `registration_start_at` dan `registration_end_at` (jika diisi).
- Kuota belum penuh (jika `quota` diisi).
- Event belum `COMPLETED` dan tidak ditutup manual.

Pemeriksaan dilakukan ulang di backend di dalam transaksi, bukan hanya berdasarkan tombol yang disembunyikan.

## Deployment

1. Siapkan server dengan PHP 8.3+, Composer, Node.js, dan MySQL.
2. Arahkan document root ke folder `public/`.
3. Clone kode, jalankan `composer install --no-dev --optimize-autoloader`.
4. Salin `.env.example` ke `.env`, isi kredensial produksi, lalu `php artisan key:generate`.
5. Jalankan `php artisan migrate --force`, lalu buat akun admin dengan `php artisan admin:password` atau `php artisan db:seed --class=AdminUserSeeder`.
6. Jalankan `npm ci && npm run build`.
7. Buat symlink storage: `php artisan storage:link`.
8. Set permission tulis untuk `storage/` dan `bootstrap/cache/`.
9. Aktifkan HTTPS, `APP_DEBUG=false`, dan cache konfigurasi/route/view.
10. Jalankan queue worker jika email/antrian digunakan: `php artisan queue:work --daemon`.
11. Jadwalkan `php artisan schedule:run` melalui cron jika ada tugas terjadwal.

Contoh konfigurasi Nginx:

```nginx
server {
    listen 443 ssl;
    server_name volunteer.bandungcare.id;
    root /var/www/openVolunteer/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Pengembangan Lanjutan

- Tambahkan tipe pertanyaan baru di `EventQuestion::TYPES`, validasi di `StoreRegistrationRequest`, dan render field di `resources/views/public/registrations/_question.blade.php`.
- Logika pendaftaran terpusat di `app/Services/RegistrationService.php`.
- Semua validasi admin berada di `app/Http/Requests`.
- Komponen UI reusable ada di `resources/views/components`.
- Setelah mengubah perilaku, tambahkan test fitur di `tests/Feature`.

---

## Project Brief (Referensi Awal)

# PROJECT: BANDUNG CARE VOLUNTEER RECRUITMENT PLATFORM

## 1. PERAN AI

Bertindaklah sebagai Senior Laravel Developer, Full-Stack Web Developer, Database Designer, dan UI/UX Designer.

Bantu saya membangun aplikasi web bernama Bandung Care Volunteer Recruitment Platform, yaitu website open recruitment volunteer untuk komunitas sosial Bandung Care.

Aplikasi harus memiliki tampilan modern, responsif, mudah digunakan, aman, dan memiliki struktur kode yang rapi sehingga dapat dikembangkan lebih lanjut.

Jangan hanya memberikan contoh kode atau pseudocode. Implementasikan fitur menggunakan kode Laravel yang benar-benar dapat dijalankan.

Jika terdapat file yang perlu dibuat atau diubah, jelaskan lokasi file dan berikan implementasi lengkapnya.

Gunakan pendekatan pengembangan bertahap agar setiap fitur dapat diuji sebelum melanjutkan ke fitur berikutnya.

## 2. TEKNOLOGI

Gunakan teknologi berikut:

- Laravel dengan versi stabil yang kompatibel dengan versi PHP yang tersedia.
- PHP.
- MySQL sebagai database utama.
- Laravel Blade sebagai template engine.
- Bootstrap 5 untuk styling.
- JavaScript untuk interaksi frontend.
- Laravel Eloquent ORM untuk operasi database.
- Laravel Migration untuk struktur tabel.
- Laravel Seeder dan Factory untuk data awal dan pengujian.
- Laravel Validation untuk validasi input.
- Laravel Authentication untuk autentikasi admin.
- Laravel Middleware untuk melindungi dashboard.
- Bootstrap Icons atau Lucide Icons untuk ikon.
- Laravel Storage untuk menyimpan gambar event.
- PHPUnit atau Pest untuk automated testing.

Periksa kompatibilitas versi Laravel, PHP, dan dependensi sebelum memulai implementasi.

Jangan menggunakan framework frontend tambahan yang tidak diperlukan.

## 3. TUJUAN APLIKASI

Aplikasi digunakan untuk mengelola open recruitment volunteer pada komunitas sosial Bandung Care.

Pengunjung dapat:

1. Melihat daftar seluruh event Bandung Care.
2. Melihat event yang sedang membuka rekrutmen.
3. Melihat event yang rekrutmennya telah ditutup atau belum dibuka.
4. Melihat detail setiap event.
5. Mengisi formulir pendaftaran volunteer tanpa membuat akun.
6. Mendapatkan konfirmasi setelah pendaftaran berhasil.

Admin dapat:

1. Login ke dashboard.
2. Mengelola data event.
3. Membuat dan mengelola pertanyaan formulir pendaftaran untuk setiap event.
4. Melihat seluruh peserta yang mendaftar.
5. Melihat jawaban setiap peserta.
6. Memfilter dan mencari peserta.
7. Mengubah status pendaftaran peserta.
8. Mengekspor data peserta.
9. Mengelola informasi yang ditampilkan pada website.

Tidak boleh ada halaman dashboard admin yang dapat diakses oleh pengunjung biasa.

## 4. DESAIN UI/UX

Buat desain website dengan identitas visual yang mengikuti color palette dari referensi gambar yang saya berikan.

Gunakan variabel CSS berikut:

--color-dark-teal: #164A46;
--color-cherry-rose: #B5185F;
--color-light-yellow: #FEFFEF;
--color-lime-cream: #D0E86E;
--color-strong-cyan: #35C1C2;

Catatan: Warna Light Yellow dapat disesuaikan jika diperlukan untuk mencocokkan warna referensi.

### Karakteristik desain

- Modern.
- Kreatif.
- Ramah anak muda.
- Menggambarkan semangat komunitas sosial.
- Memiliki identitas visual yang kuat.
- Bersih dan tidak terlalu ramai.
- Responsif pada desktop, tablet, dan smartphone.
- Memiliki tipografi yang jelas.
- Menggunakan rounded cards dan tombol yang konsisten.
- Menggunakan animasi ringan jika diperlukan.
- Memiliki empty state ketika belum ada event atau peserta.
- Memiliki loading state ketika melakukan proses yang memerlukan waktu.
- Memiliki pesan sukses dan error yang mudah dipahami.

Gunakan dark teal sebagai warna identitas utama, cherry rose untuk tombol penting, lime cream sebagai aksen, light yellow untuk area konten, dan strong cyan untuk elemen informasi.

Hindari penggunaan warna yang berlebihan.

Buat desain frontend dengan komponen yang konsisten agar mudah digunakan kembali.

## 5. STRUKTUR HALAMAN PUBLIC

### A. Homepage

Buat halaman utama dengan komponen berikut:

1. Navbar.
2. Hero section.
3. Pengenalan Bandung Care.
4. Section event yang sedang membuka rekrutmen.
5. Section event lainnya.
6. Cara menjadi volunteer.
7. Ajakan bergabung menjadi volunteer.
8. Footer.

Navbar menampilkan:

- Logo Bandung Care.
- Beranda.
- Tentang Kami.
- Daftar Event.
- Kontak.
- Tombol "Lihat Event".

Jangan tampilkan tombol login admin pada navbar publik.

Hero section berisi headline yang menarik, deskripsi singkat tentang Bandung Care, ilustrasi atau gambar kegiatan sosial, dan tombol untuk melihat event yang tersedia.

Contoh headline:

"Jadilah Bagian dari Perubahan Baik."

Contoh deskripsi:

"Bersama Bandung Care, setiap langkah kecil dapat membawa dampak besar bagi masyarakat."

Tampilkan event yang sedang membuka rekrutmen dalam bentuk kartu.

Setiap kartu event menampilkan:

- Poster event.
- Nama event.
- Kategori kegiatan.
- Tanggal kegiatan.
- Lokasi kegiatan.
- Batas akhir pendaftaran.
- Status rekrutmen.
- Tombol detail event.

Jika tidak ada event yang sedang membuka rekrutmen, tampilkan pesan bahwa belum ada rekrutmen yang tersedia.

### B. Halaman Daftar Event

Buat halaman untuk menampilkan semua event yang pernah atau sedang dikelola Bandung Care.

Sediakan:

- Daftar event.
- Pencarian berdasarkan nama event.
- Filter kategori.
- Filter status rekrutmen.
- Pagination.
- Sorting berdasarkan tanggal.
- Badge status pada setiap kartu.

Status rekrutmen yang ditampilkan:

- Segera Dibuka.
- Open Recruitment.
- Ditutup.
- Selesai.

Pengunjung harus tetap dapat melihat event yang rekrutmennya ditutup.

Namun, pengunjung tidak boleh mendaftarkan diri pada event yang tidak sedang membuka rekrutmen.

Gunakan query database dan pagination Laravel, bukan mengambil seluruh data sekaligus.

### C. Halaman Detail Event

Setiap event memiliki halaman detail tersendiri.

Tampilkan informasi berikut:

- Nama event.
- Poster.
- Deskripsi lengkap.
- Tujuan kegiatan.
- Kategori.
- Tanggal dan waktu kegiatan.
- Lokasi.
- Kuota volunteer.
- Persyaratan peserta.
- Tanggal mulai pendaftaran.
- Batas akhir pendaftaran.
- Status rekrutmen.
- Daftar pertanyaan atau informasi yang perlu dipersiapkan peserta.

Jika status rekrutmen OPEN dan masih dalam periode pendaftaran, tampilkan tombol "Daftar Menjadi Volunteer".

Jika pendaftaran belum dibuka atau sudah ditutup, sembunyikan tombol pendaftaran dan tampilkan informasi yang sesuai.

Jangan hanya mengandalkan tombol yang disembunyikan. Validasi status pendaftaran juga harus dilakukan pada backend.

Gunakan slug untuk URL event agar mudah dibaca.

Contoh:
- /events
- /events/volunteer-pendidikan-anak
- /events/bakti-sosial-bandung

## 6. FORM PENDAFTARAN VOLUNTEER TANPA LOGIN

Pengunjung tidak perlu membuat akun atau login untuk mendaftar menjadi volunteer.

Ketika pengunjung menekan tombol "Daftar Menjadi Volunteer", tampilkan halaman formulir pendaftaran khusus untuk event tersebut.

Formulir harus bersifat dinamis berdasarkan pertanyaan yang dibuat admin.

### Informasi dasar pendaftaran

Setiap pendaftaran minimal memiliki:

- Nama lengkap.
- Email.
- Nomor telepon atau WhatsApp.

Data dasar tersebut harus selalu tersedia dan tidak boleh dihapus oleh admin.

Admin dapat mengatur apakah informasi tambahan tertentu diperlukan, tetapi data identitas dasar tetap disediakan oleh sistem.

### Pertanyaan tambahan

Admin dapat menambahkan pertanyaan sesuai kebutuhan event, misalnya:

- Asal instansi.
- Universitas atau sekolah.
- Program studi.
- Usia.
- Alasan mengikuti kegiatan.
- Pengalaman menjadi volunteer.
- Keahlian yang dimiliki.
- Divisi yang diminati.
- Ketersediaan waktu.
- Riwayat organisasi.
- Tautan portofolio.
- Pertanyaan khusus lainnya.

Pertanyaan tambahan tidak boleh di-hardcode untuk setiap event.

Sistem harus mengambil konfigurasi pertanyaan dari database dan membuat formulir berdasarkan konfigurasi tersebut.

### Jenis pertanyaan yang didukung

Implementasikan jenis input berikut:

1. Text.
2. Textarea.
3. Email.
4. Number.
5. Date.
6. Select.
7. Radio.
8. Checkbox.
9. URL.
10. File upload jika diperlukan.

Untuk pertanyaan Select, Radio, dan Checkbox, admin dapat menentukan pilihan jawabannya.

Setiap pertanyaan memiliki properti:

- Label pertanyaan.
- Key atau identifier.
- Jenis input.
- Placeholder.
- Deskripsi atau petunjuk.
- Apakah wajib diisi.
- Urutan pertanyaan.
- Pilihan jawaban jika diperlukan.
- Aturan validasi.
- Status aktif atau nonaktif.

Pertanyaan dapat diurutkan menggunakan fitur drag-and-drop atau tombol naik dan turun.

Pertanyaan yang dinonaktifkan tidak ditampilkan pada formulir baru.

Jawaban dari pendaftar sebelumnya harus tetap tersimpan meskipun admin kemudian mengubah atau menonaktifkan pertanyaan.

### Validasi formulir

Terapkan validasi frontend dan backend.

Validasi backend harus menjadi sumber kebenaran.

Pastikan:

- Nama wajib diisi.
- Email valid.
- Nomor telepon memiliki format yang dapat diterima.
- Pertanyaan wajib diisi.
- Jenis jawaban sesuai dengan jenis pertanyaan.
- Jawaban pilihan hanya menerima opsi yang tersedia.
- File upload dibatasi berdasarkan tipe dan ukuran.
- File tidak dapat dieksekusi sebagai program.
- Input divalidasi menggunakan aturan Laravel.

Tampilkan pesan kesalahan di dekat field yang bermasalah.

Jika terjadi kesalahan validasi, pertahankan input sebelumnya selama aman untuk dilakukan.

### Pencegahan pendaftaran ganda

Cegah satu email mendaftar lebih dari sekali pada event yang sama.

Gunakan normalisasi email dan unique constraint pada database yang sesuai.

Jika pendaftar mencoba mendaftar kembali pada event yang sama menggunakan email yang sudah terdaftar, tampilkan pesan bahwa email tersebut sudah digunakan untuk pendaftaran event itu.

Jika sistem nantinya perlu mengizinkan pendaftaran ulang, desain mekanismenya agar dapat dikembangkan tanpa merusak struktur database.

### Keberhasilan pendaftaran

Setelah berhasil mendaftar:

- Simpan data pendaftaran.
- Simpan semua jawaban pertanyaan.
- Buat kode pendaftaran unik.
- Tampilkan halaman konfirmasi.
- Tampilkan nama event dan kode pendaftaran.
- Berikan informasi bahwa data pendaftaran telah diterima.

Jangan menampilkan data pribadi peserta lain kepada pengunjung.

Jangan menampilkan seluruh data pendaftaran melalui URL atau endpoint publik.

## 7. PENGELOLAAN STATUS REKRUTMEN

Setiap event memiliki status publik yang dapat dikonfigurasi:

- DRAFT.
- SCHEDULED.
- OPEN.
- CLOSED.
- COMPLETED.

Ketentuan:

DRAFT:
Event belum dipublikasikan dan hanya dapat dilihat admin.

SCHEDULED:
Event sudah dipublikasikan, tetapi pendaftaran belum dimulai.

OPEN:
Event dipublikasikan dan pendaftaran dapat dilakukan jika masih dalam periode pendaftaran serta kuota belum terpenuhi.

CLOSED:
Pendaftaran sudah ditutup.

COMPLETED:
Kegiatan telah selesai.

Status OPEN tidak boleh menjadi satu-satunya syarat pendaftaran.

Sistem juga harus memeriksa:

- Event telah dipublikasikan.
- Waktu saat ini sudah mencapai tanggal mulai pendaftaran.
- Waktu saat ini belum melewati batas akhir pendaftaran.
- Kuota belum terpenuhi jika kuota diterapkan.
- Event belum berstatus COMPLETED.
- Admin tidak menonaktifkan pendaftaran secara manual.

Sediakan pengaturan tanggal mulai dan akhir pendaftaran.

Jika tanggal akhir pendaftaran telah terlewati, event tidak boleh menerima pendaftar baru walaupun status database belum diperbarui secara manual.

Jika kuota telah terpenuhi, sistem harus menghentikan pendaftaran baru secara konsisten.

Lakukan pemeriksaan kuota di dalam transaksi database untuk mencegah dua pendaftaran terakhir melebihi kuota akibat request bersamaan.

## 8. ADMIN AUTHENTICATION

Dashboard hanya dapat diakses oleh admin.

Pengunjung tidak perlu login untuk melihat event dan mendaftar menjadi volunteer.

Implementasikan autentikasi admin menggunakan mekanisme autentikasi Laravel yang aman.

Jika starter kit autentikasi digunakan, pastikan implementasinya kompatibel dengan versi Laravel yang dipilih.

Buat halaman login admin dengan tampilan yang sesuai dengan identitas visual Bandung Care.

Field login:

- Email.
- Password.

Sediakan:

- Validasi login.
- Pesan error.
- Session regeneration setelah login.
- Logout.
- Session invalidation setelah logout.
- CSRF protection.
- Rate limiting untuk percobaan login.

Tambahkan role atau mekanisme otorisasi yang memastikan hanya akun dengan hak admin yang dapat mengakses dashboard.

Jangan mengandalkan penyembunyian tombol sebagai mekanisme keamanan.

Gunakan middleware dan policy atau mekanisme otorisasi Laravel yang sesuai.

Registrasi admin secara publik harus dinonaktifkan.

Admin pertama harus dibuat menggunakan seeder atau perintah Artisan yang aman.

Jangan menyimpan password dalam bentuk plaintext.

Gunakan hashing password Laravel.

Jangan memasukkan password produksi langsung ke source code, repository, atau seeder yang dibagikan.

Gunakan environment variables dan prosedur bootstrap admin yang aman.

## 9. ADMIN DASHBOARD

Buat dashboard dengan sidebar dan header.

Menu sidebar:

1. Dashboard.
2. Manajemen Event.
3. Pertanyaan Formulir.
4. Data Pendaftar.
5. Kategori Event.
6. Pengaturan.
7. Logout.

### A. Dashboard Overview

Tampilkan ringkasan statistik:

- Total event.
- Event yang sedang open recruitment.
- Event yang akan datang.
- Total pendaftar.
- Pendaftar baru.
- Total peserta per event.

Tambahkan tabel event terbaru dan pendaftaran terbaru.

Jika memungkinkan, tambahkan grafik jumlah pendaftar per event menggunakan library grafik yang ringan.

Semua statistik harus diambil dari database, bukan angka statis.

### B. CRUD Event

Admin harus dapat:

CREATE:
- Membuat event baru.
- Mengisi nama event.
- Membuat slug.
- Menentukan kategori.
- Mengisi deskripsi.
- Mengunggah poster.
- Menentukan lokasi.
- Menentukan tanggal kegiatan.
- Menentukan periode pendaftaran.
- Menentukan kuota.
- Mengisi persyaratan volunteer.
- Menentukan status event.
- Mengatur publikasi.

READ:
- Melihat daftar event.
- Melihat detail event.
- Mencari event.
- Memfilter event berdasarkan kategori dan status.
- Menggunakan pagination.

UPDATE:
- Mengedit informasi event.
- Mengganti poster.
- Mengubah kuota.
- Mengubah tanggal.
- Mengubah status rekrutmen.
- Mengatur publikasi.

DELETE:
- Menghapus event dengan konfirmasi.
- Mencegah kehilangan data pendaftar secara tidak sengaja.

Gunakan soft delete jika sesuai.

Jika event sudah memiliki pendaftar, prioritaskan pengarsipan event daripada penghapusan permanen.

Buat validasi backend untuk seluruh operasi CRUD.

Pastikan slug unik.

Pastikan tanggal kegiatan dan periode pendaftaran konsisten.

Jika terdapat pendaftaran yang sudah masuk, perubahan event tidak boleh menghapus data historis pendaftaran.

### C. CRUD Kategori Event

Admin dapat:

- Menambahkan kategori.
- Melihat kategori.
- Mengedit kategori.
- Menghapus kategori.
- Menentukan nama dan slug kategori.

Contoh kategori:

- Pendidikan.
- Lingkungan.
- Kemanusiaan.
- Kesehatan.
- Pengabdian Masyarakat.
- Donasi dan Sosial.

Kategori yang masih digunakan oleh event harus ditangani dengan aman ketika akan dihapus.

### D. CRUD Pertanyaan Formulir

Setiap event memiliki formulir yang dapat dikonfigurasi sendiri.

Admin harus dapat membuka halaman pengelolaan pertanyaan dari detail atau menu edit event.

Fitur:

- Tambah pertanyaan.
- Lihat daftar pertanyaan.
- Edit pertanyaan.
- Hapus atau arsipkan pertanyaan.
- Mengubah jenis input.
- Menambahkan pilihan jawaban.
- Menentukan apakah pertanyaan wajib diisi.
- Mengatur urutan pertanyaan.
- Menentukan placeholder.
- Menambahkan petunjuk.
- Mengaktifkan atau menonaktifkan pertanyaan.
- Melihat preview formulir sebagai pengunjung.

Admin dapat membuat formulir yang berbeda untuk setiap event.

Contoh:

EVENT A — Bakti Sosial:
- Nama lengkap.
- Email.
- Nomor WhatsApp.
- Asal instansi.
- Alasan mengikuti kegiatan.
- Pengalaman volunteer.

EVENT B — Volunteer Pendidikan:
- Nama lengkap.
- Email.
- Nomor WhatsApp.
- Universitas.
- Program studi.
- Pengalaman mengajar.
- Bidang pelajaran yang dikuasai.
- Ketersediaan waktu.

EVENT C — Aksi Lingkungan:
- Nama lengkap.
- Email.
- Nomor WhatsApp.
- Domisili.
- Pengalaman kegiatan lingkungan.
- Divisi pilihan.

Jangan membuat tabel database baru untuk setiap event.

Gunakan satu sistem formulir dinamis yang terhubung dengan event.

Jika pertanyaan diubah setelah pendaftaran masuk, jawaban lama tetap harus dapat dibaca oleh admin.

Pertimbangkan penyimpanan snapshot label dan jenis pertanyaan saat pendaftaran dibuat agar data historis tetap dapat ditampilkan dengan benar.

### E. Dashboard Pendaftar

Buat halaman untuk mengelola pendaftar dari semua event.

Admin dapat:

- Melihat semua pendaftar.
- Memilih event tertentu.
- Mencari nama pendaftar.
- Mencari email.
- Memfilter berdasarkan tanggal.
- Memfilter berdasarkan status pendaftaran.
- Membuka detail pendaftar.
- Melihat jawaban formulir.
- Mengubah status pendaftar.
- Mengekspor data ke CSV.

Setiap baris tabel menampilkan:

- Kode pendaftaran.
- Nama lengkap.
- Email.
- Nomor WhatsApp.
- Event.
- Waktu pendaftaran.
- Status pendaftaran.
- Tombol detail.

Gunakan pagination untuk daftar pendaftar.

### F. Status Pendaftar

Gunakan status berikut:

- PENDING.
- REVIEWED.
- ACCEPTED.
- REJECTED.
- WITHDRAWN.

Admin dapat mengubah status pendaftaran melalui dashboard.

Catat waktu perubahan status dan admin yang melakukan perubahan jika diperlukan.

Perubahan status tidak boleh menghapus jawaban formulir peserta.

Tampilkan badge berbeda untuk setiap status.

### G. Detail Pendaftar

Halaman detail peserta menampilkan:

- Identitas dasar.
- Informasi event.
- Waktu pendaftaran.
- Kode pendaftaran.
- Seluruh pertanyaan yang dijawab.
- Jawaban peserta.
- Status pendaftaran.
- Riwayat perubahan status jika tersedia.

Hanya admin yang memiliki hak akses yang boleh melihat informasi ini.

### H. Export Peserta

Admin dapat mengekspor data pendaftar dalam format CSV.

Fitur ekspor harus mendukung:

- Seluruh peserta.
- Peserta berdasarkan event.
- Peserta berdasarkan status.
- Peserta berdasarkan filter yang sedang aktif.

Data CSV harus memiliki header yang jelas.

Jawaban pertanyaan dinamis harus dapat disertakan dalam ekspor.

Tangani karakter khusus CSV dan formula injection agar data peserta yang diawali karakter seperti =, +, -, atau @ tidak dieksekusi ketika file dibuka dalam aplikasi spreadsheet.

Batasi ekspor hanya untuk admin.

## 10. DESAIN DATABASE MYSQL

Gunakan database relasional MySQL.

Buat migration, model, factory, dan seeder yang diperlukan.

Gunakan foreign key, index, unique constraint, dan tipe data yang sesuai.

Rancang database dengan tabel berikut.

### A. users

Untuk akun admin.

Kolom utama:

- id.
- name.
- email.
- password.
- role.
- is_active.
- email_verified_at jika diperlukan.
- remember_token.
- timestamps.

Batasi akses dashboard berdasarkan role dan status aktif.

### B. categories

Untuk kategori event.

Kolom:

- id.
- name.
- slug.
- description.
- timestamps.

Slug harus unik.

### C. events

Untuk informasi event.

Kolom:

- id.
- category_id.
- title.
- slug.
- summary.
- description.
- poster_path.
- location.
- event_start_at.
- event_end_at.
- registration_start_at.
- registration_end_at.
- quota.
- requirements.
- recruitment_status.
- is_published.
- created_by.
- timestamps.
- deleted_at jika menggunakan soft delete.

Gunakan foreign key yang sesuai.

Tentukan kebijakan ketika kategori atau akun pembuat event diubah atau dihapus.

### D. event_questions

Untuk pertanyaan formulir dinamis.

Kolom:

- id.
- event_id.
- label.
- field_key.
- type.
- placeholder.
- help_text.
- is_required.
- options JSON jika diperlukan.
- validation_rules JSON jika diperlukan.
- sort_order.
- is_active.
- timestamps.

Tambahkan unique constraint untuk kombinasi event_id dan field_key.

Pastikan opsi JSON dan aturan validasi hanya dapat berisi konfigurasi yang aman.

Jangan menerima aturan validasi Laravel arbitrer yang dikirim langsung oleh pengguna publik.

Validasi konfigurasi pertanyaan pada sisi admin.

### E. registrations

Untuk data utama pendaftaran.

Kolom:

- id.
- event_id.
- registration_code.
- full_name.
- email.
- phone.
- status.
- registered_at.
- reviewed_at jika diperlukan.
- timestamps.

Tambahkan unique constraint untuk registration_code.

Tambahkan unique constraint yang sesuai untuk event_id dan email yang telah dinormalisasi.

Jangan mengandalkan validasi frontend untuk mencegah pendaftaran ganda.

### F. registration_answers

Untuk jawaban formulir dinamis.

Kolom:

- id.
- registration_id.
- event_question_id nullable jika pertanyaan dapat diarsipkan.
- question_label_snapshot.
- question_key_snapshot.
- question_type_snapshot.
- answer_text atau answer_json sesuai desain.
- timestamps.

Jawaban harus tetap dapat dibaca walaupun label pertanyaan atau konfigurasi formulir berubah.

Satu pendaftaran dapat memiliki banyak jawaban.

Pertimbangkan tipe data JSON untuk jawaban checkbox atau jawaban yang terdiri dari beberapa pilihan.

### G. registration_status_histories

Opsional, untuk riwayat perubahan status.

Kolom:

- id.
- registration_id.
- changed_by.
- old_status.
- new_status.
- notes.
- created_at.

Gunakan foreign key yang sesuai dan pertimbangkan perilaku penghapusan data.

### H. Tabel tambahan

Jika dibutuhkan, tambahkan tabel untuk:

- Password reset.
- Session.
- Cache dan queue.
- Pengaturan website.
- Log aktivitas admin.

Gunakan fasilitas Laravel yang sesuai.

### Relasi database

Pastikan relasi berikut tersedia:

Category memiliki banyak Event.

Event dimiliki oleh Category.

Event dibuat oleh Admin.

Event memiliki banyak EventQuestion.

Event memiliki banyak Registration.

Registration dimiliki oleh Event.

Registration memiliki banyak RegistrationAnswer.

RegistrationAnswer terhubung ke pertanyaan yang relevan jika relasi tersebut masih tersedia.

Registration dapat memiliki banyak RegistrationStatusHistory.

Gunakan Eloquent Relationship seperti:

- hasMany.
- belongsTo.

Pastikan seluruh relasi memiliki foreign key dan index yang sesuai.

## 11. KEAMANAN APLIKASI

Terapkan keamanan secara menyeluruh.

Persyaratan:

- CSRF protection.
- Server-side validation.
- Authentication middleware.
- Authorization middleware atau policy.
- Password hashing.
- Login rate limiting.
- SQL injection protection melalui Eloquent atau query binding.
- XSS prevention melalui escaping Blade.
- Validasi file upload.
- Pembatasan ukuran file.
- Penyimpanan file pada disk yang sesuai.
- Proteksi terhadap mass assignment.
- Pembatasan akses data pendaftar.
- Pembatasan endpoint ekspor.
- Pembatasan akses halaman detail pendaftar.
- Pencegahan manipulasi ID dan akses data milik event lain.
- Logging error tanpa mengekspos data sensitif.
- Pengelolaan secret melalui environment variables.
- Error handling yang aman.
- Penggunaan HTTPS pada deployment produksi.

Gunakan rate limiting pada endpoint pendaftaran untuk mengurangi spam.

Gunakan validasi server-side dan mekanisme anti-bot yang sesuai jika dibutuhkan.

Jangan menampilkan stack trace pada lingkungan produksi.

Jangan menyimpan data pribadi peserta pada log secara berlebihan.

Gunakan prinsip least privilege untuk hak akses admin.

## 12. STRUKTUR FOLDER DAN ARSITEKTUR

Gunakan struktur standar Laravel.

Contoh komponen:

app/
  Http/
    Controllers/
      Public/
      Admin/
    Middleware/
    Requests/
  Models/
  Policies/
  Services/

resources/
  views/
    layouts/
    components/
    public/
    admin/
    auth/

routes/
  web.php

database/
  migrations/
  factories/
  seeders/

public/
  css/
  js/
  images/

Struktur tersebut merupakan panduan pengorganisasian, bukan kewajiban membuat seluruh folder jika tidak diperlukan.

Pisahkan controller publik dan admin agar lebih mudah dipelihara.

Gunakan Form Request untuk validasi yang kompleks.

Gunakan service class apabila proses bisnis cukup kompleks, seperti pendaftaran dinamis, pemeriksaan kuota, dan perubahan status.

Gunakan transaction database untuk proses pendaftaran yang melibatkan beberapa tabel.

Hindari menulis seluruh logika aplikasi di controller.

Jangan menggunakan satu controller besar untuk semua fitur.

Gunakan named routes dan route model binding.

## 13. DAFTAR ROUTE

Buat route publik seperti:

GET / 
GET /events
GET /events/{event:slug}
GET /events/{event:slug}/register
POST /events/{event:slug}/register
GET /registration/success/{registration_code}

Halaman konfirmasi hanya boleh mengungkap informasi minimum yang diperlukan dan tidak boleh mengekspos jawaban atau data pribadi peserta lain.

Buat route autentikasi admin seperti:

GET /admin/login
POST /admin/login
POST /admin/logout

Buat route dashboard admin yang dilindungi middleware seperti:

GET /admin/dashboard

CRUD event:

GET /admin/events
GET /admin/events/create
POST /admin/events
GET /admin/events/{event}/edit
PUT /admin/events/{event}
DELETE /admin/events/{event}

CRUD kategori:

GET /admin/categories
POST /admin/categories
PUT /admin/categories/{category}
DELETE /admin/categories/{category}

CRUD pertanyaan:

GET /admin/events/{event}/questions
POST /admin/events/{event}/questions
PUT /admin/events/{event}/questions/{question}
DELETE /admin/events/{event}/questions/{question}

Dashboard peserta:

GET /admin/registrations
GET /admin/registrations/{registration}
PATCH /admin/registrations/{registration}/status
GET /admin/registrations/export

Daftar di atas merupakan rancangan awal.

Sesuaikan route, controller, dan metode HTTP dengan implementasi Laravel yang benar.

Lindungi semua route admin dengan middleware autentikasi dan otorisasi.

Pastikan nested resource hanya dapat mengakses data milik event yang sesuai.

Gunakan route names yang konsisten.

## 14. KUALITAS FRONTEND

Buat komponen Blade reusable untuk:

- Navbar.
- Footer.
- Event card.
- Status badge.
- Alert.
- Pagination.
- Form field.
- Modal konfirmasi.
- Sidebar admin.
- Header dashboard.
- Empty state.

Gunakan layout terpisah untuk halaman publik dan dashboard admin.

Halaman publik harus nyaman digunakan melalui smartphone.

Halaman admin harus mudah digunakan untuk mengelola banyak event dan pendaftar.

Buat form event dengan section yang terorganisasi, seperti:

1. Informasi dasar.
2. Deskripsi kegiatan.
3. Poster.
4. Jadwal kegiatan.
5. Jadwal rekrutmen.
6. Kuota dan persyaratan.
7. Formulir pendaftaran.
8. Status publikasi.

Buat halaman formulir dinamis yang menampilkan jenis input sesuai konfigurasi pertanyaan.

Sediakan preview formulir sebelum event dipublikasikan.

## 15. NOTIFIKASI

Implementasikan pesan sukses dan gagal menggunakan session flash.

Contoh:

- Event berhasil dibuat.
- Event berhasil diperbarui.
- Event berhasil diarsipkan.
- Pertanyaan berhasil ditambahkan.
- Pendaftaran berhasil dikirim.
- Email sudah digunakan untuk mendaftar.
- Pendaftaran sudah ditutup.
- Kuota volunteer sudah terpenuhi.
- Akses ditolak.

Email konfirmasi kepada pendaftar dapat dibuat sebagai fitur opsional.

Jika email diimplementasikan, gunakan Laravel Mail dan konfigurasi SMTP melalui environment variables.

Jangan menganggap email telah terkirim jika proses pengiriman gagal.

## 16. PENGUJIAN

Buat automated test untuk fitur utama.

### Public tests

- Homepage dapat diakses.
- Daftar event menampilkan event yang dipublikasikan.
- Event draft tidak terlihat oleh pengunjung.
- Detail event tersedia.
- Event yang belum membuka pendaftaran tidak menerima pendaftaran.
- Event yang sudah ditutup tidak menerima pendaftaran.
- Pendaftaran tanpa login berhasil.
- Pertanyaan dinamis divalidasi.
- Email duplikat ditolak.
- Kuota tidak terlampaui.
- Kode pendaftaran unik.
- Jawaban tersimpan dengan benar.

### Admin tests

- Pengunjung tidak dapat membuka dashboard.
- Pengunjung tidak dapat mengelola event.
- Admin dapat login.
- Admin dapat membuat event.
- Admin dapat mengedit event.
- Admin dapat mengarsipkan event.
- Admin dapat membuat pertanyaan dinamis.
- Admin dapat melihat jawaban peserta.
- Admin dapat mengubah status pendaftaran.
- Admin dapat mengekspor data peserta.
- Admin yang tidak aktif tidak dapat menggunakan dashboard.

### Security tests

- CSRF dan autentikasi ditangani dengan benar sesuai konfigurasi Laravel.
- Input berbahaya tidak dieksekusi.
- File yang tidak diizinkan ditolak.
- Pengguna tidak dapat membaca data pendaftar melalui endpoint publik.
- Admin tidak dapat memodifikasi data event yang tidak sesuai dengan route binding.
- Percobaan login berlebihan dibatasi.
- Pendaftaran bersamaan tidak menyebabkan kuota terlampaui.

Gunakan database testing terpisah dan jangan menjalankan test yang menghapus data produksi.

## 17. DOKUMENTASI DAN INSTALASI

Buat README.md yang berisi:

- Deskripsi proyek.
- Teknologi yang digunakan.
- Persyaratan sistem.
- Langkah instalasi.
- Konfigurasi database MySQL.
- Konfigurasi environment.
- Cara menjalankan migration.
- Cara menjalankan seeder.
- Cara membuat akun admin pertama.
- Cara menjalankan server.
- Cara menjalankan automated test.
- Struktur database.
- Daftar route utama.
- Panduan penggunaan dashboard admin.
- Panduan deployment.

Sertakan contoh konfigurasi environment tanpa password produksi.

Gunakan perintah Artisan yang sesuai dengan versi Laravel.

Jangan menganggap file .env tersedia di repository.

## 18. PENGEMBANGAN BERTAHAP

Kerjakan proyek dalam tahap berikut.

TAHAP 1 — ANALISIS DAN SETUP
- Periksa versi PHP, Composer, Laravel, dan MySQL.
- Tentukan versi framework yang kompatibel.
- Buat proyek Laravel.
- Konfigurasikan database.
- Buat struktur folder.
- Siapkan layout frontend dan admin.

TAHAP 2 — DATABASE
- Buat migration.
- Buat model.
- Definisikan relasi.
- Buat factory dan seeder.
- Uji migration pada database development.

TAHAP 3 — AUTENTIKASI ADMIN
- Buat halaman login.
- Buat middleware dan otorisasi.
- Buat mekanisme bootstrap admin.
- Uji pembatasan akses dashboard.

TAHAP 4 — PUBLIC WEBSITE
- Buat homepage.
- Buat daftar event.
- Buat filter dan pencarian.
- Buat halaman detail event.
- Terapkan status rekrutmen.

TAHAP 5 — CRUD ADMIN
- Implementasikan CRUD event.
- Implementasikan CRUD kategori.
- Implementasikan upload poster.
- Terapkan pengarsipan event.

TAHAP 6 — FORMULIR DINAMIS
- Buat CRUD pertanyaan.
- Implementasikan tipe pertanyaan.
- Buat preview formulir.
- Terapkan validasi dinamis.
- Pastikan data historis tetap terbaca.

TAHAP 7 — PENDAFTARAN VOLUNTEER
- Buat form publik tanpa login.
- Simpan data peserta.
- Simpan jawaban dinamis.
- Terapkan validasi dan pencegahan duplikasi.
- Terapkan pemeriksaan kuota.
- Buat halaman konfirmasi.

TAHAP 8 — DASHBOARD PESERTA
- Buat statistik.
- Buat daftar pendaftar.
- Buat pencarian dan filter.
- Buat detail peserta.
- Buat pengelolaan status.
- Buat ekspor CSV.

TAHAP 9 — TESTING DAN KEAMANAN
- Jalankan seluruh test.
- Perbaiki error.
- Uji pembatasan akses.
- Uji validasi formulir.
- Uji upload.
- Uji kuota.
- Uji responsivitas.

TAHAP 10 — DOKUMENTASI
- Lengkapi README.
- Berikan petunjuk instalasi.
- Berikan petunjuk penggunaan.
- Jelaskan cara menambahkan fitur baru.

Pada akhir setiap tahap, tampilkan ringkasan implementasi, file yang dibuat atau diubah, perintah yang harus dijalankan, dan hasil pengujian.

Jangan melanjutkan dengan mengasumsikan tahap sebelumnya berhasil jika masih terdapat error yang belum diselesaikan.

## 19. ATURAN IMPLEMENTASI

Ikuti aturan berikut:

1. Jangan memberikan kode yang tidak lengkap.
2. Jangan menggunakan pseudocode untuk fitur inti.
3. Jangan menghilangkan migration atau relasi database yang diperlukan.
4. Jangan menggunakan data statis untuk menggantikan fitur yang seharusnya membaca database.
5. Jangan membuat akun admin melalui registrasi publik.
6. Jangan mewajibkan login bagi volunteer.
7. Jangan memperbolehkan pendaftaran event yang belum dibuka atau telah ditutup.
8. Jangan menghapus data pendaftar ketika event diedit atau diarsipkan.
9. Jangan membuat pertanyaan formulir yang hanya berlaku melalui hardcode.
10. Jangan menyimpan password tanpa hashing.
11. Jangan mengekspos informasi pribadi peserta kepada pengunjung.
12. Jangan mengabaikan validasi backend.
13. Jangan menuliskan seluruh aplikasi dalam satu file.
14. Jangan membuat file konfigurasi yang tidak sesuai dengan versi Laravel.
15. Jangan melanjutkan ke tahap berikutnya jika tahap saat ini masih gagal dijalankan.

Jika ada keputusan teknis yang belum ditentukan, pilih solusi yang sederhana, aman, dan sesuai praktik Laravel, lalu jelaskan alasannya.

Jika lingkungan pengembangan belum tersedia, berikan langkah setup terlebih dahulu.

## 20. OUTPUT PERTAMA YANG SAYA INGINKAN

Mulai dari Tahap 1.

Berikan:

1. Ringkasan arsitektur aplikasi.
2. Rekomendasi versi Laravel dan kebutuhan PHP yang kompatibel.
3. Struktur database awal beserta relasinya.
4. Daftar fitur dan halaman.
5. Daftar route utama.
6. Langkah membuat proyek Laravel.
7. Langkah konfigurasi MySQL.
8. Daftar file pertama yang harus dibuat.
9. Implementasi awal yang dapat langsung dijalankan.
10. Checklist untuk memastikan setup berhasil.

Setelah tahap pertama selesai, lanjutkan ke tahap berikutnya secara bertahap.

Target akhir adalah aplikasi Bandung Care Volunteer Recruitment Platform yang berfungsi penuh, menggunakan MySQL, memiliki frontend publik yang responsif, formulir pendaftaran dinamis, serta dashboard admin yang aman.