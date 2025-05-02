<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require '../includes/config.php';

    // Sanitize input
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Validasi email
    $error = [];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = "Format email tidak valid";
    }

    // Validasi password
    if (strlen($password) < 8) {
        $error[] = "Password minimal 8 karakter";
    }

    // Validasi nomor HP
    if (!empty($phone) && !preg_match('/^[0-9]{10,15}$/', $phone)) {
        $error[] = "Format nomor HP tidak valid (10-15 angka)";
    }

    // Cek email sudah terdaftar
    $check = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        $error[] = "Email sudah terdaftar";
    }

    if (empty($error)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert ke database dengan prepared statement
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO users (email, password, phone, address) 
             VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ssss", $email, $hashed_password, $phone, $address);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: login.php?success=1");
            exit;
        } else {
            $error[] = "Registrasi gagal: " . mysqli_error($conn);
        }
    }

    // Jika ada error, tampilkan semua
    $error = implode("<br>", $error);
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
                        <h2 class="mt-3 mb-0">Daftar Akun Baru</h2>
                        <p class="text-muted">Mulai belanja hoodie eksklusif</p>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Registrasi berhasil! Silakan login
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
                                    placeholder="Minimal 8 karakter"
                                    minlength="8"
                                    required>
                            </div>
                        </div>

                        <!-- Tambahkan field address dan phone -->
                        <div class="mb-3">
                            <label class="form-label">Nomor HP</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="tel"
                                    name="phone"
                                    class="form-control form-control-lg"
                                    placeholder="Contoh: 081234567890"
                                    pattern="[0-9]{10,15}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-map-marker-alt"></i>
                                </span>
                                <textarea name="address"
                                    class="form-control form-control-lg"
                                    placeholder="Contoh: Jl. Sudirman No. 123, Jakarta"
                                    rows="3"></textarea>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="btn btn-primary btn-lg w-100 mb-3">
                            Daftar Sekarang
                        </button>

                        <!-- Login Link -->
                        <div class="text-center">
                            <p class="text-muted">Sudah punya akun?
                                <a href="login.php" class="text-decoration-none">
                                    Login disini
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
    }

    .input-group-text {
        background: white;
        border-right: 0;
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
        transform: translateY(-2px);
    }
</style>

<!-- Modifikasi bagian alert error -->
<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Terjadi kesalahan:</strong><br>
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- [Bagian form tetap sama] -->

<?php include '../includes/footer.php'; ?>