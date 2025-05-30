# Hoodie Shop E-Commerce

Sebuah platform e-commerce sederhana untuk penjualan hoodie, dibangun dengan PHP, MySQL, dan Midtrans Payment Gateway.

![Hoodie Shop Preview](screenshots/preview.png) 

## Fitur

- ✅ Registrasi & Login User
- ✅ Manajemen Produk (Admin)
- ✅ Keranjang Belanja
- ✅ Checkout dengan Midtrans
- ✅ Riwayat Transaksi
- ✅ Responsive Design

## Teknologi

- PHP 7.4+
- MySQL 5.7+
- [Midtrans SDK](https://midtrans.com/)
- Bootstrap 5
- XAMPP/Apache

## Instalasi

### Persyaratan
- Web server (XAMPP/WAMP/MAMP)
- Composer
- Akun Midtrans (Sandbox)

### Langkah-langkah

1. **Clone Repository**
   ```bash
   git clone https://github.com/username/hoodie_shop.git
   cd hoodie_shop

2. **Download Comoser**
   ```bash
   composer init --no-interaction
   composer require midtrans/midtrans-php

3. **Masukkan Key Midtrans**
   Masukkan client-key dan server-key di folder checkout
