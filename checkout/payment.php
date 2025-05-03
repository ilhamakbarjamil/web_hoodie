<?php
session_start();
require_once '../includes/config.php';
require_once '../vendor/autoload.php';

// Import Midtrans classes
use Midtrans\Config;
use Midtrans\Snap;

// Configure Midtrans
Config::$serverKey = 'Server Key'; // Replace with your actual sandbox server key
Config::$isProduction = false;
Config::$isSanitized = true;
Config::$is3ds = true;

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: /hoodie_shop/users/login.php");
    exit();
}

// Gunakan header yang sesuai
include '../includes/header_logined.php';

// Validasi order_id
if(!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("ID order tidak valid");
}

$order_id = (int)$_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Ambil data order
$order_query = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($order_query, "ii", $order_id, $user_id);
mysqli_stmt_execute($order_query);
$result = mysqli_stmt_get_result($order_query);

if(mysqli_num_rows($result) == 0) {
    die("Order tidak ditemukan");
}

$order = mysqli_fetch_assoc($result);

// Cek apakah order sudah dibayar
if($order['status'] != 'pending') {
    header("Location: order_success.php?order_id=" . $order_id);
    exit();
}

// Ambil items dari order
$items_query = mysqli_prepare($conn, "SELECT oi.*, p.name as product_name 
                                     FROM order_items oi 
                                     JOIN products p ON oi.product_id = p.id 
                                     WHERE oi.order_id = ?");
mysqli_stmt_bind_param($items_query, "i", $order_id);
mysqli_stmt_execute($items_query);
$items_result = mysqli_stmt_get_result($items_query);

$items = [];
$midtrans_items = [];
while($item = mysqli_fetch_assoc($items_result)) {
    $items[] = $item;
    
    // Prepare items for Midtrans
    $midtrans_items[] = [
        'id' => $item['product_id'],
        'price' => $item['price'],
        'quantity' => $item['quantity'],
        'name' => $item['product_name']
    ];
}

// Get user details for Midtrans
$user_query = mysqli_prepare($conn, "SELECT email, phone, address FROM users WHERE id = ?");
mysqli_stmt_bind_param($user_query, "i", $user_id);
mysqli_stmt_execute($user_query);
$user_result = mysqli_stmt_get_result($user_query);
$user = mysqli_fetch_assoc($user_result);

// Always generate a new token to ensure we have a valid one
$transaction = [
    'transaction_details' => [
        'order_id' => 'HOODIE-' . $order_id . '-' . time(), // Add timestamp to make it unique
        'gross_amount' => (int)$order['total']
    ],
    'customer_details' => [
        'first_name' => 'Customer',
        'email' => $user['email'] ?? 'customer@example.com',
        'phone' => $user['phone'] ?? '08123456789'
    ],
    'item_details' => $midtrans_items
];

try {
    // Generate new Snap Token
    $snapToken = Snap::getSnapToken($transaction);
    
    // Update order with new snap token
    $update_token = mysqli_prepare($conn, "UPDATE orders SET payment_token = ? WHERE id = ?");
    mysqli_stmt_bind_param($update_token, "si", $snapToken, $order_id);
    mysqli_stmt_execute($update_token);
    
} catch (\Exception $e) {
    die("Error generating payment token: " . $e->getMessage() . ". Please check your Midtrans configuration.");
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Pembayaran Order #<?= $order_id ?></h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p class="mb-0">Silahkan selesaikan pembayaran Anda melalui payment gateway.</p>
                    </div>
                    
                    <div class="order-details mb-4">
                        <h5>Detail Pesanan</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th>Total Pembayaran</th>
                                <td>Rp <?= number_format($order['total'] ?? 0, 0, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><span class="badge bg-warning">Menunggu Pembayaran</span></td>
                            </tr>
                            <tr>
                                <th>Metode Pengiriman</th>
                                <td><?= !empty($order['shipping_method']) ? ucfirst($order['shipping_method']) : 'Tidak tersedia' ?></td>
                            </tr>
                            <tr>
                                <th>Alamat Pengiriman</th>
                                <td><?= !empty($order['address']) ? htmlspecialchars($order['address']) : 'Tidak tersedia' ?></td>
                            </tr>                        </table>
                    </div>
                    
                    <div class="items-list mb-4">
                        <h5>Item yang Dibeli</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-center">
                        <button id="pay-button" class="btn btn-primary btn-lg">Bayar Sekarang</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Midtrans JS -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="Client Key"></script>
<script>
    document.getElementById('pay-button').onclick = function() {
        // Trigger snap popup
        snap.pay('<?= $snapToken ?>', {
            onSuccess: function(result) {
                window.location.href = 'payment_notification.php?order_id=<?= $order_id ?>&status=success';
            },
            onPending: function(result) {
                window.location.href = 'payment_notification.php?order_id=<?= $order_id ?>&status=pending';
            },
            onError: function(result) {
                window.location.href = 'payment_notification.php?order_id=<?= $order_id ?>&status=error';
            },
            onClose: function() {
                alert('Anda menutup popup tanpa menyelesaikan pembayaran');
            }
        });
    };
</script>

<?php include '../includes/footer.php'; ?>
