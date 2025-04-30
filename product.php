<?php
require_once 'config.php';
include 'header.php';

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
?>

<div class="row">
    <div class="col-md-6">
        <img src="assets/images/<?= $product['image'] ?>" class="img-fluid">
    </div>
    <div class="col-md-6">
        <h1><?= $product['name'] ?></h1>
        <h3>Rp <?= number_format($product['price'], 0, ',', '.') ?></h3>
        <p><?= $product['description'] ?></p>
        <form method="post" action="cart.php">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>