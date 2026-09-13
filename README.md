# Build n Fix

Aplikasi web penjualan bahan bangunan terpercaya yang juga mendukung pengelolaan *inventory* secara terpusat dan multi-cabang. Dikembangkan oleh tim **PT Structon**.

---

## 📌 Deskripsi

**Build n Fix** adalah platform web modern untuk penjualan dan distribusi material bahan bangunan. Aplikasi ini dirancang untuk memudahkan customer, staf operasional toko, dan pemilik bisnis dalam satu ekosistem yang terintegrasi:

- **Customer** dapat mencari, memilih, dan membeli berbagai material bangunan secara mudah dengan opsi pengambilan langsung (*Pickup*) maupun pengiriman ke lokasi (*Delivery*).
- **Sistem Multi-Cabang** menyediakan visibilitas informasi produk, harga yang konsisten, dan ketersediaan stok aktual berdasarkan lokasi cabang.
- **Pengelolaan Inventory** mendukung pemantauan mutasi stok, kartu stok per cabang, dan manajemen pemasok (*supplier*).
- **Admin Cabang** bertugas mengelola operasional harian cabang masing-masing, meliputi pembaruan stok, katalog produk, verifikasi pembayaran, dan pemrosesan pesanan.
- **Owner** memiliki akses menyeluruh untuk memantau performa penjualan, pergerakan stok, dan laporan analitik di seluruh cabang.

---

## ✨ Fitur Utama

Fitur pada Build n Fix dikelompokkan berdasarkan hak akses pengguna (*Role-Based Access Control*):

### 🛒 Customer
- **Katalog Produk**: Menjelajahi ragam material bangunan lengkap dengan filter kategori, merek, dan ketersediaan.
- **Kategori Produk**: Navigasi produk terstruktur berdasarkan klasifikasi material konstruksi.
- **Detail Produk**: Informasi spesifikasi produk, harga satuan, dan ketersediaan stok di masing-masing cabang.
- **Informasi Harga & Stok per Cabang**: Tampilan stok yang transparan untuk setiap cabang toko.
- **Pencarian Produk**: Pencarian cepat berbasis nama produk, kata kunci, maupun SKU.
- **Shopping Cart**: Pengelolaan keranjang belanja terpusat dengan validasi stok per cabang.
- **Checkout Multi-Metode**: Pilihan metode pemenuhan pesanan (*Pickup* di toko atau *Delivery* ke alamat pengiriman).
- **Mock Payment**: Simulasi alur pembayaran instan multi-kanal untuk pengujian transaksi.
- **Order Center**: Riwayat seluruh transaksi pesanan yang pernah dilakukan.
- **Tracking Pesanan**: Pemantauan tahapan status pesanan secara *real-time* via *timeline* interaktif.
- **Profile Settings**: Pengaturan informasi akun dan perubahan kata sandi.
- **Autentikasi & Logout**: Sistem login, pendaftaran akun baru, dan sesi aman.

### 👨‍💼 Admin
- **Admin Dashboard**: Ringkasan metrik operasional cabang (total produk aktif, pesanan baru, mitra pemasok, dan peringatan stok menipis).
- **Manajemen Produk**: Penambahan, pembaruan, dan pengelolaan katalog produk cabang.
- **Manajemen Kategori**: Pengorganisasian kategori dan penyesuaian penempatan produk.
- **Manajemen Stok**: Penyesuaian jumlah stok (*Stock Adjustment*) langsung pada kartu stok per produk.
- **Manajemen Supplier**: Pendataan mitra pemasok dan pemetaan produk yang didistribusikan.
- **Purchase Order**: Pengelolaan data pemesanan dan pengadaan barang ke pemasok.
- **Manajemen Order**: Pembaruan status pemrosesan pesanan customer (siap diambil / dalam pengiriman).
- **Manajemen Payment**: Verifikasi dan pemantauan status bukti pembayaran transaksi.
- **Activity Log & Riwayat Mutasi**: Rekam jejak perubahan stok dan pergerakan material.

> *Admin beroperasi sesuai dengan wilayah cabang yang ditugaskan (Serdam, Gajahmada, atau Kota Baru).*

### 👑 Owner
- **Owner Executive Dashboard**: Monitoring komprehensif seluruh cabang dalam satu panel konsolidasi.
- **Sales Overview**: Gambaran total pendapatan, rata-rata nilai transaksi (*Average Order Value*), dan perbandingan performa antar cabang.
- **Stock Overview**: Monitoring total aset dan kuantitas stok material di seluruh jaringan toko.
- **Best Selling Products**: Peringkat produk material dengan volume penjualan tertinggi.
- **Low Stock Alert**: Peringatan dini otomatis untuk produk yang stoknya berada di bawah batas minimum di cabang manapun.
- **Supplier Purchases**: Pemantauan volume dan riwayat pengadaan material dari para supplier.
- **Inventory Value**: Estimasi valuasi total stok barang yang tersimpan di gudang seluruh cabang.
- **Laporan Komprehensif**:
  - *Sales Report* (Laporan Penjualan berkala harian, bulanan, dan tahunan).
  - *Stock Report* (Laporan Posisi Stok per Cabang).
  - *Purchase Report* (Laporan Pembelian & Pengadaan Barang).
  - *Inventory Report* (Laporan Valuasi & Mutasi Gudang).
