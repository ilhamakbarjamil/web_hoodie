<?php include '../includes/header.php'; ?>
<?php
require_once '../includes/config.php';
$id = $_GET['id'];
$sql = "SELECT * FROM products WHERE id = $id";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);
?>

<div class="row">
    <div class="col-md-6">
        <img src="/images/<?= $product['image'] ?>" class="img-fluid" alt="<?= $product['name'] ?>">
    </div>
    <div class="col-md-6">
        <h1><?= $product['name'] ?></h1>
        <h3>Rp <?= number_format($product['price'], 0, ',', '.') ?></h3>
        <p><?= $product['description'] ?></p>
        <form action="/cart/add_to_cart.php" method="post">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1">
            </div>
            <button type="submit" class="btn btn-primary">Add to Cart</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>