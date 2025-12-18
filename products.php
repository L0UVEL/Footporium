<?php
include 'includes/db_connect.php';
include 'includes/header.php';
?>

<!-- Header -->
<div class="container py-5 mb-5">
    <div class="glass-carousel p-5 text-center">
        <h1 class="display-5 fw-bold">Our Collection</h1>
        <p class="text-muted">Explore our wide range of premium feet.</p>
    </div>
</div>

<!-- Products Grid -->
<div class="container mb-5 reveal">
    <div class="row g-4">
        <?php
        // Kunin lahat ng products mula sa database
        $sql = "SELECT * FROM products";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            // I-loop ang bawat product para ipakita sila isa-isa sa grid
            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="product-card">
                        <div class="product-img-wrapper">
                            <!-- Link papunta sa product details page -->
                            <a href="product_details.php?id=<?php echo $row['id']; ?>">
                                <?php
                                // Check kung may image, kung wala, gumamit ng placeholder
                                $imgSrc = !empty($row['image_url']) ? $row['image_url'] : 'assets/img/placeholder.png';
                                ?>
                                <img src="<?php echo htmlspecialchars($imgSrc); ?>"
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
                            <!-- Presyo ng product -->
                            <span class="price">₱<?php echo number_format($row["price"], 2); ?></span>

                            <!-- Add to Cart button (nakadepende kung naka-login o hindi) -->
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form action="actions/add_to_cart_action.php" method="POST" class="d-grid mt-2">
                                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary-custom add-to-cart-btn">Add to Cart</button>
                                </form>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-outline-primary btn-sm d-grid mt-2">Login to Add</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            // Kung may error sa pag-query o walang nakuha
            if (!$result) {
                echo "<div class='col-12 text-center text-danger'>Database Error: " . $conn->error . "<br>Did you import the SQL file?</div>";
            } else {
                // Pag walang laman ang table
                echo "<p class='text-center'>No products found.</p>";
            }
        }
        // Isara ang connection sa database
        $conn->close();
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>