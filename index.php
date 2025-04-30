<?php
require_once 'config.php';
include 'header.php';

$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<div class="row">
    <?php while($row = $result->fetch_assoc()): ?>
    <div class="col-md-4 mb-4">
        <div class="card">
            <img src="assets/images/<?= $row['image'] ?>" class="card-img-top">
            <div class="card-body">
                <h5 class="card-title"><?= $row['name'] ?></h5>
                <p class="card-text">Rp <?= number_format($row['price'], 0, ',', '.') ?></p>
                <a href="product.php?id=<?= $row['id'] ?>" class="btn btn-primary">View Product</a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php include 'footer.php'; ?>