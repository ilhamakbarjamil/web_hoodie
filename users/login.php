<?php
session_start();
require '../includes/config.php';

$error = null;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitasi input
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Validasi input
    if(empty($email) || empty($password)) {
        $error = "Harap isi semua field!";
    } else {
        // Cari user di database
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Verifikasi password
            if(password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                
                // Redirect ke halaman utama
                header("Location: ../index.php");
                exit;
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Email tidak terdaftar!";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <!-- Logo Brand -->
                    <div class="text-center mb-4">
                        <i class="fas fa-tshirt fa-3x text-primary"></i>
                        <h2 class="mt-3 mb-0">Masuk ke Akun Anda</h2>
                        <p class="text-muted">Silakan login untuk melanjutkan</p>
                    </div>

                    <?php if($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form method="post">
                        <!-- Email Input -->
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" 
                                       name="email" 
                                       class="form-control form-control-lg"
                                       placeholder="contoh@email.com"
                                       required>
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       name="password" 
                                       class="form-control form-control-lg"
                                       placeholder="Masukkan password"
                                       required>
                            </div>
                            <div class="text-end mt-2">
                                <a href="forgot_password.php" class="text-decoration-none small">
                                    Lupa Password?
                                </a>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                class="btn btn-primary btn-lg w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Masuk
                        </button>

                        <!-- Register Link -->
                        <div class="text-center">
                            <p class="text-muted">Belum punya akun? 
                                <a href="register.php" class="text-decoration-none">
                                    Daftar disini
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
body {
    background: linear-gradient(120deg, #fdfbfb 0%, #ebedee 100%);
    min-height: 100vh;
}

.card {
    border-radius: 20px;
    overflow: hidden;
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.input-group-text {
    background: white;
    border-right: 0;
    min-width: 45px;
}

.form-control {
    border-left: 0;
}

.form-control:focus {
    box-shadow: none;
    border-color: #ced4da;
}

.btn-primary {
    background: #6366f1;
    border: none;
    padding: 12px;
    transition: all 0.3s;
}

.btn-primary:hover {
    background: #4f46e5;
}
</style>

<?php include '../includes/footer.php'; ?>