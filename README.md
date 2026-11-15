# WARKOP MK – Sistem Kasir (POS) & Manajemen Stok Warkop

**WARKOP MK (Kasir & Pemesanan)** adalah sistem informasi operasional kasir (*Point of Sale*) dan manajemen persediaan bahan baku berbasis web yang dirancang khusus untuk mendigitalkan alur pesanan meja, transaksi kasir, pencetakan nota/struk, pelacakan mutasi inventaris secara *real-time*, serta pengawasan margin penjualan operasional Warung Kopi Modern.

Aplikasi ini menggantikan pencatatan pesanan kertas dan pembukuan manual dengan sistem pencatatan otomatis transaksi multi-metode (Tunai, QRIS, Transfer, E-Wallet), validasi stok bahan baku otomatis saat pesanan dibuat, sistem penahanan tagihan (*Open Bill / Meja Belum Bayar*), peringatan batas minimum stok (*low stock threshold*), hingga audit log mutasi stok dan rekapitulasi laporan penjualan periodik.

---

## 🛠️ Tumpukan Teknologi
- **Backend & Arsitektur**: PHP 8.2+ Native dengan pola arsitektur MVC (*Model-View-Controller*) modular, PDO Database Abstraction, Prepared Statements anti-SQL Injection, serta proteksi CSRF Token & Session Authentication.
- **Basis Data**: MariaDB 10.4+ / MySQL 8.0 dengan skema relasional InnoDB, Foreign Keys constraint, dan index pencarian teroptimasi.
- **Frontend & Tampilan**: HTML5 Semantik, CSS3 Custom Design System (*Warkop Blue & Clean Modern Theme*), Vanilla JavaScript ES6+ untuk interaktivitas katalog kasir dinamis & kalkulasi kembalian otomatis.
- **Komponen & Framework UI**: Bootstrap 5.3 (Grid & modal dialogs), Plus Jakarta Sans typography, dan SVG Iconography responsif (*Mobile-First POS Ready*).
- **Audit & Keamanan**: Engine Audit Logging JSON untuk melacak histori modifikasi data produk/stok, RBAC (*Role-Based Access Control*), dan sanitasi input XSS.

---

## 👥 Peran Pengguna (Role & Akses)
Hak akses dan alur kerja aplikasi dibagi ke dalam 2 peran utama yang terisolasi:

### 1. Kasir / Barista (`role: user`)
- **Katalog Kasir Cepat**: Membuka katalog menu POS dengan filter kategori cepat (Minuman, Kopi, Minuman Rasa, Makanan, Makanan Ringan, Kacang-Kacangan, Rokok).
- **Pencatatan Pesanan**: Menambahkan menu ke keranjang (*order summary*), memilih tipe pesanan (*Dine In* / *Take Away*), penomoran meja, dan metode pembayaran.
- **Manajemen Open Bill**: Menahan tagihan meja yang belum menyelesaikan pembayaran (*Simpan Bill*) serta melunasi tagihan saat tamu selesai berkunjung (*Pay Bill*).
- **Kalkulasi Kembalian Otomatis**: Memilih nominal cepat (Uang pas, 10rb, 20rb, 50rb, 100rb) dengan kalkulasi kembalian otomatis yang akurat.
- **Penerbitan Struk**: Mencetak nota pembayaran termal pelanggan dengan nomor unik transaksi (`WRK-YYYYMMDD-XXX`).
- **Restock Dapur**: Melakukan input mutasi barang masuk saat bahan baku baru diterima dari pemasok.
- **Peringatan Porsi Menipis**: Menerima notifikasi langsung pada topbar apabila stok menu berada di bawah batas minimum.

### 2. Owner / Administrator (`role: admin`)
- **Command Center Dashboard**: Memantau ringkasan omzet harian, total omzet keseluruhan, jumlah order sukses, serta estimasi nilai rupiah stok inventaris warkop.
- **Manajemen Master Menu & Produk**: Tambah, perbarui, dan *soft-delete* 58+ master produk warkop lengkap dengan harga beli (*cost price*), harga jual (*sell price*), satuan unit, dan batas ambang minimum stok (*min stock*).
- **Manajemen Kategori & Pemasok**: Mengelola pengelompokan menu dan daftar mitra grosir/distributor (Grosir Sembako & Minuman, Distributor Kopi & Rokok).
- **Laporan Penjualan Komprehensif**: Menyaring laporan transaksi berdasarkan rentang tanggal, status pesanan, serta menghitung margin keuntungan kotor.
- **Riwayat Mutasi & Audit Log**: Meninjau rekaman pergerakan stok masuk (*IN*), keluar (*OUT*), dan penyesuaian (*ADJUSTMENT*) beserta rekam jejak pengguna dan alamat IP.
- **Administrasi Pengguna**: Mengelola data kredensial dan hak akses akun kasir warkop.

---

## 📋 Kebutuhan Sistem
- **PHP**: Versi 8.2 atau lebih tinggi (ekstensi aktif: `pdo_mysql`, `mbstring`, `json`, `session`).
- **Web Server**: Apache HTTP Server (melalui paket XAMPP pada macOS / Windows) atau PHP Built-in Server.
- **Basis Data**: MariaDB 10.4+ / MySQL 8.0+ (Port default 3306).
- **Peramban Web**: Google Chrome, Mozilla Firefox, Apple Safari, atau Microsoft Edge versi modern.

