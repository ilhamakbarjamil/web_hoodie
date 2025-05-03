  <?php
  session_start();
  require '../includes/config.php';
  // Gunakan header yang sesuai berdasarkan status login
  if(isset($_SESSION['user_id'])) {
      include '../includes/header_logined.php';
  } else {
      include '../includes/header.php';
  }
  

  // Validasi ID produk
  if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
      die("ID produk tidak valid");
  }

  $product_id = (int)$_GET['id'];

  // Query produk dengan prepared statement
  $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
  mysqli_stmt_bind_param($stmt, "i", $product_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  if(mysqli_num_rows($result) === 0) {
      die("Produk tidak ditemukan");
  }

  $product = mysqli_fetch_assoc($result);
  ?>

  <div class="container my-5">
      <div class="row g-5">
          <div class="col-md-6">
              <div class="card shadow-sm">
                  <img src="../images/products/<?= htmlspecialchars($product['image']) ?>" 
                     class="card-img-top" 
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     style="max-height: 600px; object-fit: contain">
              </div>
          </div>
        
          <div class="col-md-6">
              <h1 class="display-4 fw-bold"><?= htmlspecialchars($product['name']) ?></h1>
            
              <div class="d-flex align-items-center gap-3 my-4">
                  <h2 class="text-danger mb-0">
                      Rp <?= number_format($product['price'], 0, ',', '.') ?>
                  </h2>
                  <span class="text-muted text-decoration-line-through">
                      Rp 499.000
                  </span>
                  <span class="badge bg-success fs-6">30% OFF</span>
              </div>

              <p class="lead"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

              <form action="../cart/add_to_chart.php" method="post" class="mt-4">
                  <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                
                  <div class="row g-3">
                      <div class="col-auto">
                          <label class="form-label">Jumlah</label>
                          <input type="number" 
                               name="quantity" 
                               class="form-control" 
                               value="1" 
                               min="1" 
                               style="width: 100px">
                      </div>
                      <div class="col-auto d-flex align-items-end">
                          <button type="submit" class="btn btn-danger btn-lg px-5">
                              <i class="fas fa-cart-plus me-2"></i>Tambah ke Keranjang
                          </button>
                      </div>
                  </div>
              </form>

              <hr class="my-5">

              <div class="product-details">
                  <h4 class="mb-3">Detail Produk</h4>
                  <ul class="list-unstyled">
                      <li><strong>Bahan:</strong> Katun Premium 240gsm</li>
                      <li><strong>Ukuran:</strong> S, M, L, XL</li>
                      <li><strong>Warna:</strong> Hitam, Abu-abu, Navy</li>
                      <li><strong>Perawatan:</strong> Bisa mesin cuci</li>
                  </ul>
              </div>
          </div>
        
      </div>
  </div>

  <?php 
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
  include '../includes/footer.php'; 
  ?>