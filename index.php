<?php
// Tambahkan di bagian atas file
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/includes/config.php';

// Query produk dengan error handling
$sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT 4";
$result = mysqli_query($conn, $sql);

// Cek error query
if (!$result) {
    die("Error dalam query: " . mysqli_error($conn));
}

// Cek apakah ada hasil query
$product_count = mysqli_num_rows($result);
?>

<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section d-flex align-items-center text-white">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-4">Premium Streetwear Collection</h1>
        <p class="lead mb-4">Temukan hoodie eksklusif dengan kualitas bahan terbaik</p>
        <a href="products/" class="btn btn-light btn-lg px-5">
            <i class="fas fa-shopping-bag me-2"></i>
            Belanja Sekarang
        </a>
    </div>
</section>

<!-- Featured Products -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold">Produk Unggulan</h2>
        
        <?php if($product_count > 0): ?>
            <div class="row g-4">
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card product-card h-100 shadow-sm">
                        <img src="/images/<?= htmlspecialchars($row['image']) ?>" 
                             class="card-img-top object-fit-cover" 
                             style="height: 250px"
                             alt="<?= htmlspecialchars($row['name']) ?>">
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?= htmlspecialchars($row['name']) ?></h5>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-danger fw-bold fs-5">
                                    Rp <?= number_format($row['price'], 0, ',', '.') ?>
                                </span>
                                <a href="/products/view.php?id=<?= $row['id'] ?>" 
                                   class="btn btn-outline-dark">
                                    <i class="fas fa-eye me-2"></i>Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                Tidak ada produk yang tersedia saat ini.
            </div>
        <?php endif; ?>
    </div>
</section>

<?php 
// Tutup koneksi database
mysqli_close($conn);
include 'includes/footer.php'; 
?>