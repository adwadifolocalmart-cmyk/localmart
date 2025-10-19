<?php
// header.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['user_id']);
$user_type = $_SESSION['user_type'] ?? null;
$username = $_SESSION['username'] ?? 'User';
$email = $_SESSION['email'] ?? 'user@example.com';
$cart_item_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$unread_notifications = $_SESSION['unread_notifications'] ?? 0;

// Detect profile image with flexible extension
$profile_image = 'default.png';
$profile_dir = 'uploads/profile/';
$extensions = ['jpg', 'jpeg', 'png', 'webp'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Adwadifo'; ?></title>
    <style>
        :root {
            --primary-color: #1e8449;
            --hover-color: #145a32;
            --accent-color: #27ae60;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .site-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem 2rem;
        }

        .site-header .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .logo a {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .logo a:hover {
            color: var(--hover-color);
        }

        .search-bar form {
            display: flex;
            align-items: center;
        }

        .search-bar input {
            padding: 8px 12px;
            border: 1px solid var(--hover-color);
            border-radius: 4px 0 0 4px;
            width: 220px;
            outline: none;
        }

        .search-bar button {
            background: var(--primary-color);
            color: white;
            border: 1px solid var(--hover-color);
            padding: 8px 14px;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }

        .nav-wrapper {
            display: flex;
            align-items: center;
        }

        .main-nav ul {
            list-style: none;
            display: flex;
            gap: 15px;
            margin: 0;
            padding: 0;
        }

        .main-nav a {
            color: #2d5016;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .main-nav a:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .cart-count {
            background: white;
            color: var(--accent-color);
            padding: 2px 8px;
            border-radius: 50%;
            font-weight: bold;
        }

        /* Profile dropdown */
        .profile-dropdown {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-left: 1rem;
        }

        .profile-dropdown img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-color);
            cursor: pointer;
        }

        .profile-dropdown .username {
            font-size: 0.85rem;
            color: #2d5016;
            margin-top: 4px;
            font-weight: 600;
        }

        .profile-dropdown .email {
            font-size: 0.75rem;
            color: #555;
            margin-top: 2px;
        }

        .profile-dropdown-menu {
            position: absolute;
            top: 60px;
            right: 0;
            background: white;
            border: 1px solid #ccc;
            border-radius: 6px;
            display: none;
            flex-direction: column;
            min-width: 180px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            z-index: 1001;
        }

        .profile-dropdown:hover .profile-dropdown-menu {
            display: flex;
        }

        .profile-dropdown-menu a {
            padding: 10px;
            color: #2d5016;
            font-weight: 500;
            text-decoration: none;
            position: relative;
        }

        .profile-dropdown-menu a:hover {
            background: #f0f0f0;
            color: var(--primary-color);
        }

        .notification-badge {
            position: absolute;
            top: 8px;
            right: 10px;
            background: #dc3545;
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 50%;
        }

        /* Mobile Menu Toggle */
        .menu-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            display: none;
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }

            .nav-wrapper {
                position: fixed;
                top: 70px;
                left: 0;
                right: 0;
                background: white;
                transform: translateY(-100%);
                transition: transform 0.3s ease;
                z-index: 999;
            }

            body.nav-open .nav-wrapper {
                transform: translateY(0);
            }

            .main-nav ul {
                flex-direction: column;
                padding: 1rem;
            }

            .main-nav ul li {
                margin-bottom: 10px;
            }

            .search-bar input {
                width: 180px;
            }

            .logo a {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <button class="menu-toggle" onclick="document.body.classList.toggle('nav-open')">☰</button>
        <div class="container">
            <div class="logo">
                <a href="index.php">Adwadifo 🥦</a>
            </div>

            <div class="search-bar">
                <form method="GET" action="index.php">
                    <input type="text" name="search" placeholder="Search for products, vendors, or categories..."
                           value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit">Search</button>
                </form>
            </div>

            <div class="nav-wrapper">
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>

                        <?php if ($is_logged_in): ?>
                            <?php if ($user_type === 'vendor'): ?>
                                <li><a href="add_product.php">Add Product</a></li>
                            <?php endif; ?>
                            <li><a href="order_history.php">Order History</a></li>
                            <li><a href="cart.php">Cart <span class="cart-count"><?php echo $cart_item_count; ?></span></a></li>
                            <li><a href="about.php">About Us</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Login</a></li>
                            <li><a href="register.php">Register</a></li>
                            <li><a href="cart.php">Cart <span class="cart-count"><?php echo $cart_item_count; ?></span></a></li>
                            <li><a href="about.php">About Us</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <?php if ($is_logged_in): ?>
                    <div class="profile-dropdown">
                        <img src="uploads/profile/<?php echo htmlspecialchars($profile_image); ?>"
                             alt="Profile Picture">
                             <span class="username"><?php echo htmlspecialchars($username); ?></span>
                        <span class="email"><?php echo htmlspecialchars($email); ?></span>
                        <div class="profile-dropdown-menu">
                            <a href="profile.php?id=<?php echo $_SESSION['user_id']; ?>">View Profile</a>
                            <a href="notifications.php">
                                Notifications
                                <?php if ($unread_notifications > 0): ?>
                                    <span class="notification-badge"><?php echo $unread_notifications; ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main style="margin-top: 100px;">
