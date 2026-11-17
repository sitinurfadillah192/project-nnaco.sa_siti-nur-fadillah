# NNACOSA — Platform E-Commerce Butik Perhiasan & Aksesoris Mewah

[![NNACOSA CI/CD Pipeline](https://github.com/sitinurfadillah192/project-nnaco.sa_siti-nur-fadillah/actions/workflows/ci.yml/badge.svg)](https://github.com/sitinurfadillah192/project-nnaco.sa_siti-nur-fadillah/actions/workflows/ci.yml)

> **Tugas Akhir Mata Kuliah:** Rekayasa Perangkat Lunak (RPL)  
> **Arsitektur Sistem:** PHP Native Custom MVC, MySQL / MariaDB, Multi-Provider AI Engine, WhatsApp Gateway (Fonnte)  
> **Desain Visual:** *Adelina Soft Luxury Palette* (Warm Ivory, Deep Brown, Champagne Gold)

---

## 📋 Daftar Isi
1. [Identitas Anggota Kelompok](#1-identitas-anggota-kelompok)
2. [Tema dan Topik Proyek](#2-tema-dan-topik-proyek)
3. [Deskripsi Aplikasi](#3-deskripsi-aplikasi)
4. [Fitur Utama Aplikasi](#4-fitur-utama-aplikasi)
5. [Integrasi & Fitur AI](#5-integrasi--fitur-ai)
6. [Tautan Video Demonstrasi](#6-tautan-video-demonstrasi)
7. [Panduan Instalasi & Menjalankan Sistem](#7-panduan-instalasi--menjalankan-sistem)
8. [Akun Uji Coba (Demo Testing)](#8-akun-uji-coba-demo-testing)
9. [Pengujian Otomatis (Automated Testing)](#9-pengujian-otomatis-automated-testing)
10. [Struktur Direktori Proyek](#10-struktur-direktori-proyek)

---

## 1. Identitas Anggota Kelompok

*Silakan lengkapi identitas anggota kelompok di bawah ini:*

* **Nama Kelompok:** Sumber City
* **Kelas / Program Studi:** TI-1C / Teknik Informatika
* **Dosen Pengampu:** Yudhistira Arie Wijaya, M.Kom.

| No | Nama Lengkap | NIM | Peran / Kontribusi Utama |
|:--:|:-------------|:---:|:-------------------------|
| 1. | **Nizar Firmansyah** *(Koordinator)* | 41257147 | Frontend Styling (Adelina Luxury), Controller Logic, Git Management |
| 2. | **Siti Nur Fadillah** | 41257241 | Backend Model, Database Schema, Testing |
| 3. | **Talita Nabila** | 41257242 | Integrasi AI Chatbot, Dokumentasi & PRD |
| 4. | **Sulistina Juliyanti** | 41257161 | Integrasi WhatsApp Gateway, UI Testing |
| 5. | **Syamsul Hadi** | 41257163 | Quality Assurance, UI/UX Design |

---

## 2. Tema dan Topik Proyek

* **Judul Proyek:**  
  **"Pengembangan Sistem Informasi E-Commerce Butik Perhiasan dan Aksesoris Mewah Berbasis PHP Native MVC Terintegrasi Asisten Virtual Multi-Provider AI dan Gateway WhatsApp"**
* **Domain / Kategori:** Electronic Commerce (E-Commerce), Retail Fashion & Accessories, Artificial Intelligence (AI) Customer Service.

---

## 3. Deskripsi Aplikasi

### 3.1. Tentang NNACOSA
**NNACOSA** (`nnco_sa`) adalah platform *web e-commerce* khusus butik perhiasan dan aksesoris eksklusif (*handmade*) seperti kalung titanium, cincin berlian/safir, gelang mutiara, dan anting-anting berkelas. Aplikasi dibangun menggunakan arsitektur **PHP Native Custom Model-View-Controller (MVC)** yang dirancang dari nol (*lightweight*, tanpa framework berat seperti Laravel) guna menjamin kecepatan akses, efisiensi sumber daya server, dan kendali keamanan penuh.

Secara visual, NNACOSA menerapkan identitas desain editorial butik mewah terinspirasi palet *Adelina Soft Luxury* (Warm Ivory `#FFFDF9`, Cream `#F8F2EB`, Deep Brown `#4B3B32`, dan Champagne Gold `#D8BFA3`), memberikan pengalaman belanja yang tenang, elegan, dan premium bagi customer.

### 3.2. Tujuan Pembuatan
1. Menyediakan platform toko perhiasan yang memberikan impresi visual mewah (*high trust & prestige*) guna meningkatkan konversi transaksi aksesoris bernilai tinggi.
2. Mengintegrasikan teknologi kecerdasan buatan (*Generative AI*) sebagai konsultan gaya (*jewelry stylist*) dan asisten layanan pelanggan interaktif 24 jam sehari.
3. Menghadirkan saluran komunikasi langsung ke perangkat pengguna melalui WhatsApp OTP dan notifikasi pesanan otomatis yang cepat, andal, dan minim risiko masuk folder spam.
4. Mencegah manipulasi ulasan dengan menerapkan ulasan terverifikasi (*verified customer reviews*) yang terikat langsung dengan nomor faktur pembelian asli.
5. Memenuhi kriteria capaian pembelajaran mata kuliah **Rekayasa Perangkat Lunak (RPL)** dalam merancang perangkat lunak yang terstruktur, aman, teruji, dan terdokumentasi dengan baik.

### 3.3. Permasalahan yang Diselesaikan
* **UI E-Commerce Tradisional yang Generik:** Banyak situs web perhiasan lokal menggunakan template toko ritel biasa dengan warna mencolok dan kontras tajam yang mengurangi estetika keanggunan produk mewah.
* **Keraguan Pembeli dalam Memilih Produk:** Pembeli online sering bingung memilih perhiasan yang cocok untuk acara khusus (hadiah ulang tahun, lamaran, wisuda). Fitur AI bertindak sebagai konsultan perhiasan yang memberikan rekomendasi spesifik sesuai anggaran dan kebutuhan.
* **Keterlambatan Konfirmasi via Email:** Notifikasi berbasis email konvensional sering terlambat dibuka atau masuk folder spam. NNACOSA mengalihkan autentikasi OTP pendaftaran dan notifikasi pengiriman pesanan langsung ke WhatsApp pelanggan.
* **Maraknya Ulasan Palsu (*Fake Reviews*):** Di NNACOSA, tombol ulasan hanya aktif jika produk tersebut telah dipesan dan diverifikasi dalam riwayat pembelian pelanggan.

---

## 4. Fitur Utama Aplikasi

### A. Sisi Pelanggan (Customer Facing)
1. **Beranda & Editorial Showroom:**
   * Hero banner elegan dengan tipografi editorial (*Cormorant Garamond*).
   * Showcase kategori perhiasan (Kalung, Cincin, Gelang, Anting).
   * Produk terpopuler (*Best Seller*), koleksi terbaru, dan testimoni ulasan berbintang 4–5.
2. **Katalog Produk & Filter Pintar:**
   * Filter dinamis berdasarkan kategori produk.
   * Pencarian langsung (*real-time keyword search*).
   * Pengurutan (*sorting*) berdasarkan nama dan harga terendah/tertinggi.
   * Pagination bersih tanpa reload halaman penuh yang mengganggu.
3. **Detail Produk & Galeri:**
   * Tampilan multi-gambar dengan galeri interaktif.
   * Deskripsi detail spesifikasi material, dimensi, dan stok *real-time*.
   * Ringkasan rating bintang dan distribusi ulasan (bintang 1 hingga 5).
4. **Keranjang Belanja (*Shopping Cart*) & Wishlist:**
   * Tambah, perbarui jumlah pesanan, dan hapus item secara instan.
   * Simpan produk impian ke daftar wishlist pelanggan.
5. **Checkout & Pembayaran Transparan:**
   * Formulir alamat pengiriman terstruktur.
   * Integrasi kupon diskon promo (potongan persentase maupun nominal langsung).
   * Pemilihan metode transfer bank dengan rincian instruksi pembayaran.
6. **Riwayat Pesanan & Lacak Pengiriman:**
   * Monitoring status pesanan secara berkala: `Pending` ➔ `Paid` ➔ `Processing` ➔ `Shipped` ➔ `Completed` ➔ `Cancelled`.
   * Unduh dan cetak Invoice resmi pesanan dalam format PDF / Print View.
7. **Ulasan & Rating Pelanggan Terverifikasi (*Verified Reviews*):**
   * Customer yang sudah menyelesaikan transaksi dapat memberikan rating bintang (1–5) dan komentar ulasan.
   * Customer dapat mengedit atau menghapus ulasannya sendiri kapan saja.
8. **Autentikasi Eksklusif WhatsApp OTP:**
   * Pendaftaran akun diamankan dengan verifikasi kode OTP 6 digit yang dikirim langsung ke WhatsApp pelanggan via Fonnte Gateway.
   * Perlindungan terhadap serangan *brute force* dengan kebijakan *cooldown timer* dan batas percobaan verifikasi.
9. **Dark Mode & Light Mode:**
   * Pengalihan tema tampilan gelap/terang yang tersimpan secara persisten pada preferensi browser *localStorage*.

### B. Sisi Pengelola & Manajemen (Admin, Operator, Owner)
1. **Dashboard Statistik Real-Time:**
   * Ringkasan total pendapatan toko, total pesanan masuk, dan volume produk terjual.
   * Grafik tren transaksi dan pesanan yang memerlukan perhatian segera.
2. **Manajemen Produk & Kategori (CRUD):**
   * Tambah, ubah, dan hapus produk aksesoris lengkap dengan unggah foto beresolusi tinggi.
   * Manajemen kategori dan kontrol ketersediaan stok fisik produk.
3. **Manajemen Pesanan & Pembayaran:**
   * Verifikasi bukti transfer pembayaran dari customer.
   * Pembaruan status pemrosesan dan penginputan nomor resi pengiriman kurir.
   * Pembatalan pesanan dengan pengembalian stok otomatis.
4. **Notifikasi WhatsApp Otomatis (Order Dispatcher):**
   * Sistem otomatis mengirim pesan WhatsApp kepada customer saat status pesanan berubah menjadi `Processing` atau `Shipped`.
   * Dilengkapi riwayat log pengiriman (*delivery logs*) untuk memantau status keberhasilan pengiriman pesan Fonnte.
5. **Moderasi Ulasan Pelanggan:**
   * Admin dapat meninjau seluruh ulasan masuk, mencari berdasarkan nama pelanggan atau kata kunci, serta menyaring berdasarkan rating bintang.
6. **Manajemen Pengguna & Hak Akses (RBAC):**
   * Pengelolaan akun dengan 4 tingkatan hak akses: `Customer`, `Admin`, `Operator`, dan `Owner`.
7. **Pengaturan Sistem & Keamanan:**
   * Konfigurasi engine AI (*multi-provider switching*).
   * Konfigurasi WhatsApp API Gateway (Token Fonnte & URL Endpoint).
   * Pengaturan kebijakan keamanan OTP (panjang digit, masa kedaluwarsa, jeda waktu cooldown).
   * Enkripsi kredensial sensitif menggunakan algoritma terotentikasi **AES-256-GCM** (*Secret Vault*).

---

## 5. Integrasi & Fitur AI

### 5.1. NNACO Assistant (AI Virtual Shopping Assistant)
NNACOSA mengintegrasikan modul kecerdasan buatan interaktif bernama **NNACO Assistant** yang berada di sudut kanan bawah setiap halaman website.

```
       +-------------------------------------------------------+
       |               Frontend Client (Browser)               |
       |  - Chatbot Widget Bubble & Interactive Modal Window   |
       +-------------------------------------------------------+
                                  │  POST /ai/chat_api (JSON)
                                  ▼
       +-------------------------------------------------------+
       |              AiController (Backend Core)              |
       |  1. Context Injection:                                |
       |     • Katalog produk aktif & harga real-time          |
       |     • Kupon diskon berlaku                            |
       |     • Identitas & role user yang sedang login         |
       |     • Riwayat pesanan & statistik transaksi toko      |
       |  2. session_write_close() (Non-blocking I/O)          |
       +-------------------------------------------------------+
                                  │
                                  ▼
       +-------------------------------------------------------+
       |            AiService & Secret Vault Manager           |
       |  - Dekripsi API Key secara dinamis (AES-256-GCM)      |
       |  - Routing permintaan ke Adapter Provider Aktif       |
       +-------------------------------------------------------+
            │                     │                     │
            ▼                     ▼                     ▼
     [ NVIDIA NIM ]        [ OpenAI API ]       [ Local / Groq ]
     (Nemotron/Llama)       (GPT-4o Mini)        (Ollama / etc)
```

### 5.2. Fungsi Utama AI dalam Aplikasi
1. **Rekomendasi Gaya & Hadiah Spesifik (*Personal Stylist*):**  
   Memberikan rekomendasi perhiasan yang paling tepat ketika pelanggan bertanya kebutuhan tertentu (contoh: *"Saya ingin mencari gelang untuk hadiah ulang tahun sahabat wanita dengan budget di bawah Rp 200.000"*).
2. **Pengecekan Status & Informasi Pesanan Pribadi:**  
   Ketika customer yang sedang login bertanya *"Bagaimana status pesanan saya?"*, AI langsung membaca data pesanan pelanggan tersebut dari database dan menjawab dengan nomor resi serta status terkininya.
3. **Asisten Operasional untuk Staf Toko (Admin / Operator / Owner):**  
   Ketika staf login dan bertanya *"Berapa pesanan masuk hari ini?"* atau *"Berapa total pemasukan toko?"*, AI secara cerdas mengenali peran (*role*) pengguna dan menyajikan statistik manajemen toko yang akurat.
4. **Proteksi & Pembatasan Relevansi:**  
   AI telah diprogram untuk menolak pertanyaan di luar topik perhiasan dan aksesoris butik NNACO SA (seperti coding, matematika umum, atau politik) secara sopan dan profesional.

### 5.3. Teknologi & Model AI yang Didukung
* **Arsitektur Provider Adapter:** Dibangun dengan pola *Design Pattern Adapter* (`AiProviderInterface`) sehingga sistem dapat berpindah engine AI hanya dengan satu klik di Admin Dashboard tanpa mengubah kode program.
* **Provider yang Kompatibel:**
  * **NVIDIA NIM:** Model `nvidia/nemotron-3.5-bolt-e4b` / `meta/llama-3-70b-instruct`.
  * **OpenAI API:** Model `gpt-4o-mini` / `gpt-3.5-turbo`.
  * **OpenAI-Compatible Providers:** Google Gemini (via OpenAI compatibility endpoint), Groq Cloud, OpenRouter, dan Ollama (Local AI).
* **Mekanisme Graceful Fallback:**  
  Jika server AI pihak ketiga mengalami gangguan jaringan atau kuota habis, sistem secara otomatis mengalihkan respon ke *Smart Rule-Based Engine* berbasis pencocokan kata kunci lokal sehingga customer tidak pernah melihat layar error kosong.
* **Non-Blocking Session Handling:**  
  Panggilan jaringan AI mengeksekusi `session_write_close()` sebelum cURL outbound berjalan, memastikan browsing halaman lain di tab browser tidak terhambat (*freeze*) saat AI sedang berpikir.

---

## 6. Tautan Video Demonstrasi

Tonton video demonstrasi dan penjelasan teknis lengkap aplikasi NNACOSA melalui tautan berikut:

* **Tautan YouTube:**  
  [▶ Tonton Video Demonstrasi Proyek NNACOSA di YouTube](https://youtu.be/dw6JPwbfezc)  
  
---

## 7. Panduan Instalasi & Menjalankan Sistem

Ikuti petunjuk langkah demi langkah di bawah ini untuk menginstal dan menjalankan proyek NNACOSA di lingkungan komputer lokal Anda:

### 7.1. Persyaratan Perangkat Lunak (Prerequisites)
Pastikan komputer Anda telah terinstal:
1. **Web Server & Database:** XAMPP (Apache + MariaDB/MySQL) versi 3.3.0 atau yang lebih baru.
2. **Bahasa Pemrograman:** PHP versi 8.1 atau 8.2 (dilengkapi ekstensi `pdo_mysql`, `curl`, `openssl`, `mbstring`).
3. **Version Control:** Git CLI (Git for Windows).
4. **Browser Modern:** Google Chrome, Mozilla Firefox, atau Microsoft Edge.

---

### 7.2. Langkah 1: Kloning Repositori
Buka terminal (PowerShell atau Git Bash), navigasikan ke direktori `htdocs` XAMPP Anda, lalu lakukan clone repositori:

```bash
cd C:\xampp\htdocs
git clone https://github.com/sitinurfadillah192/project-nnaco.sa_siti-nur-fadillah.git nnco_sa
cd nnco_sa
```

---

### 7.3. Langkah 2: Konfigurasi Database MySQL
1. Buka aplikasi **XAMPP Control Panel** dan klik **Start** pada modul **Apache** dan **MySQL**.
2. Buka browser dan akses phpMyAdmin di `http://localhost/phpmyadmin/`.
3. Buat database baru dengan nama:
   ```sql
   nnaco_sa
   ```
4. Klik tab **Import**, pilih file dump database yang telah disediakan di dalam folder proyek:
   ```
   database/nnaco_sa.sql
   ```
   *(Atau alternatif skema bersih: `database/schema_full.sql`)*, lalu klik **Import / Go**.

---

### 7.4. Langkah 3: Konfigurasi Environment Variable (`.env`)
Salin file `.env.example` menjadi file `.env` di root utama proyek:

* **Di PowerShell:**
  ```powershell
  Copy-Item .env.example .env
  ```
* **Di Command Prompt / Git Bash:**
  ```bash
  cp .env.example .env
  ```

Buka file `.env` menggunakan teks editor (misalnya VS Code atau Notepad), lalu pastikan konfigurasinya sesuai:
```ini
APP_ENV=development
APP_URL=http://localhost/nnco_sa/public

# Konfigurasi Database (Gunakan 127.0.0.1 untuk performa Windows yang optimal)
DB_HOST=127.0.0.1
DB_USER=root
DB_PASS=
DB_NAME=nnaco_sa

# Kunci Enkripsi Master untuk Secret Vault (AES-256-GCM)
SYSTEM_ENCRYPTION_KEY=85dd911c3598b089229232ee5c3cd34e72dd9bf530e0872b9462fab8f188bd37

# Konfigurasi Opsional Eksternal
AI_API_KEY=your_nvidia_or_gemini_api_key_here
FONNTE_TOKEN=your_fonnte_whatsapp_token_here
```

---

### 7.5. Langkah 4: Menjalankan dan Mengakses Aplikasi
1. Pastikan modul **Apache** dan **MySQL** pada XAMPP Control Panel dalam status berwarna hijau (**Running**).
2. Buka browser web favorit Anda, lalu kunjungi alamat:
   ```
   http://localhost/nnco_sa/public/
   ```
3. Selamat! Website butik perhiasan NNACOSA siap digunakan dan dijelajahi.

---

## 8. Akun Uji Coba (Demo Testing)

Untuk mempermudah pengujian seluruh fitur oleh Dosen Penguji atau Asisten Laboratorium, berikut adalah kredensial akun uji coba yang telah tersedia di database:

| Peran (Role) | Alamat Email | Kata Sandi (Password) | Hak Akses Utama |
|:---|:---|:---:|:---|
| **Administrator** | `admin@nnacosa.com` | `123456` | Kelola Katalog, Pesanan, Settings, Moderasi Review, Konfigurasi AI & OTP |
| **Operator Toko** | `operator@nnacosa.com` | `123456` | Kelola Produk, Cek Stok, Proses Resi & Notifikasi Pesanan |
| **Pemilik (Owner)** | `owner@nnacosa.com` | `123456` | Pantau Laporan Keuangan, Omset, Statistik Toko, Audit Log |
| **Pelanggan (Customer)** | `customer@gmail.com` | `123456` | Belanja, Checkout, Wishlist, Beri Ulasan Bintang, Riwayat Order |


---

## 9. Pengujian Otomatis (Automated Testing)

Proyek NNACOSA dilengkapi dengan rangkaian pengujian otomatis (*Automated Test Suites*) dan integrasi **CI/CD GitHub Actions Pipeline** (`.github/workflows/ci.yml`) untuk memverifikasi logika bisnis, sintaksis, dan keamanan sistem secara kontinu pada setiap commit dan pull request.

Jalankan perintah Master Test Runner berikut di terminal Anda:

```bash
# Menjalankan seluruh pengujian (Linter Sintaks, Unit Test Keamanan, dan Fitur Bisnis)
php tests/run_tests.php
```

Atau jalankan modul pengujian spesifik secara individual:

```bash
# 1. Pengujian Unit Core Security & Utilities (CSRF, XSS, SSRF, AES-256-GCM)
php tests/Unit/CoreSecurityTest.php

# 2. Pengujian Modul Ulasan & Rating Pelanggan (13 Unit Test)
php tests/test_customer_reviews.php

# 3. Pengujian Alur Registrasi & WhatsApp OTP (17 Unit Test)
php tests/test_register_whatsapp_otp.php

# 4. Pengujian Enkripsi Secret Vault AES-256-GCM & System Settings (38 Unit Test)
php tests/test_system_settings.php
```

**Status Pengujian Terkini:** `ALL UNIT TESTS PASSED (0 FAILED) — 100% SUCCESS`.

---

## 10. Struktur Direktori Proyek

```
nnco_sa/
├── app/
│   ├── config/              # Berkas konfigurasi utama (config.php, mail.php)
│   ├── controllers/         # Controller MVC (Home, Catalog, Auth, Order, Review, Ai, dsb)
│   ├── core/                # Core Framework (App, Controller, Database, Security, SecretVault, dsb)
│   ├── helpers/             # Helper fungsi utilitas (env.php)
│   ├── middleware/          # Middleware autentikasi & RBAC (AuthMiddleware, RoleMiddleware)
│   ├── models/              # Model data interaksi basis data (Product, User, Order, Review, dsb)
│   ├── repositories/        # Repository layer untuk logging dan query terpisah
│   ├── services/            # Service layer (AiService, FonnteService, OtpService, dsb)
│   └── views/               # Antarmuka tampilan (Templates, Catalog, Home, Auth, Admin, dsb)
├── database/
│   ├── migrations/          # Berkas migrasi skema tabel bertahap
│   ├── nnaco_sa.sql         # Berkas dump skema dan data database lengkap
│   └── schema_full.sql      # Skema DDL lengkap pembuatan database
├── docs/                    # Dokumentasi lengkap sistem (PRD_v2.0.md)
├── public/                  # Document root web server publik
│   ├── css/                 # Stylesheet CSS kustom (Adelina Soft Luxury Tokens)
│   ├── img/                 # Aset gambar perhiasan, aksesoris, dan dekorasi
│   ├── js/                  # Skrip JavaScript interaktif sisi klien
│   ├── uploads/             # Direktori penyimpanan unggahan foto produk
│   └── index.php            # Titik masuk utama aplikasi (Single Entry Point)
├── tests/                   # Kumpulan berkas skrip pengujian otomatis (Automated Unit Tests)
├── .env.example             # Contoh template environment variable tanpa rahasia
├── .gitignore               # Konfigurasi pengecualian berkas Git (.env, cache, vendor)
└── README.md                # Dokumentasi utama proyek untuk pengumpulan tugas UAS RPL
```

---

<div align="center">
  <sub>Proyek ini dikembangkan dengan dedikasi penuh untuk memenuhi kriteria evaluasi tugas akhir <b>Rekayasa Perangkat Lunak (RPL)</b>.</sub><br>
  <sub><b>NNACOSA &copy; 2026. Hak Cipta Dilindungi.</b></sub>
</div>
