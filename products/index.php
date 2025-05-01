<?php
include '../includes/header.php';
require '../includes/config.php';

// Pagination
$per_page = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Query products
$sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT $offset, $per_page";
$result = mysqli_query($conn, $sql);

// Total products
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM products");
$total_row = mysqli_fetch_assoc($total_query);
$total_pages = ceil($total_row['total'] / $per_page);
?>

<div class="container mt-5">
    <h1 class="mb-4">All Products</h1>
    
    <div class="row row-cols-1 row-cols-md-4 g-4">
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <div class="col">
            <div class="card h-100 shadow-sm">
                <img src="../images/<?= $row['image'] ?>" 
                     class="card-img-top" 
                     style="height: 300px; object-fit: cover">
                <div class="card-body">
                    <h5 class="card-title"><?= $row['name'] ?></h5>
                    <p class="card-text text-danger fw-bold">
                        Rp <?= number_format($row['price'], 0, ',', '.') ?>
                    </p>
                    <a href="view.php?id=<?= $row['id'] ?>" 
                       class="btn btn-outline-dark w-100">
                        View Details
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <nav class="mt-5">
        <ul class="pagination justify-content-center">
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<?php 
mysqli_close($conn);
include '../includes/footer.php'; 
?>