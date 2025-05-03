<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch user data
$stmt = mysqli_prepare($conn, "SELECT id, email, address, phone FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header("Location: login.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid";
    } else {
        // Check if email already exists (if changed)
        if ($email !== $user['email']) {
            $check_email = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id != ?");
            mysqli_stmt_bind_param($check_email, "si", $email, $user_id);
            mysqli_stmt_execute($check_email);
            $email_result = mysqli_stmt_get_result($check_email);
            
            if (mysqli_num_rows($email_result) > 0) {
                $error_message = "Email sudah digunakan oleh pengguna lain";
            }
        }
    }
    
    // If no errors and password change requested
    if (empty($error_message) && !empty($current_password)) {
        // Verify current password
        $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $pwd_result = mysqli_stmt_get_result($stmt);
        $user_data = mysqli_fetch_assoc($pwd_result);
        
        if (!password_verify($current_password, $user_data['password'])) {
            $error_message = "Password saat ini tidak sesuai";
        } elseif (empty($new_password)) {
            $error_message = "Password baru tidak boleh kosong";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "Konfirmasi password tidak sesuai";
        } elseif (strlen($new_password) < 8) {
            $error_message = "Password baru minimal 8 karakter";
        }
    }
    
    // Update profile if no errors
    if (empty($error_message)) {
        if (!empty($current_password) && !empty($new_password)) {
            // Update with new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = mysqli_prepare($conn, "UPDATE users SET email = ?, address = ?, phone = ?, password = ? WHERE id = ?");
            mysqli_stmt_bind_param($update_stmt, "ssssi", $email, $address, $phone, $hashed_password, $user_id);
        } else {
            // Update without changing password
            $update_stmt = mysqli_prepare($conn, "UPDATE users SET email = ?, address = ?, phone = ? WHERE id = ?");
            mysqli_stmt_bind_param($update_stmt, "sssi", $email, $address, $phone, $user_id);
        }
        
        if (mysqli_stmt_execute($update_stmt)) {
            $success_message = "Profil berhasil diperbarui";
            // Update session email if changed
            if ($email !== $_SESSION['user_email']) {
                $_SESSION['user_email'] = $email;
            }
            // Refresh user data
            $stmt = mysqli_prepare($conn, "SELECT id, email, address, phone FROM users WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
        } else {
            $error_message = "Gagal memperbarui profil: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna - HoodieZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 80px;
        }
        .profile-card {
            border-radius: 15px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .profile-header {
            background-color: #343a40;
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px;
        }
        .btn-update {
            background-color: #343a40;
            color: white;
        }
        .btn-update:hover {
            background-color: #23272b;
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../includes/header_logined.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card profile-card">
                    <div class="profile-header">
                        <h3 class="mb-0"><i class="fas fa-user-circle me-2"></i>Profil Pengguna</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle me-2"></i><?= $success_message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-circle me-2"></i><?= $error_message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Alamat</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Nomor Telepon</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-3"><i class="fas fa-key me-2"></i>Ubah Password</h5>
                            <p class="text-muted small mb-3">Kosongkan bagian ini jika tidak ingin mengubah password</p>
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Saat Ini</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Password Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                </div>
                                <div class="form-text">Minimal 8 karakter</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-update">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                                <a href="../index_logined.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Beranda
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
