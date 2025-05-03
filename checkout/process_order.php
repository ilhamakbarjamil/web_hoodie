<?php
session_start();
require_once '../includes/config.php';
require_once '../vendor/autoload.php';

use Midtrans\Config;
use Midtrans\Snap;

// Configure Midtrans
Config::$serverKey = 'Server KeyN';
Config::$isProduction = false;
Config::$isSanitized = true;
Config::$is3ds = true;

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: /hoodie_shop/users/login.php");
    exit();
}

// Process form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];

    // Get shipping information - adjust field names based on your actual form
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');

    // Get cart items
    $cart_query = mysqli_prepare($conn, "SELECT c.id, c.product_id, c.quantity, p.name, p.price 
                                        FROM cart c 
                                        JOIN products p ON c.product_id = p.id 
                                        WHERE c.user_id = ?");
    mysqli_stmt_bind_param($cart_query, "i", $user_id);
    mysqli_stmt_execute($cart_query);
    $cart_result = mysqli_stmt_get_result($cart_query);

    // Calculate total and prepare items for Midtrans
    $total = 0;
    $items = [];
    $cart_items = [];

    while ($item = mysqli_fetch_assoc($cart_result)) {
        $subtotal = $item['price'] * $item['quantity'];
        $total += $subtotal;

        $cart_items[] = [
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'name' => $item['name']
        ];

        $items[] = [
            'id' => $item['product_id'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'name' => $item['name']
        ];
    }

    // Insert order into database - using a simple approach
    $order_query = mysqli_prepare($conn, "INSERT INTO orders (user_id, status) VALUES (?, 'pending')");
    mysqli_stmt_bind_param($order_query, "i", $user_id);
    mysqli_stmt_execute($order_query);
    $order_id = mysqli_insert_id($conn);

    // Insert order items
    foreach ($cart_items as $item) {
        $order_item_query = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, quantity, price) 
                                                 VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($order_item_query, "iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
        mysqli_stmt_execute($order_item_query);
    }

    // Clear cart
    $clear_cart = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id = ?");
    mysqli_stmt_bind_param($clear_cart, "i", $user_id);
    mysqli_stmt_execute($clear_cart);

    // Get user information for Midtrans - using email only since we know it exists
    $user_query = mysqli_prepare($conn, "SELECT email FROM users WHERE id = ?");
    mysqli_stmt_bind_param($user_query, "i", $user_id);
    mysqli_stmt_execute($user_query);
    $user_result = mysqli_stmt_get_result($user_query);
    $user = mysqli_fetch_assoc($user_result);
    $email = $user['email'] ?? '';

    // Default values for required Midtrans fields
    $customer_name = 'Customer';
    $phone = '08123456789';

    // When creating the Midtrans transaction, use a consistent order ID format
    $transaction = [
        'transaction_details' => [
            'order_id' => 'HOODIE-' . $order_id,
            'gross_amount' => (int)$total
        ],
        'customer_details' => [
            'first_name' => $customer_name,
            'email' => $email,
            'phone' => $phone,
            'billing_address' => [
                'address' => $address
            ]
        ],
        'item_details' => $items
    ];

    // Generate Snap Token
    try {
        $snapToken = Snap::getSnapToken($transaction);

        // Update order with snap token
        $update_token = mysqli_prepare($conn, "UPDATE orders SET payment_token = ? WHERE id = ?");
        mysqli_stmt_bind_param($update_token, "si", $snapToken, $order_id);
        mysqli_stmt_execute($update_token);

        // Redirect ke halaman pembayaran
        header("Location: payment.php?order_id=" . $order_id);
        exit();
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }
} else {
    // If not a POST request, redirect to checkout page
    header("Location: checkout.php");
    exit();
}