- **Activity Log**: Pengawasan log aktivitas operasional dan transaksi seluruh cabang.

---

## 📍 Branch

Build n Fix mengelola tiga cabang utama di area Pontianak dan Kubu Raya:

1. **Cabang Serdam** – Komp. Ruko Pesona Serdam, Jl. Sungai Raya Dalam.
2. **Cabang Gajahmada** – Komp. Ruko Gajah Mada Square, Jl. Gajah Mada.
3. **Cabang Kota Baru** – Komp. Ruko Kota Baru Indah, Jl. Prof. M. Yamin.

Kuantitas stok barang dikelola secara independen dan dapat berbeda di setiap cabang sesuai dengan ketersediaan fisik gudang, sementara harga produk ditetapkan secara konsisten antar cabang sesuai dengan standar penetapan harga Build n Fix.

---

## 🛍️ Order Flow

Alur pemesanan customer dari pemilihan produk hingga pesanan selesai:

```text
Product
  ↓
Cart
  ↓
Checkout
  ↓
Payment
  ↓
Order
  ↓
Tracking
  ↓
Completed
```

Customer dapat memilih salah satu metode pemenuhan pesanan (*fulfillment method*):

### 🏪 Alur Status: Pickup (Ambil di Toko)
```text
Paid
  ↓
Ready to Pick Up
  ↓
Order Completed
```

### 🚚 Alur Status: Delivery (Pengiriman ke Alamat)
```text
Paid
  ↓
Proses
  ↓
On Delivery
  ↓
Order Completed
```

---

## 💳 Payment

Sistem pembayaran pada versi saat ini menggunakan mekanisme **simulasi / mock payment** untuk keperluan pengujian alur transaksi menyeluruh tanpa memproses transaksi finansial nyata.

Metode simulasi yang tersedia:
- **Virtual Account Simulation**: Simulasi transfer bank (BCA, Mandiri, BRI, BNI).
- **E-Wallet Simulation**: Simulasi pembayaran dompet digital dan QRIS (GoPay, OVO, DANA, ShopeePay).
- **Credit / Debit Simulation**: Simulasi pembayaran kartu kredit dan kartu debit (Visa, Mastercard, JCB).

---

## 🧱 Product Catalog

Katalog material Build n Fix mencakup kategori produk resmi berikut:

- Semen & Mortar
- Cat & Finishing
- Besi & Baja
- Pipa & Plumbing
- Kayu
- Atap
- Peralatan
- Lantai & Keramik
- Elektrikal
- Bahan Lainnya
- Pondasi & Beton
- Kaca & Aluminium
- Perekat & Sealant
- Tangki Air & Pompa
- Genteng & Insulation
- Alat Keselamatan

---

## 🛠️ Technology Stack

Teknologi dan *tools* yang digunakan dalam pengembangan project:

### Frontend
- **Blade Templating Engine**: Templating dinamis terstruktur bawaan Laravel.
- **Tailwind CSS v4**: Utility-first CSS framework untuk antarmuka responsif dan modern.
- **JavaScript**: Interaktivitas UI ringan dengan dukungan Alpine.js dan Chart.js untuk visualisasi data analitik.
- **HTML5**: Struktur semantik antarmuka web.

### Backend
- **PHP**: PHP 8.3+
- **Laravel Framework**: Laravel 13 (Arsitektur MVC & Service Layer)

### Development Tools & Environment
- **Vite**: Modern frontend build tool & asset bundler.
- **Laragon**: Lingkungan pengembangan web lokal Windows (PHP & Web Server).
- **Git & GitHub**: Version Control System dan kolaborasi repositori kode.
- **Figma**: Desain wireframe, mockup UI/UX, dan perancangan antarmuka pengguna.

---

## 🏗️ Architecture

Aplikasi menerapkan pemisahan logika bisnis dari tampilan (*separation of concerns*) dengan pola arsitektur berlapis:

```text
Route
  ↓
Middleware
  ↓
Controller
  ↓
Service
  ↓
Data / Repository
  ↓
View
  ↓
Blade Components
```

- **Route**: Menerima request HTTP dan mengarahkannya ke controller terkait.
- **Middleware**: Memvalidasi hak akses role pengguna (*Customer*, *Admin*, *Owner*) serta konteks sesi cabang.
- **Controller**: Menangani request, validasi input dasar, dan mendelegasikan proses ke Service.
- **Service**: Menampung logika bisnis (*Business Logic*) utama, seperti validasi stok, kalkulasi biaya kirim, dan kalkulasi checkout.
- **Data / Repository**: Menyediakan abstraksi data terpusat (*centralized mock/static data*) untuk entitas produk, pesanan, cabang, dan pengguna.
- **View & Blade Components**: Menampilkan antarmuka pengguna secara modular, bersih, dan konsisten.

