<?php
session_start();
require_once '../includes/config.php';

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

if(isset($_SESSION['payment_message'])): ?>
    <div class="alert alert-<?= $_SESSION['payment_alert_class'] ?> mb-4">
        <?= $_SESSION['payment_message'] ?>
    </div>
    <?php 
    // Clear the message after displaying
    unset($_SESSION['payment_message']);
    unset($_SESSION['payment_alert_class']);
    ?>
<?php endif; 

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

// Ambil items dari order
$items_query = mysqli_prepare($conn, "SELECT * FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($items_query, "i", $order_id);
mysqli_stmt_execute($items_query);
$items_result = mysqli_stmt_get_result($items_query);

$items = [];
while($item = mysqli_fetch_assoc($items_result)) {
    $items[] = $item;
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Pesanan Berhasil Dibuat</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                        <h3 class="mt-3">Terima Kasih Atas Pesanan Anda!</h3>
                        <p class="lead">Order #<?= $order_id ?> telah berhasil dibuat.</p>
                    </div>
                    
                    <div class="order-details mb-4">
                        <h5>Detail Pesanan</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th>Total Pembayaran</th>
                                <td>Rp <?= number_format($order['total'], 0, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <?php if($order['status'] == 'pending'): ?>
                                        <span class="badge bg-warning">Menunggu Pembayaran</span>
                                    <?php elseif($order['status'] == 'paid'): ?>
                                        <span class="badge bg-success">Sudah Dibayar</span>
                                    <?php elseif($order['status'] == 'shipped'): ?>
                                        <span class="badge bg-info">Dikirim</span>
                                    <?php elseif($order['status'] == 'completed'): ?>
                                        <span class="badge bg-primary">Selesai</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= ucfirst($order['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Metode Pengiriman</th>
                                <td><?= ucfirst($order['shipping_method']) ?></td>
                            </tr>
                            <tr>
                                <th>Metode Pembayaran</th>
                                <td><?= ucfirst($order['payment_method']) ?></td>
                            </tr>
                            <tr>
                                <th>Alamat Pengiriman</th>
                                <td><?= htmlspecialchars($order['address']) ?></td>
                            </tr>
                        </table>
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
                        <a href="/hoodie_shop/products/index.php" class="btn btn-primary">Lanjutkan Belanja</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
