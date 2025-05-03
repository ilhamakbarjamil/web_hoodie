<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    // Tambahkan ke session cart (tetap simpan di session untuk kemudahan akses)
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    // Cek apakah produk sudah ada di keranjang user
    $check_stmt = mysqli_prepare($conn, "SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $product_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        // Update quantity jika produk sudah ada di keranjang
        mysqli_stmt_bind_result($check_stmt, $current_quantity);
        mysqli_stmt_fetch($check_stmt);
        $new_quantity = $current_quantity + $quantity;
        
        $update_stmt = mysqli_prepare($conn, "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        mysqli_stmt_bind_param($update_stmt, "iii", $new_quantity, $user_id, $product_id);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
    } else {
        // Insert produk baru ke keranjang
        $insert_stmt = mysqli_prepare($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($insert_stmt, "iii", $user_id, $product_id, $quantity);
        mysqli_stmt_execute($insert_stmt);
        mysqli_stmt_close($insert_stmt);
    }
    
    mysqli_stmt_close($check_stmt);
    
    // Redirect ke halaman keranjang
    header("Location: /hoodie_shop/cart/index.php");
    exit();
}
?>