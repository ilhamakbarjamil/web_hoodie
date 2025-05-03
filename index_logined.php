<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/includes/config.php';

// Query untuk mendapatkan 4 produk terbaru
$sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT 4";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error dalam query: " . mysqli_error($conn));
}

$product_count = mysqli_num_rows($result);
?>

<?php include 'includes/header_logined.php'; ?>

<!-- Hero Section -->
<section class="hero-section d-flex align-items-center text-white">
    <div class="container text-center position-relative z-2">
        <div class="hero-content animate__animated animate__fadeInUp">
            <h1 class="display-3 fw-bold mb-4">Temukan Hoodie Eksklusif</h1>
            <p class="lead mb-4 fs-5">Koleksi terbaru dengan bahan premium dan desain streetwear kekinian</p>
            <a href="../hoodie_shop/products/index.php" class="btn btn-danger btn-lg px-5 rounded-pill">
                <i class="fas fa-shopping-bag me-2"></i>
                Jelajahi Koleksi
            </a>
        </div>
    </div>
    <div class="hero-overlay"></div>
</section>

<!-- Produk Unggulan -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold display-5">Produk Terbaru</h2>
        
        <?php if($product_count > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="col">
                    <div class="card product-card h-100 border-0 shadow-hover">
                        <div class="card-img-top position-relative">
                            <img src="images/products/<?= htmlspecialchars($row['image']) ?>" 
                                 class="img-fluid object-fit-cover" 
                                 style="height: 300px"
                                 alt="<?= htmlspecialchars($row['name']) ?>">
                            <div class="card-badge">NEW</div>
                        </div>
                        <div class="card-body pb-0">
                            <h5 class="card-title fw-bold mb-3"><?= htmlspecialchars($row['name']) ?></h5>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-danger fw-bold fs-5">
                                        Rp <?= number_format($row['price'], 0, ',', '.') ?>
                                    </span>
                                    <span class="text-muted text-decoration-line-through ms-2">
                                        Rp 499.000
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0">
                            <a href="products/view.php?id=<?= $row['id'] ?>" 
                               class="btn btn-dark w-100 rounded-pill">
                                <i class="fas fa-cart-plus me-2"></i>Detail Produk
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center py-4 fs-5">
                <i class="fas fa-box-open me-2"></i>Produk sedang dalam persiapan
            </div>
        <?php endif; ?>
    </div>
</section>

<?php 
mysqli_close($conn);
include 'includes/footer.php'; 
?>

<style>
/* Custom CSS */
.hero-section {
    background: linear-gradient(45deg, #2d3436, #636e72);
    height: 80vh;
    margin-top: -76px;
    padding-top: 76px;
    position: relative;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.3);
    z-index: 1;
}

.product-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.1);
}

.product-card:hover {
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    transform: translateY(-5px);
}

.card-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #ff4757;
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.9em;
}

.object-fit-cover {
    object-fit: cover;
    object-position: center;
}

.btn-dark {
    background: #2d3436;
    transition: all 0.3s;
}

.btn-dark:hover {
    background: #1a1e1f;
}
</style>