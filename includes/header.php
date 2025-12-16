<?php
// Tignan kung wala pang session na nag-iistart, kung wala pa, simulan na
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cart_count = 0;
// Check kung may laman ang cart sa session, tapos bilangin ang total items
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cart_count += $qty;
    }
}

$user_header_img = '';

// Optimization: Cache header image in session to avoid fetching BLOB on every page load
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_header_img'])) {
        $user_header_img = $_SESSION['user_header_img'];
    } elseif (isset($conn)) {
        $hid = $_SESSION['user_id'];
        $hsql = "SELECT profile_image FROM users WHERE id = ?";
        if ($header_stmt = $conn->prepare($hsql)) {
            $header_stmt->bind_param("i", $hid);
            $header_stmt->execute();
            $h_result = $header_stmt->get_result();
            if ($h_row = $h_result->fetch_assoc()) {
                if ($h_row['profile_image']) {
                    $imgSrc = htmlspecialchars($h_row['profile_image']);
                    $user_header_img = '<img src="' . $imgSrc . '" class="rounded-circle" width="30" height="30" style="object-fit:cover; margin-right:5px;">';
                    $_SESSION['user_header_img'] = $user_header_img; // Cache it (I-save sa session para mabilis)
                }
            }
            $header_stmt->close();
        }
    }
}
if (!$user_header_img) {
    // Fallback or just icon
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Dynamic Title & Description: Iba-iba ang title depende sa page -->
    <title><?php echo isset($page_title) ? $page_title : 'Footporium | Premium Footwear'; ?></title>
    <meta name="description"
        content="<?php echo isset($page_desc) ? $page_desc : 'Footporium - Discover the most unique and premium collection of feet.'; ?>">
    <meta name="keywords" content="Footporium, feet, shoes, premium footwear, unique collection">
    <meta name="author" content="Footporium">

    <!-- Open Graph (Facebook/LinkedIn) -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
    <meta property="og:title"
        content="<?php echo isset($page_title) ? $page_title : 'Footporium | Premium Footwear'; ?>">
    <meta property="og:description"
        content="<?php echo isset($page_desc) ? $page_desc : 'Footporium - Discover the most unique and premium collection of feet.'; ?>">
    <meta property="og:image"
        content="<?php echo isset($og_image) ? $og_image : 'http://' . $_SERVER['HTTP_HOST'] . '/assets/img/adolfJackson.png'; ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title"
        content="<?php echo isset($page_title) ? $page_title : 'Footporium | Premium Footwear'; ?>">
    <meta name="twitter:description"
        content="<?php echo isset($page_desc) ? $page_desc : 'Footporium - Discover the most unique and premium collection of feet.'; ?>">
    <meta name="twitter:image"
        content="<?php echo isset($og_image) ? $og_image : 'http://' . $_SERVER['HTTP_HOST'] . '/assets/img/adolfJackson.png'; ?>">

    <!-- Favicon -->
    <?php
    $path_prefix = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../' : '';
    ?>
    <link rel="icon" type="image/png" href="<?php echo $path_prefix; ?>assets/img/adolfJackson.png">

    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Footporium",
      "url": "<?php echo "http://$_SERVER[HTTP_HOST]"; ?>",
      "logo": "<?php echo "http://$_SERVER[HTTP_HOST]/assets/img/adolfJackson.png"; ?>"
    }
    </script>
    <?php if (isset($product)): ?>
        <script type="application/ld+json">
                                                {
                                                  "@context": "https://schema.org",
                                                  "@type": "Product",
                                                  "name": "<?php echo htmlspecialchars($product['name']); ?>",
                                                  "image": "<?php echo isset($og_image) ? $og_image : ''; ?>",
                                                  "description": "<?php echo htmlspecialchars(json_encode($product['description']), ENT_QUOTES, 'UTF-8'); ?>",
                                                  "offers": {
                                                    "@type": "Offer",
                                                    "priceCurrency": "PHP",
                                                    "price": "<?php echo $product['price']; ?>"
                                                  }
                                                }
                                                </script>
    <?php endif; ?>

    <!-- Bootstrap CSS -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="assets/vendor/sweetalert2/sweetalert2.all.min.js"></script>
    <!-- Theme Init -->
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
</head>

<body data-barba="wrapper">
    <!-- Background Music -->
    <!-- 'loop' attribute makes the song repeat pag natapos -->
    <audio id="bgMusic" loop>
        <source src="<?php echo $path_prefix; ?>assets/audio/bgm.mp3" type="audio/mpeg">
    </audio>
    <!-- Audio Script moved to script.js for better persistence -->


    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-shoe-prints me-2"></i>Footporium
            </a>

            <!-- Mobile/Desktop Actions (Visible Always) -->
            <!-- order-lg-last ensures it stays on the right on desktop -->
            <div class="d-flex align-items-center ms-auto order-lg-last gap-3">
                <!-- Theme Toggle -->
                <button class="btn btn-link nav-link theme-toggle-btn p-0 border-0" style="font-size: 1.2rem;">
                    <i class="fas fa-moon"></i>
                </button>

                <!-- Cart Pill -->
                <a class="nav-link btn btn-light rounded-pill px-3 position-relative d-flex align-items-center gap-2"
                    href="cart.php">
                    <i class="fas fa-shopping-cart text-primary"></i>
                    <span class="d-none d-sm-inline">Cart</span>
                    <span id="cart-badge"
                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                        style="font-size: 0.7rem; <?php echo ($cart_count > 0) ? '' : 'display: none;'; ?>">
                        <?php echo $cart_count; ?>
                    </span>
                </a>

                <!-- User Profile / Login -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center text-dark p-0" href="#" role="button"
                            data-bs-toggle="dropdown">
                            <?php echo $user_header_img ? $user_header_img : '<i class="fas fa-user-circle fs-4"></i>'; ?>
                            <span
                                class="d-none d-lg-inline ms-1"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="position: absolute; right: 0;">
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <li><a class="dropdown-item text-warning" href="admin/dashboard.php">Admin Panel</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                            <li><a class="dropdown-item" href="my_orders.php">My Orders</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php"
                        class="btn btn-primary-custom btn-sm rounded-pill px-3 py-2 d-none d-sm-block">Login</a>
                    <a href="login.php" class="text-dark fs-4 d-sm-none"><i class="fas fa-sign-in-alt"></i></a>
                <?php endif; ?>

                <!-- Mobile Toggler (Using ms-2 to separate from icons) -->
                <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarContent">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <!-- Collapsible Content (Links) -->
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>

                    <!-- Mobile Only Login/Register Links (If user wants them in menu too, but top button is better) -->
                    <!-- Removed duplicate login links to keep menu clean, user has button on top right -->
                </ul>
            </div>
        </div>
    </nav>
    <main data-barba="container" data-barba-namespace="default">