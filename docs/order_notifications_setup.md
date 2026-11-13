# Panduan Setup Notifikasi Email Otomatis NNACOSA
## PHP Native + MySQL + PHPMailer SMTP

Dokumen ini menjelaskan arsitektur, konfigurasi, pengoperasian, dan pemeliharaan fitur pengiriman email otomatis saat status pesanan diperbarui oleh Admin pada platform e-commerce **NNACOSA**.

---

## 1. Arsitektur & Alur Kerja

```
[Admin Update Status] (adminorder/update_status)
       │
       ▼
[Database Transaction (InnoDB)]
  ├── Kunci baris pesanan (SELECT ... FOR UPDATE)
  ├── Periksa apakah status berubah (old_status !== new_status)
  │     ├── Jika sama ──> Selesai (Tidak ada event baru / Idempoten)
  │     └── Jika berubah:
  ├── Perbarui status pesanan pada tabel `orders`
  ├── Buat event unik pada `order_notification_logs` (event_key UNIQUE, status PENDING)
  └── COMMIT Transaksi Database
       │
       ▼
[OrderNotificationService]
  ├── Klaim atomik baris notifikasi (status SENDING)
  ├── Ambil data valid langsung dari DB (Customer, Produk, Alamat, Kurir/Resi)
  ├── Render template email (HTML & TXT) dengan sanitasi XSS (Security::escape)
  ├── Kirim via PHPMailer SMTP
  │     ├── Sukses ──> Update log status = SENT, sent_at = NOW()
  │     ├── Timeout/Koneksi Ambigu ──> Update log status = REVIEW_REQUIRED
  │     └── Gagal ──> Update log status = FAILED (attempt_count + 1)
  └── Admin & Pelanggan melihat riwayat status terbaru
```

---

## 2. Dependensi & Instalasi

Proyek menggunakan **PHPMailer (v7.1.1)** yang diinstal melalui Composer.

- Lokasi Autoloader: `vendor/autoload.php`
- File dependensi: `composer.json` & `composer.lock`

Jika dependensi perlu dipasang ulang di server baru:
```bash
composer install --no-dev --optimize-autoloader
```

---

## 3. Konfigurasi Environment (`.env`)

Konfigurasi disimpan di file `.env` di root direktori proyek (telah ditambahkan ke `.gitignore`).

Salin dari template `.env.example`:
```bash
cp .env.example .env
```

Contoh konfigurasi `.env`:
```dotenv
APP_ENV=development
APP_URL=http://localhost/nnco_sa/public

# Database Configuration
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=nnaco_sa

# Mail / SMTP Configuration
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=toko.nnacosa@gmail.com
MAIL_PASSWORD=xxxx-xxxx-xxxx-xxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=toko.nnacosa@gmail.com
MAIL_FROM_NAME=NNACOSA
MAIL_TIMEOUT=15
MAIL_ENABLED=true

# Retry Configuration
MAIL_MAX_RETRIES=5
MAIL_RETRY_DELAY_MINUTES=5
```

### Panduan Gmail SMTP App Password:
1. Masuk ke Akun Google Anda -> **Keamanan (Security)**.
2. Aktifkan **Verifikasi 2 Langkah (2-Step Verification)** jika belum aktif.
3. Buka menu **Sandi Aplikasi (App Passwords)**: `https://myaccount.google.com/apppasswords`.
4. Buat sandi baru untuk aplikasi "NNACOSA Web".
5. Salin 16 digit kode sandi aplikasi yang dihasilkan ke variabel `MAIL_PASSWORD` di `.env` (tanpa spasi).
6. Jangan pernah menggunakan sandi login utama akun Google Anda!

### Alternatif SMTP Provider Transaksional:
- **SendGrid**: Host: `smtp.sendgrid.net`, Port: `587`, User: `apikey`, Pass: `[API_KEY]`
- **Mailgun**: Host: `smtp.mailgun.org`, Port: `587`
- **Amazon SES**: Host: `email-smtp.[region].amazonaws.com`, Port: `587`
- **Mailtrap** (Testing): Host: `sandbox.smtp.mailtrap.io`, Port: `2525`

---

## 4. Migrasi Database

Tabel `order_notification_logs` digunakan sebagai outbox log dan idempotensi pengiriman notifikasi.

File migrasi:
`database/migrations/create_order_notification_logs.sql`

Untuk menjalankan migrasi via CLI:
```bash
php scripts/run_migrations.php
```

