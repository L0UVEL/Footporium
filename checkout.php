<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db_connect.php';
include 'includes/functions.php';

check_login();

// Redirect if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Calculate Total
$ids = implode(',', array_keys($_SESSION['cart']));
$sql_cart = "SELECT * FROM products WHERE id IN ($ids)";
$result_cart = $conn->query($sql_cart);
$cart_items = [];
$total_price = 0;
if ($result_cart) {
    while ($row = $result_cart->fetch_assoc()) {
        $row['qty'] = $_SESSION['cart'][$row['id']];
        $row['subtotal'] = $row['price'] * $row['qty'];
        $total_price += $row['subtotal'];
        $cart_items[] = $row;
    }
}

// Handle Checkout Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address_id = null;
    $address_line = sanitize_input($_POST['address']);
    $country = sanitize_input($_POST['country']);
    $province = sanitize_input($_POST['province']);
    $city = sanitize_input($_POST['city']);
    $barangay = sanitize_input($_POST['barangay']);
    $postal_code = sanitize_input($_POST['postal_code']);
    $payment_method = sanitize_input($_POST['payment_method']);

    // 1. Save/Update Address
    // For simplicity in this mock, we'll insert a new address or use existing logic. 
    // To match strict DB schema, we might need to check if address exists.
    // Let's insert a NEW address entry for this specific order context if needed, 
    // or just update the user's main address. 
    // The DB schema likely has an `addresses` table.
    // Let's check if user has an address record; if so update, else insert.

    $check_addr = "SELECT id FROM addresses WHERE user_id = ?";
    $stmt = $conn->prepare($check_addr);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $addr_row = $res->fetch_assoc();
        $address_id = $addr_row['id'];
        $sql_addr = "UPDATE addresses SET address_line = ?, country=?, province=?, city=?, barangay=?, postal_code = ? WHERE id = ?";
        $stmt_addr = $conn->prepare($sql_addr);
        $stmt_addr->bind_param("ssssssi", $address_line, $country, $province, $city, $barangay, $postal_code, $address_id);
        $stmt_addr->execute();
        $stmt_addr->close();
    } else {
        $sql_addr = "INSERT INTO addresses (user_id, address_line, country, province, city, barangay, postal_code) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_addr = $conn->prepare($sql_addr);
        $stmt_addr->bind_param("issssss", $user_id, $address_line, $country, $province, $city, $barangay, $postal_code);
        $stmt_addr->execute();
        $address_id = $stmt_addr->insert_id;
        $stmt_addr->close();
    }

    // 2. Create Order
    $order_status = 'pending';
    $sql_order = "INSERT INTO orders (user_id, address_id, total_amount, status) VALUES (?, ?, ?, ?)";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("iids", $user_id, $address_id, $total_price, $order_status);

    if ($stmt_order->execute()) {
        $order_id = $stmt_order->insert_id;

        // 3. Insert Order Items
        $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt_item = $conn->prepare($sql_item);

        foreach ($cart_items as $item) {
            $stmt_item->bind_param("iiid", $order_id, $item['id'], $item['qty'], $item['price']);
            $stmt_item->execute();
        }
        $stmt_item->close();

        // 4. Record Payment (Mock)
        // Check if payments table exists, otherwise skip (assuming user wants simple structure)
        // Standard schema usually has payments. Let's try basic insert.
        $payment_status = 'pending'; // Mock payment usually pending until "verified" or completed immediately if 'credit card'
        $sql_pay = "INSERT INTO payments (order_id, payment_method, amount, status) VALUES (?, ?, ?, ?)";
        $stmt_pay = $conn->prepare($sql_pay);
        // If SQL error occurs here (table missing), we handle it gracefully or ignore
        if ($stmt_pay) {
            $stmt_pay->bind_param("isds", $order_id, $payment_method, $total_price, $payment_status);
            $stmt_pay->execute();
            $stmt_pay->close();
        }

        // 5. Clear Cart
        unset($_SESSION['cart']);

        // 6. Redirect to Success
        header("Location: success.php?order_id=" . $order_id);
        exit;

    } else {
        $error = "Error creating order: " . $conn->error;
    }
    $stmt_order->close();
}

// Fetch User Address for Form Pre-fill
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