<?php
session_start();
require_once '../includes/config.php';

// Gunakan header yang sesuai berdasarkan status login
if(isset($_SESSION['user_id'])) {
    include '../includes/header_logined.php';
} else {
    include '../includes/header.php';
    // Redirect to login if not logged in
    header("Location: /hoodie_shop/users/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Load cart items from database
$stmt = mysqli_prepare($conn, "
    SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image 
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Sync session cart with database (for cart badge in header)
$_SESSION['cart'] = [];
while ($row = mysqli_fetch_assoc($result)) {
    $_SESSION['cart'][$row['product_id']] = $row['quantity'];
}

// Reset result pointer
mysqli_data_seek($result, 0);

// Check if cart is empty
$cart_empty = mysqli_num_rows($result) == 0;
?>

<div class="container mt-5 pt-4">
    <h2 class="mb-4">Keranjang Belanja</h2>
    
    <?php if($cart_empty): ?>
        <div class="alert alert-info">
            <i class="fas fa-shopping-cart me-2"></i>
            Keranjang belanja Anda masih kosong.
            <a href="/hoodie_shop/products/index.php" class="alert-link">Belanja sekarang</a>.
        </div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total = 0;
                            while ($item = mysqli_fetch_assoc($result)): 
                                $subtotal = $item['price'] * $item['quantity'];
                                $total += $subtotal;
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="/hoodie_shop/images/products/<?= htmlspecialchars($item['image']) ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>" 
                                             class="img-thumbnail me-3" 
                                             style="width: 80px; height: 80px; object-fit: cover;">
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($item['name']) ?></h6>
                                        </div>
                                    </div>
                                </td>
                                <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                <td>
                                    <form action="/hoodie_shop/cart/update_cart.php" method="post" class="d-flex align-items-center">
                                        <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="form-control form-control-sm" style="width: 70px;">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary ms-2">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </form>
                                </td>
                                <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                <td>
                                    <form action="/hoodie_shop/cart/remove_item.php" method="post">
                                        <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash-alt"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Total:</td>
                                <td class="fw-bold">Rp <?= number_format($total, 0, ',', '.') ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="/hoodie_shop/products/index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Lanjutkan Belanja
                </a>
                <a href="/hoodie_shop/checkout/index.php" class="btn btn-danger">
                    <i class="fas fa-shopping-bag me-2"></i>Checkout
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php 
mysqli_stmt_close($stmt);
include '../includes/footer.php'; 
?>