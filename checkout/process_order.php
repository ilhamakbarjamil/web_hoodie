<?php
session_start();
require_once '../includes/config.php';
// Hapus require '../vendor/autoload.php';

// Hapus use statements
// use Midtrans\Config;
// use Midtrans\Snap;

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: /hoodie_shop/users/login.php");
    exit();
}

// Validasi form
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /hoodie_shop/checkout/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$email = $_SESSION['user_email'] ?? $_POST['email'];
$phone = mysqli_real_escape_string($conn, $_POST['phone']);
$address = mysqli_real_escape_string($conn, $_POST['address']);
$shipping_method = mysqli_real_escape_string($conn, $_POST['shipping_method']);
$payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
$total = (float)$_POST['total'];

// Validasi data
if(empty($phone) || empty($address) || empty($shipping_method) || empty($payment_method)) {
    die("Semua field harus diisi");
}

// Mulai transaksi database
mysqli_begin_transaction($conn);

try {
    // 1. Buat order baru
    $order_stmt = mysqli_prepare($conn, "INSERT INTO orders (user_id, total, status, shipping_method, payment_method, address, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $status = 'pending';
    mysqli_stmt_bind_param($order_stmt, "idsssss", $user_id, $total, $status, $shipping_method, $payment_method, $address, $phone);
    mysqli_stmt_execute($order_stmt);
    
    
    $order_id = mysqli_insert_id($conn);
    
    // 2. Ambil items dari cart
    $cart_query = mysqli_prepare($conn, "
        SELECT c.product_id, c.quantity, p.price, p.name 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    mysqli_stmt_bind_param($cart_query, "i", $user_id);
    mysqli_stmt_execute($cart_query);
    $cart_items = mysqli_stmt_get_result($cart_query);
    
    // 3. Tambahkan items ke order_items
    $order_items_stmt = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, quantity, price, product_name) VALUES (?, ?, ?, ?, ?)");
    
    $order_items = [];
    while($item = mysqli_fetch_assoc($cart_items)) {
        mysqli_stmt_bind_param($order_items_stmt, "iiids", $order_id, $item['product_id'], $item['quantity'], $item['price'], $item['name']);
        mysqli_stmt_execute($order_items_stmt);
        
        $order_items[] = [
            'id' => $item['product_id'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'name' => $item['name']
        ];
    }
    
    // 4. Kosongkan cart
    $clear_cart = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id = ?");
    mysqli_stmt_bind_param($clear_cart, "i", $user_id);
    mysqli_stmt_execute($clear_cart);
    
    // 5. Commit transaksi
    mysqli_commit($conn);
    
    // Untuk sementara, langsung redirect ke halaman sukses
    header("Location: order_success.php?order_id=" . $order_id);
    exit();
    
    /* Kode Midtrans dikomentari untuk sementara
    // 6. Setup Midtrans jika metode pembayaran adalah midtrans
    if($payment_method === 'midtrans') {
        // Setup Midtrans
        Config::$serverKey = 'SB-Mid-server-YourKey';
        Config::$isProduction = false;
        Config::$isSanitized = true;
        
        // Siapkan item details untuk Midtrans
        $items = [];
        foreach($order_items as $item) {
            $items[] = [
                'id' => $item['id'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'name' => $item['name']
            ];
        }
        
        // Tambahkan biaya pengiriman
        $shipping_cost = ($shipping_method === 'regular') ? 15000 : 30000;
        $items[] = [
            'id' => 'shipping',
            'price' => $shipping_cost,
            'quantity' => 1,
            'name' => 'Biaya Pengiriman (' . ucfirst($shipping_method) . ')'
        ];
        
        // Siapkan transaction details
        $transaction = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $total
            ],
            'customer_details' => [
                'first_name' => 'Customer',
                'email' => $email,
                'phone' => $phone
            ],
            'item_details' => $items
        ];
        
        // Generate Snap Token
        try {
            $snapToken = Snap::getSnapToken($transaction);
            
            // Update order dengan snap token
            $update_token = mysqli_prepare($conn, "UPDATE orders SET payment_token = ? WHERE id = ?");
            mysqli_stmt_bind_param($update_token, "si", $snapToken, $order_id);
            mysqli_stmt_execute($update_token);
            
            // Redirect ke halaman pembayaran
            header("Location: payment.php?order_id=" . $order_id);
            exit();
        } catch (Exception $e) {
            die("Payment Error: " . $e->getMessage());
        }
    } else {
        // Redirect ke halaman sukses untuk metode pembayaran lain
        header("Location: order_success.php?order_id=" . $order_id);
        exit();
    }
    */
    
} catch (Exception $e) {
    // Rollback jika ada error
    mysqli_rollback($conn);
    die("Error: " . $e->getMessage());
}
