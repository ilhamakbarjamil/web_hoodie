<?php include '../includes/header.php'; ?>
<?php
require_once '../includes/config.php';
$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);
?>

<h2>Our Hoodies</h2>
<div class="row">
    <?php while($row = mysqli_fetch_assoc($result)): ?>
    <div class="col-md-4 mb-4">
        <div class="card">
            <img src="/images/<?= $row['image'] ?>" class="card-img-top" alt="<?= $row['name'] ?>">
            <div class="card-body">
                <h5 class="card-title"><?= $row['name'] ?></h5>
                <p class="card-text">Rp <?= number_format($row['price'], 0, ',', '.') ?></p>
                <a href="/products/view.php?id=<?= $row['id'] ?>" class="btn btn-primary">View Detail</a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php include '../includes/footer.php'; ?>