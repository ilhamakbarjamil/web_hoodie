<?php
session_start();
require '../includes/config.php';

// Validasi user login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit;
}

// Mulai transaction
mysqli_begin_transaction($conn);

try {
    // 1. Simpan data ke tabel orders
    $user_id = $_SESSION['user_id'];
    $total = (float)$_POST['total'];
    $address = mysqli_real_escape_string($conn, $_POST['shipping_address']);
    
    $order_sql = "INSERT INTO orders 
                 (user_id, total, shipping_address) 
                 VALUES ($user_id, $total, '$address')";
    mysqli_query($conn, $order_sql);
    $order_id = mysqli_insert_id($conn);

    // 2. Simpan item ke order_items
    $cart_items = mysqli_query($conn, 
        "SELECT c.product_id, c.quantity, p.price 
         FROM carts c 
         JOIN products p ON c.product_id = p.id 
         WHERE c.user_id = $user_id");

    while($item = mysqli_fetch_assoc($cart_items)) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        
        $item_sql = "INSERT INTO order_items 
                    (order_id, product_id, quantity, price) 
                    VALUES ($order_id, $product_id, $quantity, $price)";
        mysqli_query($conn, $item_sql);
    }

    // 3. Kosongkan cart
    mysqli_query($conn, "DELETE FROM carts WHERE user_id = $user_id");

    // Commit transaction
    mysqli_commit($conn);

    // Redirect ke payment
    header("Location: payment.php?order_id=$order_id");
    exit;

} catch (Exception $e) {
    // Rollback jika ada error
    mysqli_rollback($conn);
    die("Error processing order: " . $e->getMessage());
}
?>