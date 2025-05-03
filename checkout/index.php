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

$user_id = $_SESSION['user_id'];

// Ambil data user
$user_query = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($user_query, "i", $user_id);
mysqli_stmt_execute($user_query);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($user_query));

// Ambil items dari cart
$cart_query = mysqli_prepare($conn, "
    SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image 
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
mysqli_stmt_bind_param($cart_query, "i", $user_id);
mysqli_stmt_execute($cart_query);
$cart_items = mysqli_stmt_get_result($cart_query);

// Hitung total
$total = 0;
$items = [];
while($item = mysqli_fetch_assoc($cart_items)) {
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;
    $items[] = $item;
}

// Jika cart kosong, redirect ke halaman cart
if(count($items) == 0) {
    header("Location: /hoodie_shop/cart/index.php");
    exit();
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Informasi Pengiriman</h4>
                </div>
                <div class="card-body">
                    <form id="checkout-form" action="process_order.php" method="post">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Nomor HP</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat Pengiriman</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required><?= htmlspecialchars($user['address']) ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="shipping" class="form-label">Metode Pengiriman</label>
                            <select class="form-select" id="shipping" name="shipping_method" required>
                                <option value="">Pilih metode pengiriman</option>
                                <option value="regular">Regular (Rp 15.000)</option>
                                <option value="express">Express (Rp 30.000)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment" class="form-label">Metode Pembayaran</label>
                            <select class="form-select" id="payment" name="payment_method" required>
                                <option value="">Pilih metode pembayaran</option>
                                <option value="midtrans">Payment Gateway (Midtrans)</option>
                            </select>
                        </div>
                        
                        <input type="hidden" name="total" value="<?= $total ?>">
                        <button type="submit" class="btn btn-primary btn-lg w-100">Lanjutkan ke Pembayaran</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Ringkasan Pesanan</h4>
                </div>
                <div class="card-body">
                    <div class="order-summary">
                        <?php foreach($items as $item): ?>
                            <div class="d-flex mb-3">
                                <img src="/hoodie_shop/uploads/<?= htmlspecialchars($item['image']) ?>" class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($item['name']) ?></h6>
                                    <small class="text-muted"><?= $item['quantity'] ?> x Rp <?= number_format($item['price'], 0, ',', '.') ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2 shipping-cost">
                            <span>Biaya Pengiriman</span>
                            <span>-</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between fw-bold total-amount">
                            <span>Total</span>
                            <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const shippingSelect = document.getElementById('shipping');
    const shippingCostElement = document.querySelector('.shipping-cost span:last-child');
    const totalElement = document.querySelector('.total-amount span:last-child');
    const totalInput = document.querySelector('input[name="total"]');
    
    let subtotal = <?= $total ?>;
    
    shippingSelect.addEventListener('change', function() {
        let shippingCost = 0;
        
        if (this.value === 'regular') {
            shippingCost = 15000;
        } else if (this.value === 'express') {
            shippingCost = 30000;
        }
        
        shippingCostElement.textContent = 'Rp ' + shippingCost.toLocaleString('id-ID');
        
        const total = subtotal + shippingCost;
        totalElement.textContent = 'Rp ' + total.toLocaleString('id-ID');
        totalInput.value = total;
    });
});
</script>

<?php include '../includes/footer.php'; ?>
