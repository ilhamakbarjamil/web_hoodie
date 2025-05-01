<?php
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  // Validasi email
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Email tidak valid");
  }

  // Cek email unik
  $check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
  if (mysqli_num_rows($check) > 0) {
    die("Email sudah terdaftar");
  }

  // Insert ke database
  $sql = "INSERT INTO users (email, password) VALUES ('$email', '$password')";
  
  if (mysqli_query($conn, $sql)) {
    header("Location: login.php?success=1");
  } else {
    die("Error: " . mysqli_error($conn));
  }
}
?>