<?php
session_start();
// require '../includes/config.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = (float)$_POST['price'];
    
    // File upload handling
    $image = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        
        if(in_array(strtolower($ext), $allowed)) {
            $filename = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], "../images/products/$filename");
            $image = $filename;
        }
    }
    
    $stmt = mysqli_prepare($conn, 
        "INSERT INTO products (name, description, price, image) 
        VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssds", $name, $description, $price, $image);
    mysqli_stmt_execute($stmt);
    
    $success = "Produk berhasil ditambahkan!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar { width: 250px; height: 100vh; }
        .main-content { flex: 1; }
    </style>
</head>
<body class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar bg-dark text-white p-3">
        <h4>Admin Panel</h4>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link text-white">Tambah Produk</a>
            </li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link text-white">Logout</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content p-4">
        <h2>Tambah Produk Baru</h2>
        
        <?php if(isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="mt-4">
            <div class="mb-3">
                <label>Nama Produk</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control" rows="3" required></textarea>
            </div>

            <div class="mb-3">
                <label>Harga</label>
                <input type="number" name="price" class="form-control" step="500" required>
            </div>

            <div class="mb-3">
                <label>Gambar Produk</label>
                <input type="file" name="image" class="form-control" accept="image/*" required>
                <small class="text-muted">Format: JPG, PNG, WEBP (Max 2MB)</small>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Produk</button>
        </form>
    </div>
</body>
</html>