Struktur Tabel `order_notification_logs`:
- `id`: INT AUTO_INCREMENT PRIMARY KEY
- `order_id`: INT(11) NOT NULL (Foreign Key ke `orders.id` ON DELETE CASCADE)
- `old_status`: VARCHAR(50) NOT NULL
- `new_status`: VARCHAR(50) NOT NULL
- `recipient_email`: VARCHAR(150) NOT NULL
- `recipient_name`: VARCHAR(100) NULL
- `event_key`: VARCHAR(100) NOT NULL UNIQUE
- `notification_status`: ENUM('PENDING','SENDING','SENT','FAILED','REVIEW_REQUIRED')
- `attempt_count`: INT(11) DEFAULT 0
- `max_attempts`: INT(11) DEFAULT 5
- `last_error`: TEXT NULL
- `sent_at`: DATETIME NULL
- `created_at`: TIMESTAMP DEFAULT CURRENT_TIMESTAMP
- `updated_at`: TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

---

## 5. Pengujian Sistem

### A. Menjalankan Automated Test Suite (16 Skenario)
Test suite menggunakan `MockMailer` sehingga tidak akan mengirimkan email sungguhan dan tidak mengubah data pesanan pelanggan asli.

```bash
php tests/test_order_notifications.php
```

Hasil yang dicakup:
1. Status PROCESSING -> SHIPPED membuat tepat satu event.
2. Status tidak berubah tidak membuat event baru.
3. Email customer diambil dari database yang benar.
4. Alamat email tidak valid ditolak.
5. SMTP sukses mencatat status `SENT` dan `sent_at`.
6. SMTP gagal mencatat status `FAILED` dan menambah `attempt_count`.
7. Timeout koneksi mencatat status `REVIEW_REQUIRED` (cegah duplikasi).
8. Dua request konkuren ditangani secara serial melalui row lock.
9. Dua worker konkuren diisolasi melalui klaim atomik.
10. Retry terhadap status `FAILED` berhasil diperbarui.
11. Event `SENT` tidak pernah dikirim ulang.
12. Otorisasi admin/operator diverifikasi.
13. Customer diisolasi dari pesanan pelanggan lain.
14. HTML template meng-escape karakter XSS.
15. Konfigurasi SMTP tidak lengkap ditangani dengan aman.
16. `MAIL_ENABLED=false` tidak memicu pengiriman email sungguhan.

### B. Menguji Koneksi SMTP Sungguhan
Setelah mengisi kredensial pada `.env`, uji koneksi menggunakan skrip diagnostik:

```bash
php scripts/test-smtp-connection.php tujuan_email@domain.com
```

---

## 6. Pemrosesan Retry Otomatis & Cron Worker

Untuk memproses notifikasi pending atau notifikasi yang gagal kirim (status `FAILED` dengan `attempt_count < 5` dan jeda bertahap):

### Eksekusi Manual (Development):
```bash
php scripts/retry-order-notifications.php --limit=10 --delay=5
```

### Konfigurasi Cron pada Server Linux (Production):
Buka crontab:
```bash
crontab -e
```
Tambahkan baris berikut untuk menjalankan worker setiap 5 menit:
```cron
*/5 * * * * /usr/bin/php /var/www/html/nnco_sa/scripts/retry-order-notifications.php >> /var/www/html/nnco_sa/logs/notification-worker.log 2>&1
```

### Konfigurasi Windows Task Scheduler (Lokal):
1. Buka Task Scheduler Windows.
2. Buat "Basic Task" dengan trigger "Daily" / "Repeat task every 5 minutes".
3. Action: "Start a program".
4. Program/script: `C:\xampp\php\php.exe`.
5. Add arguments: `scripts/retry-order-notifications.php --limit=10`.
6. Start in: `C:\xampp\htdocs\nnco_sa`.

---

## 7. Pemantauan & Status untuk Admin

1. Buka halaman Detail Pesanan di admin: `http://localhost/nnco_sa/public/adminorder/detail/{id}`.
2. Pada panel sebelah kanan terdapat bagian **Notifikasi Email**:
   - Menampilkan status pengiriman (`Terkirim`, `Antrean`, `Sedang Diproses`, `Gagal`, atau `Perlu Pemeriksaan`).
   - Riwayat transisi status (misal: `PENDING -> PROCESSING`).
   - Alamat email penerima.
   - Waktu pengiriman atau pesan kesalahan yang telah disanitasi.
   - Tombol **Coba Kirim Ulang** jika pengiriman berstatus `FAILED` (dilindungi token CSRF dan pemeriksaan role).
