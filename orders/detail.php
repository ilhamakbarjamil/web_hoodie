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

$order_id = (int)$_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Ambil data order
$order_query = mysqli_prepare($conn, "
    SELECT o.*, u.email, u.address, u.phone, u.nama 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
");
mysqli_stmt_bind_param($order_query, "ii", $order_id, $user_id);
mysqli_stmt_execute($order_query);
$result = mysqli_stmt_get_result($order_query);

if(mysqli_num_rows($result) == 0) {
    die("Order tidak ditemukan");
}

$order = mysqli_fetch_assoc($result);

// Ambil items dari order
$items_query = mysqli_prepare($conn, "
    SELECT oi.*, p.name, p.image 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
mysqli_stmt_bind_param($items_query, "i", $order_id);
mysqli_stmt_execute($items_query);
$items_result = mysqli_stmt_get_result($items_query);

$items = [];
while($item = mysqli_fetch_assoc($items_result)) {
    $items[] = $item;
}

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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detail Pesanan #<?= $order_id ?></h2>
        <a href="/hoodie_shop/orders/history.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Pesanan</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>ID Pesanan:</strong> #<?= $order_id ?></p>
                            <p class="mb-1"><strong>Tanggal:</strong> <?= date('d M Y H:i', strtotime($order['created_at'])) ?></p>
                            <p class="mb-1">
                                <strong>Status:</strong> 
                                <span class="badge bg-<?= $status_labels[$order['status']]['class'] ?>">
                                    <?= $status_labels[$order['status']]['label'] ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Metode Pembayaran:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
                            <p class="mb-1"><strong>Total:</strong> Rp <?= number_format($order['total'], 0, ',', '.') ?></p>
                            <?php if($order['status'] == 'pending'): ?>
                                <a href="/hoodie_shop/checkout/payment.php?order_id=<?= $order['id'] ?>" class="btn btn-success mt-2">
                                    <i class="fas fa-credit-card me-2"></i>Lanjutkan Pembayaran
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Item Pesanan</h5>
                </div>
                <div class="card-body">
                    <?php foreach($items as $item): ?>
                        <div class="d-flex mb-3 pb-3 border-bottom">
                            <img src="/hoodie_shop/images/products/<?= htmlspecialchars($item['image']) ?>" class="img-thumbnail me-3" style="width: 80px; height: 80px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                <p class="mb-0 text-muted">
                                    <?= $item['quantity'] ?> x Rp <?= number_format($item['price'], 0, ',', '.') ?>
                                </p>
                            </div>
                            <div class="text-end">
                                <p class="mb-0 fw-bold">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="d-flex justify-content-between mt-3">
                        <h5>Total</h5>
                        <h5>Rp <?= number_format($order['total'], 0, ',', '.') ?></h5>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Pengiriman</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($order['nama'] ?? 'N/A') ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                    <p class="mb-1"><strong>Telepon:</strong> <?= htmlspecialchars($order['phone'] ?? 'N/A') ?></p>
                    <p class="mb-1"><strong>Alamat:</strong> <?= nl2br(htmlspecialchars($order['address'] ?? 'N/A')) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
