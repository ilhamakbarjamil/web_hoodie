<?php
// Tambahkan di bagian atas config.php
// session_start();

// Security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Timezone setting
date_default_timezone_set('Asia/Jakarta');

$host = "localhost";
$user = "root";
$password = "";
$dbname = "hoodie_shop";

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>