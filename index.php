<?php
include 'includes/db_connect.php';
include 'includes/header.php';
?>

<!-- Hero Section -->
<!-- Hero Carousel -->
<div id="heroCarousel" class="carousel slide mb-5" data-bs-ride="carousel" data-bs-interval="5000">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
    </div>
    <div class="carousel-inner rounded-4 rounded-top-0 shadow-lg">
        <div class="carousel-item active">
            <div class="d-flex align-items-center justify-content-center glass-carousel hero-height rounded-top-0">
                <div class="text-center">
                    <h1 class="display-3 fw-bold mb-3">We Bring "FOOT" for Everybody</h1>
                    <p class="lead mb-4">Discover the most unique and premium collection of feet.</p>
                    <img src="assets/img/human.png" alt="Human Foot" style="height: 200px; object-fit: contain;"
                        class="mb-4 drop-shadow">
                    <br>
                    <a href="products.php" class="btn btn-primary-custom btn-lg px-5">Shop Collection</a>
                </div>
            </div>
        </div>
        <div class="carousel-item">
            <div class="d-flex align-items-center justify-content-center glass-carousel hero-height rounded-top-0">
                <div class="text-center">
                    <h1 class="display-3 fw-bold mb-3">Steps Ahead of the Rest</h1>
                    <p class="lead mb-4">Upgrade your walk with our Prosthetic selection.</p>
                    <img src="assets/img/prosthetic.png" alt="Prosthetic Foot"
                        style="height: 200px; object-fit: contain;" class="mb-4 drop-shadow">
                    <br>
                    <a href="products.php" class="btn btn-primary-custom btn-lg px-5">View Prosthetics</a>
                </div>
            </div>
        </div>
        <div class="carousel-item">
            <div class="d-flex align-items-center justify-content-center glass-carousel hero-height rounded-top-0">
                <div class="text-center">
                    <h1 class="display-3 fw-bold mb-3">Legendary Finds</h1>
                    <p class="lead mb-4">Rare collectibles like the Bigfoot Foot.</p>
                    <img src="assets/img/bigfoot.png" alt="Bigfoot" style="height: 200px; object-fit: contain;"
                        class="mb-4 drop-shadow">
                    <br>
                    <a href="products.php" class="btn btn-primary-custom btn-lg px-5">Shop Exclusives</a>
                </div>
            </div>
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon bg-dark rounded-circle" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>

<!-- Best Sellers -->
<div class="container mb-5 reveal">
    <div class="text-center mb-5">
        <h2 class="section-title">Best Sellers</h2>
        <p class="text-muted">Our most popular picks this week</p>
    </div>

    <div class="row g-4">
        <?php
        // Fetch 4 products for "Best Sellers"
        // Fetch 4 products for "Best Sellers"
        $sql = "SELECT * FROM products ORDER BY RAND() LIMIT 4";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="product-card">
                        <div class="product-img-wrapper">
                            <a href="product_details.php?id=<?php echo $row['id']; ?>">
                                <img src="data:image/png;base64,<?php echo base64_encode($row['image_data']); ?>"
                                    alt="<?php echo htmlspecialchars($row["name"]); ?>">
                            </a>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="product_details.php?id=<?php echo $row['id']; ?>"
                                    class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($row["name"]); ?>
                                </a>
                            </h5>
                            <span class="price">₱<?php echo number_format($row["price"], 2); ?></span>
                            <form action="actions/add_to_cart_action.php" method="POST" class="d-grid mt-2">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-primary-custom add-to-cart-btn">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div class="col-12 text-center"><p>No products found yet.</p></div>';
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>