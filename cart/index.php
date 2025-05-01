<?php
include '../includes/header.php';
require '../includes/config.php';

// Pastikan user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit;
}

// Ambil data cart dari database
$user_id = $_SESSION['user_id'];
$sql = "SELECT p.*, c.quantity 
        FROM carts c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = $user_id";
$result = mysqli_query($conn, $sql);
?>

<div class="container mt-5">
    <h2 class="mb-4">Your Cart</h2>
    
    <?php if(mysqli_num_rows($result) > 0): ?>
    <div class="row">
        <div class="col-md-8">
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="card mb-3 shadow-sm">
                <div class="row g-0">
                    <div class="col-md-4">
                        <img src="../images/<?= $row['image'] ?>" 
                             class="img-fluid rounded-start" 
                             style="height: 200px; object-fit: cover">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h5 class="card-title"><?= $row['name'] ?></h5>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <form action="update_cart.php" method="post" class="d-inline">
                                        <input type="number" name="quantity" 
                                               value="<?= $row['quantity'] ?>" 
                                               min="1" 
                                               class="form-control" 
                                               style="width: 80px">
                                        <input type="hidden" name="product_id" 
                                               value="<?= $row['id'] ?>">
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-primary mt-2">
                                            Update
                                        </button>
                                    </form>
                                    <a href="remove_from_cart.php?id=<?= $row['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger mt-2">
                                        Remove
                                    </a>
                                </div>
                                <h5 class="text-danger">
                                    Rp <?= number_format($row['price'] * $row['quantity'], 0, ',', '.') ?>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <!-- Order Summary -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Order Summary</h5>
                    <?php
                    mysqli_data_seek($result, 0);
                    $total = 0;
                    while($row = mysqli_fetch_assoc($result)) {
                        $total += $row['price'] * $row['quantity'];
                    }
                    ?>
                    <div class="d-flex justify-content-between">
                        <span>Total:</span>
                        <h4 class="text-danger">Rp <?= number_format($total, 0, ',', '.') ?></h4>
                    </div>
                    <a href="../checkout/" class="btn btn-danger w-100 mt-3">Checkout Now</a>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        Your cart is empty. <a href="../products/">Start shopping</a>
    </div>
    <?php endif; ?>
</div>

<?php 
mysqli_close($conn);
include '../includes/footer.php'; 
?>