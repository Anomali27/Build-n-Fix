# Build n Fix

Build n Fix adalah aplikasi web penjualan bahan bangunan yang juga mendukung pengelolaan inventory. Dikembangkan oleh tim PT Structon.

## ?? Deskripsi

- Platform web untuk penjualan bahan bangunan.
- Customer dapat mencari dan membeli material bangunan.
- Sistem menyediakan informasi produk, harga, dan stok berdasarkan cabang.
- Admin mengelola operasional cabang masing-masing.
- Owner dapat memantau seluruh cabang.

## ? Fitur Utama

### ?? Customer
- Melihat katalog produk
- Melihat kategori
- Melihat detail produk
- Melihat harga dan stok berdasarkan cabang
- Pencarian produk
- Shopping cart
- Checkout
- Pilihan Pickup atau Delivery
- Simulasi pembayaran (mock payment)
- Order Center (riwayat order)
- Melihat status/tracking pesanan
- Pengaturan profil
- Logout

### ???? Admin
- Admin Dashboard
- Manajemen produk
- Manajemen kategori
- Manajemen stok
- Manajemen supplier
- Purchase Order
- Manajemen order
- Manajemen payment
- Activity Log
- Operasi berbasis cabang (Serdam, Gajahmada, Kota Baru)

### ?? Owner
- Owner Dashboard
- Monitoring seluruh cabang
- Overview penjualan
- Overview stok
- Produk terlaris
- Stok rendah
- Pembelian supplier
- Nilai inventory
- Laporan penjualan, stok, purchase, inventory
- Activity Log

## ?? Branch

Build n Fix memiliki tiga cabang:
- Serdam
- Gajahmada
- Kota Baru

Stok dapat berbeda pada setiap cabang, sementara harga produk bersifat konsisten antar cabang sesuai rancangan aplikasi.

## ??? Order Flow

`
Product ? Cart ? Checkout ? Payment ? Order ? Tracking ? Completed
`

Customer dapat memilih metode:
- **Pickup**: Paid ? Ready to Pick Up ? Order Completed
- **Delivery**: Paid ? Proses ? On Delivery ? Order Completed

## ?? Payment

Sistem pembayaran masih berupa simulasi/mock dan belum terintegrasi dengan gateway nyata. Metode simulasi yang tersedia:
- Virtual Account
- E-Wallet
- Credit/Debit Simulation

## ?? Product Catalog

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

## ??? Technology Stack

### Frontend
- HTML
- Blade templating
- Tailwind CSS
- JavaScript

### Backend
- PHP 8.5
- Laravel 13

### Development Tools
- Vite
- Git & GitHub
- Laragon
- Figma (design)

## ??? Architecture

`
Route ? Middleware ? Controller ? Service ? Data (mock/static) ? View ? Blade Components
`

Business logic dipisahkan ke service layer, sementara data masih berupa mock/static dan disimpan di session selama tahap pengembangan.

## ??? Database Status

Saat ini proyek belum terhubung ke database. Data dikelola melalui:
- Mock Data
- Static Data
- Laravel Session

Struktur dipersiapkan agar dapat di-migrasikan ke database pada fase berikutnya.

## ?? Design

Desain Build n Fix bersifat modern, minimalist, industrial, professional, clean, dan spacious.

**Palet warna**:
- #FFFFFF (putih)
- #F8F8F6 (off-white)
- #111111 (hitam gelap)
- #171717 (gelap)
- #F97316 (oranye)
- #2563EB (biru)

## ?? Project Structure

`	ext
app/
+-- Data/
+-- Services/
+-- Http/
    +-- Controllers/
    +-- Middleware/

resources/
+-- views/
    +-- layouts/
    +-- components/
    +-- auth/
    +-- home/
    +-- products/
    +-- categories/
    +-- cart/
    +-- checkout/
    +-- orders/
    +-- inventory/
    +-- suppliers/
    +-- purchase-orders/
    +-- admin/
    +-- owner/
` 

## ?? Project Status

- **In Development** – Beberapa fitur masih menggunakan mock/static data dan belum terhubung ke database atau payment gateway.

## ?? Team

| Name | Role |
|---|---|
| Aricks Wijaya | UI/UX |
| David Wijaya | UI/UX |
| Edward Cornelius | Front-End & Back-End |
| Vincent William | Front-End & Back-End |

---

*README ini memberikan gambaran lengkap tentang proyek Build n Fix sesuai dengan implementasi yang ada pada repository.*
