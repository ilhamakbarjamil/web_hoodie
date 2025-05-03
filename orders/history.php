<?php
session_start();
require_once '../includes/config.php';
require_once '../vendor/autoload.php'; // Add Composer autoload

// Configure Midtrans
\Midtrans\Config::$serverKey = 'YOUR_MIDTRANS_SERVER_KEY'; // Replace with your sandbox server key
\Midtrans\Config::$isProduction = false; // Set false for sandbox mode
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: /hoodie_shop/users/login.php");
    exit();
}

// Gunakan header yang sesuai
include '../includes/header_logined.php';

$user_id = $_SESSION['user_id'];

// Ambil semua order milik user - removed order_number from the query
$orders_query = mysqli_prepare($conn, "
    SELECT o.id, o.total, o.status, o.created_at, o.payment_method, o.payment_token 
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
mysqli_stmt_bind_param($orders_query, "i", $user_id);
mysqli_stmt_execute($orders_query);
$orders_result = mysqli_stmt_get_result($orders_query);

// Status label dan warna
$status_labels = [
    'pending' => ['label' => 'Menunggu Pembayaran', 'class' => 'warning'],
    'paid' => ['label' => 'Dibayar', 'class' => 'success'],
    'processing' => ['label' => 'Diproses', 'class' => 'info'],
    'shipped' => ['label' => 'Dikirim', 'class' => 'primary'],
    'completed' => ['label' => 'Selesai', 'class' => 'success'],
    'cancelled' => ['label' => 'Dibatalkan', 'class' => 'danger']
];
?>

<div class="container mt-5 pt-4">
    <h2 class="mb-4">Riwayat Pesanan</h2>
    
    <?php if(mysqli_num_rows($orders_result) == 0): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Anda belum memiliki pesanan. 
            <a href="/hoodie_shop/products/index.php" class="alert-link">Belanja sekarang</a>.
        </div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID Pesanan</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Metode Pembayaran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($order = mysqli_fetch_assoc($orders_result)): ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td><?= date('d M Y H:i', strtotime($order['created_at'])) ?></td>
                                    <td>Rp <?= number_format($order['total'], 0, ',', '.') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $status_labels[$order['status']]['class'] ?>">
                                            <?= $status_labels[$order['status']]['label'] ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($order['payment_method']) ?></td>
                                    <td>
                                        <a href="/hoodie_shop/orders/detail.php?order_id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        <?php if($order['status'] == 'pending'): ?>
                                            <?php if(!empty($order['payment_token'])): ?>
                                                <button 
                                                    class="btn btn-sm btn-success pay-button" 
                                                    data-token="<?= $order['payment_token'] ?>"
                                                    data-order-id="<?= $order['id'] ?>">
                                                    <i class="fas fa-credit-card"></i> Bayar Sekarang
                                                </button>
                                            <?php else: ?>
                                                <a href="/hoodie_shop/checkout/payment.php?order_id=<?= $order['id'] ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-credit-card"></i> Bayar
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Add Midtrans Snap JS -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-DID8kELBD3qNIhjN"></script>

<script>
    // Add event listeners to all payment buttons
    document.addEventListener('DOMContentLoaded', function() {
        const payButtons = document.querySelectorAll('.pay-button');
        
        payButtons.forEach(button => {
            button.addEventListener('click', function() {
                const token = this.getAttribute('data-token');
                const orderId = this.getAttribute('data-order-id');
                
                // Open Snap payment popup
                snap.pay(token, {
                    onSuccess: function(result) {
                        // Handle success
                        window.location.href = '/hoodie_shop/checkout/order_success.php?order_id=' + orderId;
                    },
                    onPending: function(result) {
                        // Handle pending
                        alert('Pembayaran dalam proses. Silakan selesaikan pembayaran Anda.');
                    },
                    onError: function(result) {
                        // Handle error
                        alert('Pembayaran gagal: ' + result.status_message);
                    },
                    onClose: function() {
                        // Handle customer closing the popup without finishing payment
                        alert('Anda menutup popup tanpa menyelesaikan pembayaran');
                    }
                });
            });
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
