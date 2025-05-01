<?php
include '../includes/header.php';
require_once '../vendor/autoload.php'; // Jika pakai composer

use Midtrans\Config;
use Midtrans\Snap;

// Setup Midtrans Config
Config::$serverKey = 'SB-Mid-server-Your_Server_Key';
Config::$isProduction = false; // Sandbox mode
Config::$isSanitized = true;
Config::$is3ds = true;

// Ambil data dari cart
$total = 0;
$items = [];

foreach($_SESSION['cart'] as $product_id => $qty) {
  // Ambil info produk dari DB
  $product = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT * FROM products WHERE id = $product_id"));
  
  $items[] = [
    'id' => $product['id'],
    'price' => $product['price'],
    'quantity' => $qty,
    'name' => $product['name']
  ];
  
  $total += $product['price'] * $qty;
}

// Simpan order ke database
$order_id = 'ORDER-' . time();
mysqli_query($conn, 
  "INSERT INTO orders (user_id, total, status) 
   VALUES ({$_SESSION['user_id']}, $total, 'pending')");
$order_id = mysqli_insert_id($conn); // Dapatkan ID order
?>