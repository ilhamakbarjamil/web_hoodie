<?php
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    require '../includes/config.php';
    
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if(mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if(password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            header("Location: ../");
            exit;
        }
    }
    
    $error = "Invalid email or password";
}
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-5" style="max-width: 500px">
    <div class="card shadow">
        <div class="card-body">
            <h2 class="card-title mb-4">Login</h2>
            
            <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            
            <div class="mt-3 text-center">
                Don't have an account? <a href="register.php">Register here</a>
           