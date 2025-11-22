# **Product Requirements Document (PRD)**

**Nama Proyek:** Website Indo Cafe | Rasa Nusantara, Gaya Masa Kini

**Versi:** 1.0

**Status:** Draft Final

**Tanggal:** 22 November 2025

## **1. Pendahuluan (Introduction)**

### **1.1 Latar Belakang**

Indo Cafe adalah kafe yang mengusung konsep "Rasa Nusantara" dengan penyajian modern. Saat ini, operasional pemesanan masih dilakukan secara manual atau semi-digital yang menyebabkan inefisiensi, antrean, dan keterbatasan jangkauan pemasaran. Diperlukan sebuah platform digital yang tidak hanya berfungsi sebagai katalog menu, tetapi juga sebagai mesin transaksi dan *branding*.

### **1.2 Tujuan (Objectives)**

1. **Digitalisasi Bisnis:** Mengubah proses manual menjadi digital (pemesanan, manajemen menu).  
2. **Branding & Pemasaran:** Memperkuat citra "Gaya Masa Kini" melalui desain UI/UX modern dan fitur promosi.  
3. **Efisiensi Operasional:** Mengurangi kesalahan pesanan dan mempercepat waktu layanan melalui notifikasi *real-time*.  
4. **Data-Driven Decision:** Menyediakan laporan penjualan akurat untuk strategi bisnis.

### **1.3 Lingkup Proyek (Scope)**

* **In-Scope:** Website Responsif (Mobile/Desktop), Manajemen Menu & Konten, Sistem Pemesanan *Online* (Delivery/Takeaway), Integrasi Pembayaran Digital, Dashboard Admin & Staf, Laporan Penjualan.  
* **Out-of-Scope:** Aplikasi Mobile Native (Android/iOS App), Integrasi logistik otomatis (GoSend/GrabExpress API - tahap 1 manual driver internal/pihak ketiga).

## **2. Profil Pengguna (User Personas)**

| Peran | Deskripsi & Kebutuhan Utama |
| :---- | :---- |
| **Pelanggan** (*Customer*) | Pengguna yang ingin memesan kopi/makanan dengan cepat tanpa antre. Membutuhkan UI yang intuitif, proses *checkout* cepat, dan info status pesanan. |
| **Pengunjung Umum** (*Guest*) | Calon pelanggan yang mencari info lokasi, melihat menu, atau cek suasana kafe (Galeri). Membutuhkan *loading page* cepat. |
| **Staf Operasional** (*Barista/Admin*) | Petugas di lapangan yang memproses pesanan. Membutuhkan notifikasi *real-time* yang jelas dan tombol aksi cepat (Terima/Selesai). |
| **Manajemen** (*Owner*) | Pemilik bisnis. Membutuhkan ringkasan data penjualan dan kendali atas strategi promosi. |
| **Admin Sistem** | Pengelola teknis. Membutuhkan akses penuh untuk konfigurasi sistem, *backup*, dan manajemen *user*. |

## **3. Fitur & Kebutuhan Fungsional (Functional Requirements)**

### **3.1 Modul Publik & Pelanggan (Front-End)**

* **Katalog Menu Interaktif:**  
  * Menampilkan menu berdasarkan kategori (Kopi Nusantara, Kudapan, dll).  
  * Pencarian menu (*Search*) dan Filter.  
  * Label khusus untuk "Menu Musiman" atau "Best Seller".  
  * Detail produk dengan foto HD dan deskripsi rasa (*storytelling*).  
* **Manajemen Akun Pelanggan:**  
  * Registrasi/Login (Email & Password).  
  * Manajemen Profil & **Alamat Favorit** (Rumah, Kantor).  
  * Riwayat Pesanan.  
* **Sistem Transaksi (Shopping Cart & Checkout):**  
  * Keranjang belanja (tambah/kurang item, catatan khusus per item).  
  * Pilihan Layanan: **Takeaway** (Ambil Sendiri) atau **Delivery** (Pesan Antar).  
  * Penerapan **Kode Promosi/Voucher**.  
  * **Pembayaran Digital:** Integrasi Payment Gateway (QRIS, VA, E-Wallet).  
* **Social Proof:**  
  * Fitur **Rating & Review** untuk produk yang sudah dibeli.

### **3.2 Modul Admin & Operasional (Back-Office Dashboard)**

* **Manajemen Pesanan (Order Management):**  
  * Daftar pesanan masuk dengan status *real-time* (Baru, Diproses, Siap, Selesai, Batal).  
  * Tombol aksi cepat untuk mengubah status pesanan.  
  * Notifikasi suara/visual saat pesanan baru masuk.  
