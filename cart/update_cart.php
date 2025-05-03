<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /hoodie_shop/users/login.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cart_id']) && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $user_id = $_SESSION['user_id'];
    $cart_id = $_POST['cart_id'];
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity > 0) {
        // Update database
        $stmt = mysqli_prepare($conn, "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "iii", $quantity, $cart_id, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // Update session
        $_SESSION['cart'][$product_id] = $quantity;
    } else {
        // If quantity is 0 or negative, remove the item
        $stmt = mysqli_prepare($conn, "DELETE FROM cart WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $cart_id, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // Remove from session
        unset($_SESSION['cart'][$product_id]);
    }
}

header("Location: /hoodie_shop/cart/index.php");
exit();
?>