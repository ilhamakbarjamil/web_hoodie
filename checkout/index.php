<?php
include '../includes/header.php';
require '../includes/config.php';

// Pastikan user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit;
}

// Ambil data cart
$user_id = $_SESSION['user_id'];
$cart_items = mysqli_query($conn, 
    "SELECT p.*, c.quantity 
     FROM carts c 
     JOIN products p ON c.product_id = p.id 
     WHERE c.user_id = $user_id");

// Hitung total
$total = 0;
while($item = mysqli_fetch_assoc($cart_items)) {
    $total += $item['price'] * $item['quantity'];
}
?>

<div class="container mt-5">
    <h2>Checkout</h2>
    
    <!-- Form Alamat Pengiriman -->
    <form action="process_checkout.php" method="post">
        <div class="mb-3">
            <label>Alamat Lengkap</label>
            <textarea name="shipping_address" class="form-control" required></textarea>
        </div>
        
        <!-- Ringkasan Order -->
        <div class="card mb-4 shadow">
            <div class="card-body">
                <h5 class="card-title">Ringkasan Pesanan</h5>
                <ul class="list-group">
                    <?php 
                    mysqli_data_seek($cart_items, 0);
                    while($item = mysqli_fetch_assoc($cart_items)): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= $item['name'] ?> (<?= $item['quantity'] ?>x)</span>
                        <span>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></span>
                    </li>
                    <?php endwhile; ?>
                </ul>
                <hr>
                <h4 class="text-end text-danger">
                    Total: Rp <?= number_format($total, 0, ',', '.') ?>
                </h4>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary btn-lg w-100">
            Lanjut ke Pembayaran
        </button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>