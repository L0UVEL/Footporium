<?php
// Tignan kung wala pang session, kung wala, simulan ito
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cart_count = 0;
// Bilangin ang items sa cart mula sa session
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cart_count += $qty;
    }
}

$user_header_img = '';
// Kunin ang profile image ng user kung naka-login
if (isset($_SESSION['user_id']) && isset($conn)) {
    $hid = $_SESSION['user_id'];
    $hsql = "SELECT profile_image FROM users WHERE id = ?";
    if ($header_stmt = $conn->prepare($hsql)) {
        $header_stmt->bind_param("i", $hid);
        $header_stmt->execute();
        $h_result = $header_stmt->get_result();
        if ($h_row = $h_result->fetch_assoc()) {
            if ($h_row['profile_image']) {
                $user_header_img = '<img src="data:image/png;base64,' . base64_encode($h_row['profile_image']) . '" class="rounded-circle" width="30" height="30" style="object-fit:cover; margin-right:5px;">';
            }
        }
        $header_stmt->close();
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
    <title>Footporium | Premium Footwear</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <source src="assets/audio/bgm.mp3" type="audio/mpeg">
    </audio>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var audio = document.getElementById("bgMusic");
            audio.volume = 0.5; // Set volume to 50%

            function startAudio() {
                // Try playing the audio
                audio.play().then(() => {
                    // Remove listeners pag nag-play na para di mag-duplicate
                    document.removeEventListener('click', startAudio);
                    document.removeEventListener('mousemove', startAudio);
                    document.removeEventListener('scroll', startAudio);
                    document.removeEventListener('keydown', startAudio);
                    document.removeEventListener('touchstart', startAudio);
                }).catch(error => {
                    console.log("Autoplay prevented even on interaction.");
                });
            }

            // Try autoplay immediately (usually blocked by browsers)
            var promise = audio.play();
            if (promise !== undefined) {
                promise.catch(error => {
                    console.log("Autoplay prevented. Waiting for interaction.");
                    // Add listeners para mag-play pag nag-interact si user (click/scroll)
                    document.addEventListener('click', startAudio);
                    document.addEventListener('mousemove', startAudio);
                    document.addEventListener('scroll', startAudio);
                    document.addEventListener('keydown', startAudio);
                    document.addEventListener('touchstart', startAudio);
                });
            }
        });
    </script>


    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-shoe-prints me-2"></i>Footporium
            </a>

            <!-- Mobile Icons (Visible on Mobile) -->
            <div class="d-lg-none d-flex align-items-center gap-3 me-2">
                <a href="#" class="nav-link theme-toggle-btn" title="Toggle Theme">
                    <i class="fas fa-moon fs-5"></i>
                </a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="nav-link text-body" title="My Profile">
                        <?php echo $user_header_img ? $user_header_img : '<i class="fas fa-user-circle fs-5"></i>'; ?>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="nav-link text-body">
                        <i class="fas fa-user fs-5"></i>
                    </a>
                <?php endif; ?>

                <a href="cart.php" class="nav-link position-relative text-body">
                    <i class="fas fa-shopping-cart fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                        style="font-size: 0.6rem; <?php echo ($cart_count > 0) ? '' : 'display: none;'; ?>">
                        <?php echo $cart_count; ?>
                    </span>
                </a>
            </div>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                style="border: none;">
                <i class="fas fa-bars" style="color: var(--text-dark); font-size: 1.5rem;"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>

                    <!-- Desktop User Menu -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link fw-bold text-danger" href="admin/dashboard.php">Admin
                                    Panel</a></li>
                        <?php endif; ?>
                        <!-- User Dropdown Menu -->
                        <li class="nav-item dropdown d-none d-lg-block">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button"
                                data-bs-toggle="dropdown">
                                <?php echo $user_header_img ? $user_header_img : '<i class="fas fa-user-circle me-1"></i>'; ?>
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                                <li><a class="dropdown-item" href="my_orders.php">My Orders</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                        <!-- Mobile Logout Link (since dropdown is hidden on mobile, show simple links) -->
                        <li class="nav-item d-lg-none"><a class="nav-link" href="my_orders.php">My Orders</a></li>
                        <li class="nav-item d-lg-none"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item d-none d-lg-block"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item d-none d-lg-block"><a class="nav-link" href="register.php">Register</a></li>
                    <?php endif; ?>

                    <!-- Desktop Theme Toggle -->
                    <li class="nav-item me-2 d-none d-lg-block">
                        <a href="#" class="nav-link theme-toggle-btn" id="themeToggle" title="Toggle Theme">
                            <i class="fas fa-moon"></i>
                        </a>
                    </li>

                    <!-- Desktop Cart -->
                    <li class="nav-item d-none d-lg-block">
                        <a class="nav-link btn btn-light rounded-pill px-3 ms-2 position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart text-primary"></i> Cart
                            <span id="cart-badge"
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                style="<?php echo ($cart_count > 0) ? '' : 'display: none;'; ?>">
                                <?php echo $cart_count; ?>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main data-barba="container" data-barba-namespace="default">