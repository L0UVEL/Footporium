<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db_connect.php';
include 'includes/functions.php';

// Check kung naka-login ang user. Required ito para maka-checkout.
check_login();

// Redirect sa cart page kapag walang laman ang cart o empty ang session
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    // Walang items, balik sa cart
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// I-calculate ang Total na babayaran base sa laman ng cart (Re-verify price from DB)
$ids = implode(',', array_keys($_SESSION['cart']));
$sql_cart = "SELECT * FROM products WHERE id IN ($ids)";
$result_cart = $conn->query($sql_cart);
$cart_items = [];
$total_price = 0;
if ($result_cart) {
    while ($row = $result_cart->fetch_assoc()) {
        // I-set ang quantity mula sa session
        $row['qty'] = $_SESSION['cart'][$row['id']];
        // Compute subtotal
        $row['subtotal'] = $row['price'] * $row['qty'];
        // Update total
        $total_price += $row['subtotal'];
        $cart_items[] = $row;
    }
}

// Logic moved to actions/place_order_action.php for AJAX handling
// (Ang logic ng pag-place ng order ay nasa ibang file na para malinis at secure via AJAX)

// Kunin ang Address ng User para i-fill sa form (Pre-fill) para di na mag-type si user kung may record na
$sql_addr_fetch = "SELECT * FROM addresses WHERE user_id = ? LIMIT 1";
$stmt = $conn->prepare($sql_addr_fetch);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_addr = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <h2 class="section-title mb-4">Checkout</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="checkout.php" method="post" id="checkoutForm">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-map-marker-alt text-primary me-2"></i> Shipping
                            Address</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" required
                                    value="<?php echo htmlspecialchars($user_addr['country'] ?? 'Philippines'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Province</label>
                                <input type="text" name="province" class="form-control" required
                                    value="<?php echo htmlspecialchars($user_addr['province'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City / Municipality</label>
                                <input type="text" name="city" class="form-control" required
                                    value="<?php echo htmlspecialchars($user_addr['city'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Barangay</label>
                                <input type="text" name="barangay" class="form-control" required
                                    value="<?php echo htmlspecialchars($user_addr['barangay'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Postal Code</label>
                                <input type="text" name="postal_code" class="form-control" required
                                    value="<?php echo htmlspecialchars($user_addr['postal_code'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address Line (Street, Bldg, etc.)</label>
                            <input type="text" name="address" class="form-control" required
                                value="<?php echo htmlspecialchars($user_addr['address_line'] ?? ''); ?>"
                                placeholder="Street, Building, House No.">
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-credit-card text-primary me-2"></i> Payment Method
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod"
                                checked>
                            <label class="form-check-label fw-bold" for="cod">
                                Cash on Delivery (COD)
                            </label>
                            <div class="text-muted small ms-2">Pay when you receive your order.</div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="card"
                                value="credit_card">
                            <label class="form-check-label fw-bold" for="card">
                                Credit/Debit Card (Mockup)
                            </label>
                            <div class="text-muted small ms-2">Fast and secure payment.</div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="gcash" value="gcash">
                            <label class="form-check-label fw-bold" for="gcash">
                                GCash / E-Wallet
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-custom w-100 py-3 text-uppercase letter-spacing-1">
                    Place Order <i class="fas fa-check-circle ms-2"></i>
                </button>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header py-3">
                    <h5 class="mb-0 fw-bold">Order Summary</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-column gap-3 mb-4">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary rounded-pill"><?php echo $item['qty']; ?>x</span>
                                    <span class="text-truncate"
                                        style="max-width: 150px;"><?php echo htmlspecialchars($item['name']); ?></span>
                                </div>
                                <span class="fw-bold">₱<?php echo number_format($item['subtotal'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span>₱<?php echo number_format($total_price, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span class="text-success">Free</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <h5 class="fw-bold mb-0">Total</h5>
                            <h4 class="text-primary fw-bold mb-0">₱<?php echo number_format($total_price, 2); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>