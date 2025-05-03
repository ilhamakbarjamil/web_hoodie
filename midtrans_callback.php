<?php
require_once 'includes/config.php';
require_once 'vendor/autoload.php';

use Midtrans\Config;

Config::$isProduction = false;
Config::$serverKey = 'SB-Mid-server-YOUR_SERVER_KEY'; // Replace with your server key

try {
    $notif = new \Midtrans\Notification();
} catch (\Exception $e) {
    exit($e->getMessage());
}

$transaction = $notif->transaction_status;
$fraud = $notif->fraud_status;
$order_id = $notif->order_id;

// Extract the actual order ID from the Midtrans order ID format (HOODIE-xxx-timestamp)
$parts = explode('-', $order_id);
if (count($parts) >= 2) {
    $actual_order_id = $parts[1];
} else {
    exit('Invalid order ID format');
}

// Handle transaction status
if ($transaction == 'capture') {
    if ($fraud == 'challenge') {
        $status = 'pending';
    } else {
        $status = 'paid';
    }
} else if ($transaction == 'settlement') {
    $status = 'paid';
} else if ($transaction == 'pending') {
    $status = 'pending';
} else if ($transaction == 'deny') {
    $status = 'cancelled';
} else if ($transaction == 'expire') {
    $status = 'cancelled';
} else if ($transaction == 'cancel') {
    $status = 'cancelled';
}

// Update order status
$update_query = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");
mysqli_stmt_bind_param($update_query, "si", $status, $actual_order_id);
$result = mysqli_stmt_execute($update_query);

if (!$result) {
    error_log("Failed to update order status: " . mysqli_error($conn));
}

// Return success response
header('HTTP/1.1 200 OK');