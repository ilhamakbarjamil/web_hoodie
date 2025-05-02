<?php
include '../includes/header.php';
require '../includes/config.php';

// Pagination setup
$per_page = 8;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Get products with prepared statement
$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM products 
    ORDER BY created_at DESC 
    LIMIT ?, ?"
);
mysqli_stmt_bind_param($stmt, "ii", $offset, $per_page);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get total products
$total_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM products");
$total_row = mysqli_fetch_assoc($total_result);
$total_pages = ceil($total_row['total'] / $per_page);

// Validate page number
if ($page < 1 || $page > $total_pages) {
    header("Location: ?page=1");
    exit;
}
?>

<div class="container mt-5">
    <h1 class="mb-4 text-center display-5 fw-bold">Koleksi Hoodie Kami</h1>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm product-card">
                        <!-- Di bagian card image, perbaiki kode menjadi: -->
                        <div class="card-img-top position-relative">
                            <img src="/hoodie_shop/images/products/<?= htmlspecialchars($row['image']) ?>"
                                class="img-fluid object-fit-cover"
                                style="height: 300px"
                                alt="<?= htmlspecialchars($row['name']) ?>">
                            <div class="card-badge"></div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?= htmlspecialchars($row['name']) ?></h5>
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="card-text text-danger fw-bold mb-0">
                                    Rp <?= number_format($row['price'], 0, ',', '.') ?>
                                </p>
                                <a href="view.php?id=<?= $row['id'] ?>"
                                    class="btn btn-outline-dark btn-sm">
                                    <i class="fas fa-eye me-1"></i>Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation" class="mt-5">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>">Sebelumnya</a>
                </li>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= $page == $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>">Selanjutnya</a>
                </li>
            </ul>
        </nav>

    <?php else: ?>
        <div class="alert alert-warning text-center py-5 my-5">
            <i class="fas fa-box-open fa-2x mb-3"></i>
            <h4>Produk tidak tersedia saat ini</h4>
        </div>
    <?php endif; ?>
</div>

<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
include '../includes/footer.php';
?>

<style>
    .product-card {
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .card-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9em;
    }

    .page-item.active .page-link {
        background-color: #2d3436;
        border-color: #2d3436;
    }

    .page-link {
        color: #2d3436;
    }

    .page-link:hover {
        color: #1a1e1f;
    }

    .card-img-top {
    transition: transform 0.3s ease-in-out;
    backface-visibility: hidden;
}

.card:hover .card-img-top {
    transform: scale(1.05) rotate(0.5deg);
}

.object-fit-cover {
    object-fit: cover;
    object-position: center top;
}

.card-img-top {
    display: block;
    width: 100%;
    height: auto;
}
</style>