---

## 🗄️ Database Status

Pada tahap pengembangan saat ini, sistem **belum terhubung ke database relasional (RDBMS) aktif seperti MySQL**. 

Penyimpanan dan manipulasi data saat ini mengandalkan:
- **Centralized Mock Data**: Data statis terstruktur untuk produk, cabang, supplier, dan laporan.
- **Laravel Session Storage**: Penyimpanan dinamis selama sesi aplikasi aktif untuk keranjang belanja (*cart*), status *checkout*, modifikasi stok sementara, penambahan pesanan baru, dan otentikasi peran pengguna.

Arsitektur *Repository* dan *Service* yang diterapkan telah dirancang secara modular agar siap dimigrasikan ke database relasional (seperti MySQL/PostgreSQL via Laravel Eloquent ORM) pada fase pengembangan selanjutnya tanpa perlu merombak logika antarmuka dan bisnis.

---

## 🎨 Design

Desain visual Build n Fix mengusung konsep:
- **Modern & Industrial**: Merefleksikan karakter kuat industri konstruksi dan material bangunan dengan sentuhan modern.
- **Minimalist & Clean**: Tata letak yang rapi, informatif, dan tidak membingungkan pengguna.
- **Spacious & Professional**: Penggunaan *white space* yang cukup, kontras warna yang tegas, dan tipografi jelas untuk kemudahan navigasi katalog dan dasbor analitik.

### Color Palette

| Kode Warna | Nama / Karakter | Penggunaan Utama |
|---|---|---|
| `#FFFFFF` | Pure White | Latar belakang kartu (*card*), modal, dan kontainer utama |
| `#F8F8F6` | Off-White / Soft Gray | Latar belakang halaman (*page background*) |
| `#111111` | Jet Dark | Teks utama, judul (*headings*), dan aksen gelap |
| `#171717` | Industrial Charcoal | Header, sidebar navigasi, dan elemen struktural gelap |
| `#F97316` | Construction Safety Orange | Warna aksen primer (*primary brand color*), tombol CTA, status aktif, dan sorotan harga |
| `#2563EB` | Professional Blue | Aksen sekunder, status pengiriman (*delivery*), dan indikator informatif |

---

## 📁 Project Structure

Ringkasan struktur direktori utama pada Build n Fix:

```text
Build-n-Fix/
├── app/
│   ├── Helpers/                 # Utility & formatting helper functions
│   ├── Http/
│   │   ├── Controllers/         # Multi-role HTTP controllers (Product, Order, Stock, dll.)
│   │   └── Middleware/          # Role & branch verification middleware
│   ├── Models/                  # Eloquent models
│   ├── Providers/               # Application service providers
│   ├── Repositories/            # Centralized static & mock data store
│   └── Services/                # Core business logic layer (Cart, Order, Checkout, dll.)
├── config/                      # File konfigurasi aplikasi Laravel
├── database/                    # Skema migrasi, seeder, dan SQLite lokal
├── public/                      # Asset publik dan gambar statis
├── resources/
│   ├── css/                     # Konfigurasi Tailwind CSS v4
│   ├── js/                      # Script JavaScript aplikasi
│   └── views/
│       ├── layouts/             # Master layout (app, admin, owner, auth, customer)
│       ├── components/          # Komponen Blade reusable (header, sidebar, cards, timeline)
│       ├── auth/                # Halaman login, register, dan signup
│       ├── home/                # Landing page customer
│       ├── products/            # Katalog dan formulir produk
│       ├── categories/          # Halaman daftar dan manajemen kategori
│       ├── cart/                # Keranjang belanja
│       ├── checkout/            # Alur checkout dan simulasi pembayaran
│       ├── orders/              # Manajemen pesanan & order tracking
│       ├── payments/            # Monitoring & verifikasi pembayaran
│       ├── stock/               # Kartu stok dan penyesuaian stok cabang
│       ├── stock-movements/     # Log mutasi pergerakan stok
│       ├── suppliers/           # Manajemen data mitra pemasok
│       ├── dashboard/           # Multi-role dashboard (Admin & Owner)
│       ├── reports/             # Laporan penjualan & performa cabang
│       └── profile/             # Halaman profil dan keamanan akun
└── routes/
    ├── console.php              # Definisi command artisan
    └── web.php                  # Rute aplikasi web
```

---

## 🚧 Project Status

**Status: In Development**

Project ini masih dalam tahap pengembangan aktif. Beberapa fitur operasional, autentikasi sesi, dan pemrosesan pesanan saat ini dijalankan menggunakan data simulasi (*mock/static data*) serta sesi aplikasi untuk mendemonstrasikan keseluruhan alur kerja secara fungsional.

---

## 👥 Team

Dikembangkan dengan bangga oleh tim **PT Structon**:

| Name | Role |
|---|---|
| Aricks Wijaya | UI/UX |
| David Wijaya | UI/UX |
| Edward Cornelius | Front-End & Back-End |
| Vincent William | Front-End & Back-End |
