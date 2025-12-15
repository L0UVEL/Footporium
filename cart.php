<?php
include 'includes/db_connect.php';
include 'includes/header.php';
?>
<!-- Cart Section -->
<div class="container my-5">
    <h2 class="section-title text-center mb-5">Your Shopping Cart</h2>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="cart-container">
                <?php
                $total_price = 0;

                if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                    $ids = array_keys($_SESSION['cart']);

                    // Prepared statement for variable number of IDs
                    $types = str_repeat('i', count($ids));
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));

                    $sql = "SELECT * FROM products WHERE id IN ($placeholders)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param($types, ...$ids);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $id = $row['id'];
                            $qty = $_SESSION['cart'][$id];
                            $subtotal = $row['price'] * $qty;
                            $total_price += $subtotal;
                            ?>
                            <!-- Cart Item -->
                            <div class="cart-item d-flex align-items-center justify-content-between flex-wrap gap-3"
                                data-id="<?php echo $id; ?>">
                                <div class="d-flex align-items-center gap-3">
                                    <?php
                                    $imgSrc = !empty($row['image_url']) ? $row['image_url'] : 'assets/img/placeholder.png';
                                    ?>
                                    <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                                        alt="<?php echo htmlspecialchars($row['name']); ?>" class="cart-img">
                                    <div>
                                        <h5 class="mb-1"><?php echo htmlspecialchars($row['name']); ?></h5>
                                        <span class="text-primary fw-bold">₱<?php echo number_format($row['price'], 2); ?></span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-4">
                                    <div class="quantity-control">
                                        <button class="btn btn-sm btn-outline-secondary rounded-circle cart-update-btn"
                                            data-action="decrease"><i class="fas fa-minus"></i></button>
                                        <input type="text" value="<?php echo $qty; ?>" class="quantity-input" readonly>
                                        <button class="btn btn-sm btn-outline-secondary rounded-circle cart-update-btn"
                                            data-action="increase"><i class="fas fa-plus"></i></button>
                                    </div>
                                    <button class="btn btn-outline-danger btn-sm rounded-pill px-3 cart-remove-btn">
                                        <i class="fas fa-trash-alt me-1"></i> Remove
                                    </button>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<p class='text-center text-muted'>Items not found in database.</p>";
                    }
                } else {
                    echo "<p class='text-center text-muted'>Your cart is empty.</p>";
                }
                ?>

                <!-- Cart Summary -->
                <div class="mt-4 pt-4" style="border-top: 1px solid var(--border-color)">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Subtotal</h4>
                        <h3 class="text-primary fw-bold mb-0" id="cart-total">
                            ₱<?php echo number_format($total_price, 2); ?></h3>
                    </div>
                    <?php if ($total_price > 0): ?>
                        <a href="checkout.php"
                            class="btn btn-primary-custom w-100 py-3 text-uppercase letter-spacing-1 text-decoration-none">
                            Proceed to Checkout <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>