* **Manajemen Menu (CMS):**  
  * CRUD (Create, Read, Update, Delete) Menu & Kategori.  
  * Pengaturan Harga dan Ketersediaan Stok (Ada/Habis).  
  * Upload Foto Produk.  
* **Manajemen Konten & Promosi:**  
  * Pengaturan Banner Halaman Utama.  
  * Manajemen Kode Voucher (Diskon %, Nominal, Masa Berlaku).  
  * Update Halaman "Tentang Kami" dan Galeri.  
* **Laporan & Analitik:**  
  * Dashboard ringkasan harian (Omzet, Jumlah Transaksi).  
  * Laporan Penjualan (Harian/Bulanan) ekspor ke PDF/Excel.  
  * Statistik Menu Terlaris.

## **4. Kebutuhan Non-Fungsional (Non-Functional Requirements)**

### **4.1 Performa (Performance)**

* Waktu muat halaman utama (*Load Time*) < 2 detik.  
* Proses *checkout* hingga konfirmasi pembayaran < 5 detik.  
* Mampu menangani lonjakan trafik hingga 2x lipat saat jam makan siang/malam.

### **4.2 Keamanan (Security)**

* Wajib menggunakan protokol **HTTPS/SSL** untuk seluruh halaman.  
* Enkripsi *password* pengguna di database (*Hashing*).  
* Pemisahan hak akses yang ketat (*Role-Based Access Control*) antara Staf dan Manajemen.  
* Data pembayaran tidak disimpan di database lokal (menggunakan tokenisasi Payment Gateway).

### **4.3 Ketersediaan & Keandalan (Availability)**

* Uptime target 99.9%.  
* **Backup Otomatis:** Database dicadangkan setiap hari pukul 03:00 pagi.

### **4.4 Usability (UI/UX)**

* Desain **Mobile-First**: Diutamakan tampilan di smartphone karena target pasar milenial/Gen-Z.  
* Tema Visual: Bersih, Minimalis, dengan aksen warna Nusantara (Terakota/Hijau/Emas).

## **5. Desain Antarmuka & Alur (UX Flow)**

### **5.1 Sitemap Global**

1. **Home:** Banner Promo, Menu Favorit, Lokasi Singkat.  
2. **Menu:** Full Katalog, Search, Filter Kategori.  
3. **Detail Produk:** Foto Besar, Deskripsi, Review, Add to Cart.  
4. **Cart/Checkout:** Review Pesanan, Input Alamat, Pilih Pembayaran.  
5. **Akun Saya:** Profil, Riwayat, Alamat Tersimpan.  
6. **Tentang Kami:** Cerita Kafe, Visi Misi.  
7. **Kontak:** Peta Lokasi, Jam Buka, WhatsApp Admin.

### **5.2 Alur Pesanan (Happy Path)**

1. Pelanggan Login -> Pilih Menu -> Masuk Keranjang.  
2. Checkout -> Pilih "Delivery" -> Pilih Alamat -> Pilih "QRIS".  
3. Bayar -> Sistem Verifikasi -> Status "Pesanan Baru".  
4. Staf Terima Notif -> Ubah Status "Diproses" -> Dapur Siapkan.  
5. Pesanan Siap -> Ubah Status "Sedang Diantar/Siap Ambil" -> Pelanggan Terima Notif.

## **6. Spesifikasi Teknis (Technical Specs)**

* **Platform:** Web-based Application (PWA Ready disarankan).  
* **Frontend:** React.js / Vue.js / HTML5 + Tailwind CSS.  
* **Backend:** Node.js / Laravel / Go.  
* **Database:** PostgreSQL atau MySQL (Relational DB).  
* **Infrastructure:** Cloud Hosting (AWS/GCP/DigitalOcean) atau VPS Lokal.  
* **Third-Party Integration:**  
  * Payment Gateway (Midtrans/Xendit).  
  * Maps API (Google Maps) untuk lokasi.  
  * WhatsApp API (Opsional untuk notifikasi).

## **7. Metrik Keberhasilan (Success Metrics)**

1. **Adopsi:** 50% pelanggan reguler beralih memesan via website dalam 3 bulan pertama.  
2. **Efisiensi:** Waktu pemrosesan pesanan berkurang rata-rata 30% dibanding manual.  
3. **Penjualan:** Kenaikan rata-rata nilai transaksi (*Average Order Value*) sebesar 10% berkat kemudahan tambah menu (*add-on*) di sistem.  
4. **Stabilitas:** Zero critical bugs saat peluncuran.

**Disetujui Oleh:**

| Nama | Jabatan | Tanggal | Tanda Tangan |
| :---- | :---- | :---- | :---- |
| \[Nama Pemilik\] | Owner Indo Cafe |  |  |
| \[Nama PM\] | Project Manager |  |  |
