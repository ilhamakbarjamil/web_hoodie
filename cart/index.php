<?php include '../includes/header.php'; ?>

<div class="container">
  <h2>Keranjang Belanja</h2>
  
  <?php if(empty($_SESSION['cart'])): ?>
    <div class="alert alert-info">Keranjang kosong</div>
  
  <?php else: 
    // Ambil semua product ID dari cart
    $product_ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', $product_ids);
    
    $sql = "SELECT * FROM products WHERE id IN ($ids_string)";
    $result = mysqli_query($conn, $sql);
    $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
  ?>
  
  <table class="table">
    <thead>
      <tr>
        <th>Produk</th>
        <th>Harga</th>
        <th>Qty</th>
        <th>Subtotal</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
    <?php 
    $total = 0;
    foreach($products as $product): 
      $qty = $_SESSION['cart'][$product['id']];
      $subtotal = $product['price'] * $qty;
      $total += $subtotal;
    ?>
      <tr>
        <td>
          <img src="/images/<?= $product['image'] ?>" 
               style="width: 50px; height: 50px; object-fit: cover">
          <?= $product['name'] ?>
        </td>
        <td>Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
        <td>
          <form method="post" action="update_cart.php">
            <input type="number" name="quantity" 
                   value="<?= $qty ?>" min="1" style="width: 70px">
            <input type="hidden" name="product_id" 
                   value="<?= $product['id'] ?>">
          </form>
        </td>
        <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
        <td>
          <a href="remove_from_cart.php?id=<?= $product['id'] ?>" 
             class="btn btn-danger btn-sm">Hapus</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <div class="text-end">
    <h4>Total: Rp <?= number_format($total, 0, ',', '.') ?></h4>
    <a href="/checkout" class="btn btn-success">Checkout Sekarang</a>
  </div>
  
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>