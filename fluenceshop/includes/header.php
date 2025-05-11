<?php
    session_start();
    
    // Initialize cart if not set
    if(!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    // Get cart count
    $cartCount = 0;
    foreach($_SESSION['cart'] as $quantity) {
        $cartCount += $quantity;
    }
    
    // Get page title
    $current_page = basename($_SERVER['PHP_SELF']);
    $page_titles = [
        'index.php' => 'Home',
        'products.php' => 'Products',
        'product.php' => 'Product Details',
        'cart.php' => 'Shopping Cart',
        'checkout.php' => 'Checkout',
        'login.php' => 'Login',
        'register.php' => 'Register',
        'profile.php' => 'My Account',
        'about.php' => 'About Us',
        'contact.php' => 'Contact Us',
    ];
    
    $page_title = isset($page_titles[$current_page]) ? $page_titles[$current_page] . ' - FluenceShop' : 'FluenceShop';
    
    // Get product name for product detail page
    if($current_page == 'product.php' && isset($_GET['id'])) {
        require_once 'db_connect.php';
        $productId = (int)$_GET['id'];
        $productQuery = "SELECT name FROM products WHERE id = $productId";
        $productResult = $conn->query($productQuery);
        
        if($productResult && $productResult->num_rows > 0) {
            $product = $productResult->fetch_assoc();
            $page_title = $product['name'] . ' - FluenceShop';
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div class="header-top-content">
                    <div class="header-top-left">
                        <span><i class="fas fa-phone-alt"></i> +1 (888) 123-4567</span>
                        <span><i class="fas fa-envelope"></i> support@fluenceshop.com</span>
                    </div>
                    <div class="header-top-right">
                        <?php if(isset($_SESSION['user_id'])) { ?>
                            <a href="profile.php"><i class="fas fa-user"></i> My Account</a>
                        <?php } else { ?>
                            <a href="login.php"><i class="fas fa-user"></i> Login / Register</a>
                        <?php } ?>
                        <a href="#"><i class="fas fa-question-circle"></i> Help</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="header-main">
            <div class="container">
                <div class="header-main-content">
                    <div class="logo">
                        <a href="index.php">
                            <span class="logo-icon"><i class="fas fa-video"></i></span>
                            <span class="logo-text">Fluence<span>Shop</span></span>
                        </a>
                    </div>
                    
                    <div class="search-bar">
                        <form action="products.php" method="GET">
                            <input type="text" name="search" placeholder="Search for products...">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                    
                    <div class="header-actions">
                        <a href="#" class="header-action">
                            <i class="fas fa-heart"></i>
                            <span class="action-text">Wishlist</span>
                        </a>
                        
                        <a href="cart.php" class="header-action cart-action">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="action-text">Cart</span>
                            <?php if($cartCount > 0) { ?>
                            <span class="cart-count"><?php echo $cartCount; ?></span>
                            <?php } ?>
                        </a>
                    </div>
                    
                    <div class="mobile-menu-toggle">
                        <button id="menu-toggle">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="header-nav">
            <div class="container">
                <nav class="main-nav">
                    <ul class="nav-menu">
                        <li class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                            <a href="index.php">Home</a>
                        </li>
                        <li class="<?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                            <a href="products.php">Products</a>
                        </li>
                        <li class="<?php echo $current_page == 'combo-deals.php' ? 'active' : ''; ?>">
                            <a href="combo-deals.php">Combo Deals</a>
                        </li>
                        <li class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>">
                            <a href="about.php">Our Story</a>
                        </li>
                        <li class="<?php echo $current_page == 'blog.php' ? 'active' : ''; ?>">
                            <a href="blog.php">Blog</a>
                        </li>
                        <li class="<?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">
                            <a href="contact.php">Contact</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div class="mobile-menu" id="mobile-menu">
            <div class="mobile-menu-header">
                <div class="logo">
                    <a href="index.php">
                        <span class="logo-icon"><i class="fas fa-video"></i></span>
                        <span class="logo-text">Fluence<span>Shop</span></span>
                    </a>
                </div>
                <button class="mobile-menu-close" id="mobile-menu-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="mobile-menu-content">
                <ul class="mobile-nav">
                    <li class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                        <a href="index.php">Home</a>
                    </li>
                    <li class="<?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                        <a href="products.php">Products</a>
                    </li>
                    <li class="<?php echo $current_page == 'combo-deals.php' ? 'active' : ''; ?>">
                        <a href="combo-deals.php">Combo Deals</a>
                    </li>
                    <li class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>">
                        <a href="about.php">Our Story</a>
                    </li>
                    <li class="<?php echo $current_page == 'blog.php' ? 'active' : ''; ?>">
                        <a href="blog.php">Blog</a>
                    </li>
                    <li class="<?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">
                        <a href="contact.php">Contact</a>
                    </li>
                </ul>
                
                <div class="mobile-auth">
                    <?php if(isset($_SESSION['user_id'])) { ?>
                        <a href="profile.php" class="btn-mobile-auth">My Account</a>
                        <a href="includes/logout.php" class="btn-mobile-auth">Logout</a>
                    <?php } else { ?>
                        <a href="login.php" class="btn-mobile-auth">Login</a>
                        <a href="register.php" class="btn-mobile-auth">Register</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </header>