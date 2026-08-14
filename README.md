# Frank Shines Music & Lyrics — website lokal XAMPP

Website PHP + MySQL untuk menampilkan video YouTube, katalog lagu, lirik, kredit karya, dan panel admin lokal. Tidak memakai framework atau Composer sehingga cocok untuk PHP 8.2 bawaan XAMPP.

Nama website sudah disiapkan sebagai **Frank Shines**, dengan rencana domain produksi **FrankShines.com**. Domain tersebut baru aktif setelah hosting, pembelian domain, dan DNS selesai diatur.

## Fitur

- Beranda sinematik dengan video YouTube lagu unggulan sebagai background (autoplay tanpa suara).
- Gambar fallback dan thumbnail YouTube otomatis.
- Katalog lagu dengan pencarian judul, artis, album, atau songwriter.
- Halaman detail berisi player YouTube, kredit, cerita lagu, dan pembaca lirik.
- Tombol salin lirik serta pengatur besar tulisan.
- Admin login, tambah/edit/hapus lagu, draft/published, dan pemilihan lagu unggulan.
- Pengaturan nama website, tagline, kanal YouTube, Instagram, dan email.
- PDO prepared statements, password hash, CSRF token, session aman, validasi URL/ID YouTube.
- Responsif untuk desktop dan HP, serta menghormati `prefers-reduced-motion`.

## Cara paling cepat menjalankan

### 1. Siapkan database

1. Buka XAMPP Control Panel.
2. Jalankan **Apache** dan **MySQL**.
3. Buka `http://localhost/phpmyadmin`.
4. Pilih tab **Import**.
5. Pilih file `S:\fsw\database\schema.sql`, lalu klik **Import/Go**.

File SQL akan membuat database internal `jmi_music` dan satu lagu contoh “Thy Cross”. Nama database ini tidak terlihat oleh pengunjung dan boleh tetap dipakai untuk website Frank Shines. Lirik contoh sengaja kosong agar Anda memasukkan lirik milik sendiri melalui admin.

### 2. Jalankan website

Karena Apache XAMPP pada komputer ini sudah diarahkan ke proyek lain (`PWADashboard2`), cara aman adalah PHP development server. Buka PowerShell lalu jalankan:

```powershell
& "C:\xampp\php\php.exe" -S localhost:8080 -t "S:\fsw\public"
```

Biarkan jendela PowerShell tersebut terbuka, lalu akses:

- Website: `http://localhost:8080`
- Setup admin pertama: `http://localhost:8080/admin/setup.php`

Pada halaman setup, buat username dan password pilihan Anda. Demi keamanan, pembuatan admin pertama hanya diizinkan dari `localhost`. Setelah akun dibuat, halaman setup otomatis terkunci dan pengunjung diarahkan ke login.

### 3. Isi website

1. Masuk ke `http://localhost:8080/admin/login.php`.
2. Pilih **Edit** pada “Thy Cross” untuk memasukkan lirik yang Anda miliki/izinkan.
3. Pilih **Tambah lagu** untuk karya berikutnya.
4. Tempel URL YouTube lengkap; website mengambil ID video secara otomatis.
5. Centang **Jadikan lagu unggulan** agar videonya menjadi background beranda.
6. Buka **Pengaturan situs** untuk mengganti nama, tagline, dan tautan sosial.

## Konfigurasi database

Konfigurasi bawaan mengikuti XAMPP standar:

```text
host      127.0.0.1
port      3306
database  jmi_music
username  root
password  (kosong)
```

Jika berbeda, salin `config/config.local.example.php` menjadi `config/config.local.php`, lalu ubah nilainya. File lokal tersebut sudah masuk `.gitignore` agar password tidak ikut terunggah saat nanti memakai Git. Opsi `debug` pada file contoh hanya untuk pemeriksaan lokal; biarkan `false` di hosting.

Jika website dipasang pada subfolder Apache, isi `base_url` di `config/config.local.php`, misalnya `/frank-shines/public`. Jika memakai perintah server port 8080 di atas, biarkan kosong.

## Struktur singkat

```text
app/                 fungsi database, auth, CSRF, dan partial tampilan
config/              konfigurasi aplikasi/database
database/schema.sql  skema + data contoh untuk phpMyAdmin
public/               document root website
public/admin/         panel admin
public/assets/        CSS dan JavaScript
```

## Saat nanti pindah ke hosting

- Arahkan document root domain ke folder `public`, bukan ke root proyek.
- Ubah kredensial database di `config/config.local.php`.
- Pertahankan `debug` bernilai `false` agar detail error server tidak tampil ke publik.
- Ekspor database lokal setelah akun admin dibuat, lalu impor hasilnya ke database hosting; setup admin dari internet memang sengaja diblokir.
- Gunakan HTTPS dan password database/admin yang kuat.
- Jangan pernah membuka phpMyAdmin untuk umum tanpa perlindungan.
- Hanya terbitkan foto, video, dan lirik yang Anda miliki atau sudah mendapat izin.
