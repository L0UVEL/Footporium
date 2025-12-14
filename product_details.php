<?php
include 'includes/db_connect.php';
include 'includes/header.php';

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
} else {
    echo "<div class='container my-5 text-center'><h1>Product not found</h1><a href='products.php' class='btn btn-primary-custom'>Back to Products</a></div>";
    include 'includes/footer.php';
    exit;
}

// Fetch Reviews
$review_sql = "SELECT r.*, u.full_name, u.profile_image FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC";
$review_stmt = $conn->prepare($review_sql);
$review_stmt->bind_param("i", $product_id);
$review_stmt->execute();
$reviews_result = $review_stmt->get_result();
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
            <div class="product-detail-img-wrapper rounded-4 shadow-sm position-relative overflow-hidden bg-white"
                style="height: 500px;">
                <img src="data:image/png;base64,<?php echo base64_encode($product['image_data']); ?>"
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

            <form action="actions/add_to_cart_action.php" method="POST" class="mb-4">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <div class="row g-3">
                    <div class="col-sm-4">
                        <div
                            class="quantity-control d-flex align-items-center justify-content-between border rounded-pill px-3 py-2 bg-white">
                            <button type="button" class="btn btn-link text-dark p-0" onclick="updateQty(-1)"><i
                                    class="fas fa-minus"></i></button>
                            <input type="number" name="quantity" id="quantity" value="1" min="1"
                                class="form-control border-0 text-center bg-transparent p-0 fw-bold"
                                style="width: 40px;">
                            <button type="button" class="btn btn-link text-dark p-0" onclick="updateQty(1)"><i
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

            <div class="d-flex gap-4 p-3 bg-light rounded-3 mt-auto">
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
                                <img src="data:image/png;base64,<?php echo base64_encode($rel['image_data']); ?>"
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
<section class="bg-light py-5">
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
                                            <img src="data:image/png;base64,<?php echo base64_encode($review['profile_image']); ?>"
                                                alt="<?php echo htmlspecialchars($review['full_name']); ?>" class="rounded-circle"
                                                style="width: 45px; height: 45px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                                style="width: 45px; height: 45px; font-size: 1.2rem;">
                                                <?php echo strtoupper(substr($review['full_name'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($review['full_name']); ?></h6>
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

<script>
    function updateQty(change) {
        const input = document.getElementById('quantity');
        let newVal = parseInt(input.value) + change;
        if (newVal < 1) newVal = 1;
        input.value = newVal;
    }
</script>

<?php include 'includes/footer.php'; ?>