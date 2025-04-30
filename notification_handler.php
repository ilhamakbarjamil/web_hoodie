<?php
require_once 'config.php';
require_once 'midtrans_config.php';

$notif = new Midtrans\Notification();

$transaction = $notif->transaction_status;
$order_id = $notif->order_id;
$fraud = $notif->fraud_status;

// Update status order di database
$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE transaction_id = ?");
$stmt->bind_param("ss", $transaction, $order_id);
$stmt->execute();

http_response_code(200);
?>