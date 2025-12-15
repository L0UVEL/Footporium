<?php
include 'includes/db_connect.php';

// Get Product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch Product Details
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    // Set Dynamic SEO Metadata
    $page_title = htmlspecialchars($product['name']) . " | Footporium";
    $page_desc = "Buy " . htmlspecialchars($product['name']) . " at Footporium. " . htmlspecialchars(substr($product['description'], 0, 100)) . "...";

    // Set Open Graph Image
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $base_url = $protocol . "://" . $_SERVER['HTTP_HOST'];
    // Assuming image_proxy.php is in the root directory relative to the site
    // Adjust path if files are in subfolder, but usually $_SERVER['HTTP_HOST'] is root.
    // If the site is in a subfolder like /WEB-PROJECTS/Footporium/, we need to account for that.
    // simpler: relative path from root if we know it, or just use absolute path logic.
    // Let's rely on relative path from web root if possible, or construct fully qualified.
    // Since I don't know the exact web root offset easily, I'll assume root or relative.
    // Actually, header.php uses $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'].
    // I'll try to find the path to image_proxy.php relative to current script.
    // Since product_details.php is in root, image_proxy.php is too.
    $og_image = $base_url . '/' . ($product['image_url'] ?? 'assets/img/placeholder.png');
} else {
    // If product not found, set default or redirect logic (we'll handle redirect in body or here)
    echo "<div class='container my-5 text-center'><h1>Product not found</h1><a href='products.php' class='btn btn-primary-custom'>Back to Products</a></div>";
    // Note: If we output HTML here before header is included, it might look broken if we don't include header.
    // Better logic: INCLUDE header with "Not Found" title, then show error.
    // However, original code exited. Let's redirect or show proper 404 page structure.
    // For now, adhering to original logic but including header for clean exit.
    $page_title = "Product Not Found | Footporium";
    include 'includes/header.php';
    echo "<div class='container my-5 text-center'><h1>Product not found</h1><a href='products.php' class='btn btn-primary-custom'>Back to Products</a></div>";
    include 'includes/footer.php';
    exit;
}

// Fetch Reviews
$review_sql = "SELECT r.*, u.first_name, u.last_name, u.profile_image FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC";
$review_stmt = $conn->prepare($review_sql);
$review_stmt->bind_param("i", $product_id);
$review_stmt->execute();
$reviews_result = $review_stmt->get_result();

// Include Header AFTER fetching data to use $page_title
include 'includes/header.php';
?>

<!-- Product Details Section -->
<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0 mb-4">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item"><a href="products.php" class="text-decoration-none text-muted">Products</a></li>
            <li class="breadcrumb-item active text-dark" aria-current="page">
                <?php echo htmlspecialchars($product['name']); ?>
            </li>
        </ol>
    </nav>

    <div class="row g-5">
        <!-- Product Image -->
        <div class="col-lg-6">
            <div class="product-detail-img-wrapper rounded-4 shadow-sm position-relative overflow-hidden"
                style="height: 500px; background-color: var(--bg-card);">
                <?php
                $mainImg = !empty($product['image_url']) ? $product['image_url'] : 'assets/img/placeholder.png';
                ?>
                <img src="<?php echo htmlspecialchars($mainImg); ?>"
                    alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid w-100 h-100"
                    style="object-fit: contain;">
                <span class="position-absolute top-0 end-0 m-3 badge bg-primary rounded-pill px-3 py-2">Premium</span>
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-6 d-flex flex-column justify-content-center">
            <h1 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>

            <!-- Average Rating -->
            <?php
            // Calculate Average
            $avg_sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE product_id = ?";
            $avg_stmt = $conn->prepare($avg_sql);
            $avg_stmt->bind_param("i", $product_id);
            $avg_stmt->execute();
            $avg_res = $avg_stmt->get_result()->fetch_assoc();
            $avg_rating = round($avg_res['avg_rating'] ?? 0, 1);
            $review_count = $avg_res['count'];
            ?>
            <div class="mb-4 d-flex align-items-center gap-2">
                <div class="text-warning">
                    <?php
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $avg_rating)
                            echo '<i class="fas fa-star"></i>';
                        elseif ($i - 0.5 <= $avg_rating)
                            echo '<i class="fas fa-star-half-alt"></i>';
                        else
                            echo '<i class="far fa-star"></i>';
                    }
                    ?>
                </div>
                <span class="text-muted small">(<?php echo $review_count; ?> reviews)</span>
            </div>

            <h2 class="text-primary fw-bold mb-4 display-6">₱<?php echo number_format($product['price'], 2); ?></h2>

            <p class="lead text-secondary mb-5" style="line-height: 1.8;">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </p>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="actions/add_to_cart_action.php" method="POST" class="mb-4">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="quantity-control d-flex align-items-center justify-content-between border rounded-pill px-3 py-2"
                                style="background-color: var(--bg-input);">
                                <button type="button" class="btn btn-link text-dark p-0 qty-decrease"><i
                                        class="fas fa-minus"></i></button>
                                <input type="number" name="quantity" id="quantity" value="1" min="1"
                                    class="form-control border-0 text-center bg-transparent p-0 fw-bold"
                                    style="width: 40px;">
                                <button type="button" class="btn btn-link text-dark p-0 qty-increase"><i
                                        class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <button type="submit" class="btn btn-primary-custom btn-lg w-100 rounded-pill shadow-sm">
                                <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="mb-4">
                    <a href="login.php" class="btn btn-outline-primary btn-lg w-100 rounded-pill shadow-sm">
                        <i class="fas fa-sign-in-alt me-2"></i> Login to Add to Cart
                    </a>
                </div>
            <?php endif; ?>

            <div class="d-flex gap-4 p-3 rounded-3 mt-auto" style="background-color: var(--bg-navbar);">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-check-circle text-success fs-5"></i>
                    <span class="fw-medium">In Stock</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-truck text-primary fs-5"></i>
                    <span class="fw-medium">Fast Shipping</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-shield-alt text-warning fs-5"></i>
                    <span class="fw-medium">Authentic</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Related Products -->
