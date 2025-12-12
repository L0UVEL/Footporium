<?php
include 'includes/db_connect.php';
include 'includes/header.php';
?>

    <!-- Header -->
    <div class="bg-light py-5 mb-5">
        <div class="container text-center">
            <h1 class="display-5 fw-bold">Our Collection</h1>
            <p class="text-muted">Explore our wide range of premium feet.</p>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="container mb-5">
        <div class="row g-4">
            <?php
            $sql = "SELECT * FROM products";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                // Output data of each row
                while($row = $result->fetch_assoc()) {
                    ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="product-card">
                            <div class="product-img-wrapper">
                                <img src="<?php echo htmlspecialchars($row["image_url"]); ?>" alt="<?php echo htmlspecialchars($row["name"]); ?>">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row["name"]); ?></h5>
                                <span class="price">₱<?php echo number_format($row["price"], 2); ?></span>
                                <button class="btn btn-primary-custom mt-2 add-to-cart-btn">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                if (!$result) {
                    echo "<div class='col-12 text-center text-danger'>Database Error: " . $conn->error . "<br>Did you import the SQL file?</div>";
                } else {
                    echo "<p class='text-center'>No products found.</p>";
                }
            }
            $conn->close();
            ?>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
