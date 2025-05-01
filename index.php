<?php
// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mulai session
session_start();

// Include file konfigurasi database
require __DIR__ . '/includes/config.php';
?>

<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<div class="p-5 bg-primary text-white text-center">
  <h1>Welcome to Hoodie Heaven</h1>
  <p class="lead">Temukan hoodie premium dengan kualitas terbaik</p>
</div>

<!-- Featured Products -->
<div class="container mt-5">
  <h2 class="text-center mb-4">Produk Unggulan</h2>
  
  <div class="row">
    <?php
    // Query untuk mendapatkan 4 produk terbaru
    $sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT 4";
    $result = mysqli_query($conn, $sql);
    
    // Cek error query
    if (!$result) {
      die("Error: " . mysqli_error($conn));
    }

    // Handle jika tidak ada produk
    if (mysqli_num_rows($result) == 0) {
      echo '<div class="alert alert-warning">Produk belum tersedia.</div>';
    }

    // Tampilkan produk
    while ($row = mysqli_fetch_assoc($result)) {
    ?>
    <div class="col-md-3 mb-4">
      <div class="card h-100">
        <img src="/images/<?= htmlspecialchars($row['image']) ?>" 
             class="card-img-top" 
             alt="<?= htmlspecialchars($row['name']) ?>"
             style="height: 200px; object-fit: cover">
        
        <div class="card-body">
          <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
          <p class="card-text text-danger fw-bold">
            Rp <?= number_format($row['price'], 0, ',', '.') ?>
          </p>
          <a href="/products/view.php?id=<?= $row['id'] ?>" 
             class="btn btn-primary btn-sm">
            Lihat Detail
          </a>
        </div>
      </div>
    </div>
    <?php } ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<?php
// Tutup koneksi database (opsional)
mysqli_close($conn);
?>