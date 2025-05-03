<?php
session_start();
require_once '../includes/config.php';

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: /hoodie_shop/users/login.php");
    exit();
}

// Validate parameters
if(!isset($_GET['order_id']) || !is_numeric($_GET['order_id']) || !isset($_GET['status'])) {
    die("Invalid parameters");
}

$order_id = (int)$_GET['order_id'];
$status = $_GET['status'];
$user_id = $_SESSION['user_id'];

// Verify order belongs to user
$order_query = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($order_query, "ii", $order_id, $user_id);
mysqli_stmt_execute($order_query);
$result = mysqli_stmt_get_result($order_query);

if(mysqli_num_rows($result) == 0) {
    die("Order not found");
}

// Update order status based on payment result
if($status == 'success') {
    $new_status = 'paid';
    $message = "Pembayaran berhasil! Pesanan Anda sedang diproses.";
    $alert_class = "success";
} elseif($status == 'pending') {
    $new_status = 'pending';
    $message = "Pembayaran dalam proses. Silakan selesaikan pembayaran Anda.";
    $alert_class = "warning";
} else {
    $new_status = 'pending'; // Keep as pending if error
    $message = "Pembayaran gagal. Silakan coba lagi.";
    $alert_class = "danger";
}

// Only update if payment was successful
if($status == 'success') {
    $update_query = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($update_query, "si", $new_status, $order_id);
    mysqli_stmt_execute($update_query);
}

// Redirect to order success page with message
$_SESSION['payment_message'] = $message;
$_SESSION['payment_alert_class'] = $alert_class;
header("Location: order_success.php?order_id=" . $order_id);
exit();
