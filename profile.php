<?php
include 'includes/db_connect.php';
include 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

check_login();

// Optimization: Removed auto-migration logic. Ensure DB schema is up to date manually.

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Initialize variables for dynamic SQL construction
    $update_fields = [];
    $params = [];
    $types = "";

    // 2. Check for Text Fields (Using explicit flag check)
    if (isset($_POST['is_edit_mode'])) {
        $full_name = sanitize_input($_POST['fullName']);
        $phone = sanitize_input($_POST['phone']);

        $update_fields[] = "full_name = ?";
        $params[] = $full_name;
        $types .= "s";

        $update_fields[] = "phone = ?";
        $params[] = $phone;
        $types .= "s";

        // Address Fields
        $country = sanitize_input($_POST['country']);
        $province = sanitize_input($_POST['province']);
        $city = sanitize_input($_POST['city']);
        $barangay = sanitize_input($_POST['barangay']);
        $street = sanitize_input($_POST['street']);
        $postal_code = sanitize_input($_POST['postal_code']);
    }

    // 3. Check for Image Upload
    $imgContent = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $imgContent = file_get_contents($_FILES['profile_image']['tmp_name']);
        $update_fields[] = "profile_image = ?";
        $params[] = null; // Placeholder for blob
        $types .= "b";
    }

    // 4. Construct Query if there is something to update
    if (!empty($update_fields)) {
        $sql_user = "UPDATE users SET " . implode(", ", $update_fields) . " WHERE id = ?";
        $types .= "i";
        $params[] = $user_id;

        $stmt_user = $conn->prepare($sql_user);

        // Bind parameters dynamically
        $stmt_user->bind_param($types, ...$params);

        // Send Blob data if image exists
        if ($imgContent !== null) {
            // Find the index of the blob parameter. 
            // It's the index where 'profile_image = ?' was added.
            // If text fields exist, image corresponds to specific param index.
            // Simplified: If image is present, it's the LAST param before WHERE ID.
            // Logic:
            // Text fields count: $text_count = isset($_POST['is_edit_mode']) ? 2 : 0;
            // Blob index (0-based) would be $text_count.

            $blob_param_index = isset($_POST['is_edit_mode']) ? 2 : 0;
            $stmt_user->send_long_data($blob_param_index, $imgContent);
        }

        if ($stmt_user->execute()) {
            if (isset($full_name)) {
                $_SESSION['user_name'] = $full_name;
            }
            $message = "Profile updated successfully!";

            // 5. Update Address ONLY if text fields were present
            if (isset($_POST['is_edit_mode'])) {
                // Check if address exists
                $check_addr = "SELECT id FROM addresses WHERE user_id = ?";
                $stmt_check = $conn->prepare($check_addr);
                $stmt_check->bind_param("i", $user_id);
                $stmt_check->execute();
                $res_check = $stmt_check->get_result();

                if ($res_check->num_rows > 0) {
                    $sql_addr = "UPDATE addresses SET country=?, province=?, city=?, barangay=?, address_line=?, postal_code=? WHERE user_id = ?";
                    $stmt_addr = $conn->prepare($sql_addr);
                    $stmt_addr->bind_param("ssssssi", $country, $province, $city, $barangay, $street, $postal_code, $user_id);
                } else {
                    $sql_addr = "INSERT INTO addresses (user_id, country, province, city, barangay, address_line, postal_code) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt_addr = $conn->prepare($sql_addr);
                    $stmt_addr->bind_param("issssss", $user_id, $country, $province, $city, $barangay, $street, $postal_code);
                }

                if (!$stmt_addr->execute()) {
                    $error = "Error updating address: " . $conn->error;
                }
                $stmt_addr->close();
                $stmt_check->close();
            }

        } else {
            $error = "Error updating profile: " . $conn->error;
        }
        $stmt_user->close();
    }
}