<section class="py-5 border-top">
    <div class="container">
        <h3 class="fw-bold mb-4">You Might Also Like</h3>
        <div class="row g-4">
            <?php
            $rel_sql = "SELECT * FROM products WHERE id != ? ORDER BY RAND() LIMIT 4";
            $rel_stmt = $conn->prepare($rel_sql);
            $rel_stmt->bind_param("i", $product_id);
            $rel_stmt->execute();
            $related = $rel_stmt->get_result();
            while ($rel = $related->fetch_assoc()):
                ?>
                <div class="col-6 col-md-3">
                    <div class="product-card h-100 small-card">
                        <div class="product-img-wrapper" style="height: 200px; padding: 15px;">
                            <a href="product_details.php?id=<?php echo $rel['id']; ?>">
                                <?php
                                $relImg = !empty($rel['image_url']) ? $rel['image_url'] : 'assets/img/placeholder.png';
                                ?>
                                <img src="<?php echo htmlspecialchars($relImg); ?>"
                                    alt="<?php echo htmlspecialchars($rel['name']); ?>">
                            </a>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="card-title fw-bold text-truncate mb-1">
                                <a href="product_details.php?id=<?php echo $rel['id']; ?>"
                                    class="text-dark text-decoration-none"><?php echo htmlspecialchars($rel['name']); ?></a>
                            </h6>
                            <span class="price fs-6">₱<?php echo number_format($rel['price'], 2); ?></span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Reviews Section -->
<section class="py-5" style="background-color: var(--bg-light);">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h3 class="fw-bold mb-1">Customer Reviews</h3>
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4 fw-bold"><?php echo $avg_rating; ?></span>
                    <div class="text-warning">
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $avg_rating)
                                echo '<i class="fas fa-star"></i>';
                            elseif ($i - 0.5 <= $avg_rating)
                                echo '<i class="fas fa-star-half-alt"></i>';
                            else
                                echo '<i class="far fa-star"></i>';
                        }
                        ?>
                    </div>
                    <span class="text-muted">Analysis based on <?php echo $review_count; ?> reviews</span>
                </div>
            </div>
        </div>

        <?php if ($reviews_result->num_rows > 0): ?>
            <div class="row g-4">
                <?php while ($review = $reviews_result->fetch_assoc()): ?>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100 rounded-4">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if (!empty($review['profile_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($review['profile_image']); ?>"
                                                alt="<?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?>"
                                                class="rounded-circle" style="width: 45px; height: 45px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                                style="width: 45px; height: 45px; font-size: 1.2rem;">
                                                <?php echo strtoupper(substr($review['first_name'], 0, 1) . substr($review['last_name'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h6 class="fw-bold mb-0">
                                                <?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?>
                                            </h6>
                                            <small
                                                class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                        </div>
                                    </div>
                                    <div class="text-warning small">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <i class="<?php echo ($i < $review['rating']) ? 'fas' : 'far'; ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="card-text text-secondary" style="line-height: 1.6;">
                                    "<?php echo htmlspecialchars($review['comment']); ?>"</p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="far fa-comments fa-3x text-muted mb-3 opacity-25"></i>
                <p class="text-muted">No reviews yet. Be the first to share your thoughts!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>