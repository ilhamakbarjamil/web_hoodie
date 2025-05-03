<?php
// session_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HoodieZone - Premium Streetwear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 0.8rem 0;
        }

        .nav-link {
            transition: all 0.3s ease;
            padding: 0.5rem 1rem !important;
        }

        .dropdown-menu {
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: #343a40;
        }

        .dropdown-item {
            color: #fff;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: #495057;
            color: #fff;
        }

        .cart-badge {
            font-size: 0.7em;
            position: relative;
            top: -8px;
            right: -5px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/hoodie_shop/index_logined.php">
                <i class="fas fa-tshirt me-2"></i>
                HoodieZone
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="/hoodie_shop/products/index.php">
                            <i class="fas fa-store me-1"></i>Shop
                        </a>
                    </li>

                    <!-- <li class="nav-item mx-3">
                        <a class="nav-link position-relative" href="cart/index.php">
                            <i class="fas fa-shopping-cart"></i>Chart
                            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                                <span class="badge bg-danger cart-badge">
                                    <?= array_sum($_SESSION['cart']) ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li> -->

                    <!-- Profile Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" 
                           href="#" 
                           id="profileDropdown" 
                           role="button" 
                           data-bs-toggle="dropdown" 
                           aria-expanded="false">
                            <?php if (isset($_SESSION['user_avatar'])): ?>
                                <img src="<?= htmlspecialchars($_SESSION['user_avatar']) ?>" 
                                     class="user-avatar me-2" 
                                     alt="Profile Picture">
                            <?php else: ?>
                                <i class="fas fa-user-circle fa-lg me-2"></i>
                            <?php endif; ?>
                            <?= isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : 'Profile' ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li>
                                <a class="dropdown-item py-2" href="/hoodie_shop/users/profile.php">
                                    <i class="fas fa-user-edit me-2 text-primary"></i>Edit Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="/hoodie_shop/cart/index.php">
                                    <i class="fas fa-shopping-cart me-2 text-primary"></i>
                                    Keranjang
                                    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                                        <span class="badge bg-danger rounded-pill ms-1">
                                            <?= array_sum($_SESSION['cart']) ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="/hoodie_shop/orders/history.php">
                                    <i class="fas fa-box me-2 text-primary"></i>My Orders
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-2"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="../users/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="mt-5 pt-4">