---

## 🚀 Panduan Instalasi Lokal

### 1. Salin Repositori
```bash
git clone https://github.com/depdan/warkop-stock-management.git
cd warkop-stock-management
```

### 2. Penempatan Direktori Web Server (XAMPP)
- **Windows**: Letakkan folder proyek pada:
  `C:/xampp/htdocs/Warkop Stock Management`
- **macOS**: Letakkan folder proyek pada:
  `/Applications/XAMPP/xamppfiles/htdocs/Warkop Stock Management`

### 3. Konfigurasi Basis Data
Buka berkas `config/database.php` dan sesuaikan parameter kredensial basis data Anda:
```php
return [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'warkop_stock',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
];
```

### 4. Penyiapan Basis Data
1. Buat basis data bernama `warkop_stock` pada MySQL/phpMyAdmin:
   ```sql
   CREATE DATABASE warkop_stock CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Impor struktur tabel relasional dan data awal:
   - Buka phpMyAdmin (`http://localhost/phpmyadmin`).
   - Pilih basis data **`warkop_stock`**.
   - Buka tab **Import**.
   - Pilih berkas **`database/schema.sql`**, kemudian klik **Kirim / Import**.

---

## 💻 Menjalankan Aplikasi

### Opsi A: Menggunakan XAMPP Apache (Direkomendasikan untuk Kasir / Produksi)
1. Aktifkan modul **Apache** dan **MySQL** melalui XAMPP Control Panel.
2. Buka peramban web dan akses URL:
   👉 `http://localhost/Warkop%20Stock%20Management/public/`
3. Untuk akses kasir nirkabel via tablet / smartphone melalui jaringan Wi-Fi lokal warkop, akses melalui IP statis komputer server:
   👉 `http://192.168.1.100/Warkop%20Stock%20Management/public/`

### Opsi B: Menggunakan PHP Built-in Server (Pengembangan Cepat)
Jalankan perintah berikut melalui terminal dari direktori root proyek:
```bash
php -S 127.0.0.1:8000 -t public
```
Akses aplikasi melalui peramban:
👉 `http://127.0.0.1:8000`

---

## 🔐 Kredensial Akun Awal (Demo / Pengujian)

| Peran Akun | Email / Username | Kata Sandi | Keterangan |
| :--- | :--- | :--- | :--- |
| **Owner / Administrator** | `admin@warkop.local` | `password` | Akses penuh dashboard kendali, master produk, laporan omzet & audit log |
| **Kasir / Barista** | `user@warkop.local` | `password` | Akses kasir POS, simpan/lunasi bill meja, cetak struk & restock dapur |

---

## 📂 Struktur Direktori Proyek
```text
├── app/
│   ├── controllers/      # Controller logika alur kerja (Auth, Dashboard, Order, Stock, User)
│   ├── helpers/          # Fungsi utilitas (auth.php, db.php, security.php, ui.php)
│   ├── models/           # Abstraksi basis data (OrderModel, StockModel, UserModel, ExpenseModel)
│   └── views/            # Antarmuka tampilan (auth, layout, dashboard, products, receipt, sales_report, dll.)
├── config/
│   ├── app.php           # Konfigurasi nama aplikasi, zona waktu, dan penentuan BASE_URL
│   └── database.php      # Parameter koneksi MariaDB/MySQL PDO
├── database/
│   ├── schema.sql        # Skema DDL tabel relasional, master produk, kategori, dan akun default
│   └── import_penjualan.sql # Skrip pendukung data transaksi penjualan
├── public/
│   ├── assets/           # Berkas stylesheet CSS modern dan logika JavaScript antarmuka
│   ├── .htaccess         # Konfigurasi perutean URL Apache
│   └── index.php         # Entry point pembuka utama aplikasi
├── .gitignore            # Konfigurasi isolasi berkas sementara dan sistem
└── README.md             # Dokumentasi teknis sistem
```

---

## 🔒 Tata Kelola & Keamanan Berkas
- **Enkripsi Kredensial**: Seluruh kata sandi akun pengguna diamankan menggunakan enkripsi hashing standar industri `password_hash()` (Bcrypt).
- **Proteksi CSRF**: Setiap form transaksi dan perubahan data diproteksi token anti-CSRF (`verifyCsrf()`) guna menangkal eksploitasi *Cross-Site Request Forgery*.
- **Pencegahan SQL Injection**: Seluruh transaksi basis data menggunakan PDO *Prepared Statements* berparameter.
- **Audit Jejak Digital**: Setiap penambahan, pembaruan, penghapusan produk, dan mutasi stok dicatat ke tabel `audit_logs` lengkap dengan rekaman IP pengguna dan stempel waktu.
- **Isolasi Berkas Git**: Berkas sementara sistem (`.DS_Store`, `Thumbs.db`) dan konfigurasi editor (`.vscode`, `.idea`) diisolasi dari pelacakan Git melalui ketentuan `.gitignore`.

---

## 📄 Lisensi
Hak cipta proyek dikembangkan untuk kebutuhan operasional **WARKOP MK**.