// ... (Fetch logic remains)
// Kailangan i-fetch ulit ang user para makuha ang bagong image
// Kailangan i-fetch ulit ang user para makuha ang bagong image
$sql = "SELECT * FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
} else {
    die("Error preparing user fetch: " . $conn->error);
}

// Kunin ang Address Data mula sa database
$sql_addr = "SELECT * FROM addresses WHERE user_id = ? LIMIT 1";
if ($stmt_addr = $conn->prepare($sql_addr)) {
    $stmt_addr->bind_param("i", $user_id);
    $stmt_addr->execute();
    $res_addr = $stmt_addr->get_result();
    $address = $res_addr->fetch_assoc();
    $stmt_addr->close();
} else {
    // Optional: die or just ignore address error
    $address = []; // fallback
}
?>
<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row">
        <!-- Sidebar Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden text-center p-4">
                <div class="card-body">
                    <div class="position-relative d-inline-block mb-2">
                        <!-- Profile Image ni User -->
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="data:image/png;base64,<?php echo base64_encode($user['profile_image']); ?>"
                                alt="Profile Picture" class="rounded-circle shadow-sm object-fit-cover" width="150"
                                height="150">
                        <?php else: ?>
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=800000&color=fff&size=150"
                                alt="Profile Picture" class="rounded-circle shadow-sm" width="150" height="150">
                        <?php endif; ?>

                        <!-- Button na pang-upload ng picture (Yung camera icon) -->
                        <label for="profile_image_trigger"
                            class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle shadow p-2"
                            style="cursor: pointer; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-camera"></i>
                        </label>
                    </div>

                    <h3 class="fw-bold mb-0"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                    <p class="text-muted small mb-1"><?php echo htmlspecialchars($user['email']); ?></p>

                    <div class="mb-3">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                            <?php echo $user['role'] === 'user' ? 'Member' : ucfirst($user['role']) . ' Member'; ?>
                        </span>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" id="edit-profile-btn"
                            class="btn btn-outline-primary rounded-pill py-2 fw-bold">
                            Edit Profile
                        </button>
                        <a href="logout.php" class="btn btn-outline-danger rounded-pill py-2 fw-bold">
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content (Yung nasa kanan) -->
        <div class="col-lg-8">
            <form id="profileForm" action="profile.php" method="post" enctype="multipart/form-data">
                <!-- NEW Explicit Edit Mode Flag (Disabled by default, enabled by JS) -->
                <input type="hidden" name="is_edit_mode" id="is_edit_mode" value="1" disabled>

                <!-- Nakatagong file input na tine-trigger ng camera icon sa sidebar -->
                <input type="file" id="profile_image_trigger" name="profile_image" class="d-none" accept="image/*"
                    onchange="this.form.submit()">

                <!-- My Orders Section (Mga order ko) -->
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                    <div class="card-header border-0 pt-4 px-4 pb-0">
                        <h4 class="fw-bold mb-0">My Orders</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php
                        // Kunin ang specific orders para sa profile dashboard
                        // Ginaya natin yung logic dito para sa standard View
                        $order_sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
                        $orders_result = false; // Default safe value
                        
                        if ($stmt_orders = $conn->prepare($order_sql)) {
                            $stmt_orders->bind_param("i", $user_id);
                            $stmt_orders->execute();
                            $orders_result = $stmt_orders->get_result();
                        } else {
                            // If failed, just show empty orders or error message (avoid crash)
                            echo '<div class="alert alert-warning">Could not fetch orders.</div>';
                        }
                        ?>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="text-muted fw-light">
                                    <tr>
                                        <th class="border-0">Order ID</th>
                                        <th class="border-0">Date</th>
                                        <th class="border-0">Status</th>
                                        <th class="border-0">Total</th>
                                        <th class="border-0 text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                                            <tr>
                                                <td class="fw-bold">#FP-<?php echo $order['id']; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                                <td>
                                                    <?php
                                                    $status = $order['status'];
                                                    $badgeClass = match ($status) {
                                                        'completed', 'delivered' => 'bg-success text-success',
                                                        'shipped' => 'bg-info text-info',
                                                        'processing' => 'bg-primary text-primary',
                                                        'cancelled' => 'bg-danger text-danger',
                                                        default => 'bg-warning text-warning'
                                                    };
                                                    ?>
                                                    <span
                                                        class="badge <?php echo $badgeClass; ?> bg-opacity-10 px-3 rounded-pill text-uppercase"
                                                        style="font-size: 0.75rem;">
                                                        <?php echo $status; ?>
                                                    </span>
                                                </td>
                                                <td class="fw-bold">₱<?php echo number_format($order['total_amount'], 2); ?>
                                                </td>
                                                <td class="text-end">
                                                    <a href="order_details.php?id=<?php echo $order['id']; ?>"
                                                        class="text-decoration-none fw-bold text-primary"
                                                        style="font-size: 0.9rem;">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No recent orders found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if ($orders_result->num_rows > 0): ?>
                            <div class="mt-3 text-center">
                                <!-- Link to full history if desired, or just keep it simple -->
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Account Settings Section -->
                <div id="account-settings" class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header border-0 pt-4 px-4 pb-0">
                        <h4 class="fw-bold mb-0">Account Settings</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($message): ?>
                            <div class="alert alert-success rounded-3 mb-4"><?php echo $message; ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger rounded-3 mb-4"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <div class="row g-4">
                            <!-- Full Name (Editable) -->
                            <div class="col-12">
                                <label for="fullName" class="form-label text-muted small fw-bold">Full Name</label>
                                <input type="text" class="form-control" id="fullName" name="fullName"
                                    value="<?php echo htmlspecialchars($user['full_name']); ?>" placeholder="Full Name"
                                    disabled>
                            </div>

                            <!-- Phone Number -->
                            <div class="col-md-6">
                                <label for="phone" class="form-label text-muted small fw-bold">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                    placeholder="+63 912 345 6789" disabled>
                            </div>

                            <!-- Address Dissection -->
                            <div class="col-12">
                                <h6 class="fw-bold text-primary mb-3"><i class="fas fa-map-marker-alt me-2"></i>Delivery
                                    Address</h6>
                            </div>

                            <!-- Country & Province -->
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Country</label>
                                <input type="text" class="form-control" name="country"
                                    value="<?php echo htmlspecialchars($address['country'] ?? 'Philippines'); ?>"
                                    disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Province</label>
                                <input type="text" class="form-control" name="province"
                                    value="<?php echo htmlspecialchars($address['province'] ?? ''); ?>"
                                    placeholder="Province" disabled>
                            </div>

                            <!-- City & Barangay -->
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">City / Municipality</label>
                                <input type="text" class="form-control" name="city"
                                    value="<?php echo htmlspecialchars($address['city'] ?? ''); ?>" placeholder="City"
                                    disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Barangay</label>
                                <input type="text" class="form-control" name="barangay"
                                    value="<?php echo htmlspecialchars($address['barangay'] ?? ''); ?>"
                                    placeholder="Barangay" disabled>
                            </div>

                            <!-- Postal Code -->
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Postal Code</label>
                                <input type="text" class="form-control" name="postal_code"
                                    value="<?php echo htmlspecialchars($address['postal_code'] ?? ''); ?>"
                                    placeholder="Postal Code" disabled>
                            </div>



                            <!-- Street -->
                            <div class="col-12">
                                <label class="form-label text-muted small fw-bold">Street Name, Bldg, House No.</label>
                                <input type="text" class="form-control" name="street"
                                    value="<?php echo htmlspecialchars($address['address_line'] ?? ''); ?>"
                                    placeholder="Street Name, Building, House No." disabled>
                            </div>

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

</div>



<?php include 'includes/footer.php'; ?>