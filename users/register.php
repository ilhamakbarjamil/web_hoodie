<?php
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    require '../includes/config.php';
    
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Validasi email
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Cek email exists
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
        if(mysqli_num_rows($check) > 0) {
            $error = "Email already registered";
        } else {
            // Insert user
            $sql = "INSERT INTO users (email, password) VALUES ('$email', '$password')";
            if(mysqli_query($conn, $sql)) {
                header("Location: login.php?success=1");
                exit;
            } else {
                $error = "Registration failed: " . mysqli_error($conn);
            }
        }
    }
}
?>

<?php include '../includes/footer.php';?>