<?php
include 'includes/db_connect.php';
include 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

check_login();

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = sanitize_input($_POST['fullName']);
    $phone = sanitize_input($_POST['phone']);
    $address_line = sanitize_input($_POST['address']);
    
    // Update User Info
    $sql_user = "UPDATE users SET full_name = ?, phone = ? WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("ssi", $full_name, $phone, $user_id);

    if ($stmt_user->execute()) {
        $_SESSION['user_name'] = $full_name; // Update session name
        
        // Update or Insert Address
        // Check if address exists
        $check_addr = "SELECT id FROM addresses WHERE user_id = ?";
        $stmt_check = $conn->prepare($check_addr);
        $stmt_check->bind_param("i", $user_id);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            $sql_addr = "UPDATE addresses SET address_line = ? WHERE user_id = ?";
            $stmt_addr = $conn->prepare($sql_addr);
            $stmt_addr->bind_param("si", $address_line, $user_id);
        } else {
            $sql_addr = "INSERT INTO addresses (user_id, address_line) VALUES (?, ?)";
            $stmt_addr = $conn->prepare($sql_addr);
            $stmt_addr->bind_param("is", $user_id, $address_line);
        }

        if ($stmt_addr->execute()) {
            $message = "Profile updated successfully!";
        } else {
            $error = "Error updating address: " . $conn->error;
        }
        $stmt_addr->close();
        $stmt_check->close();

    } else {
        $error = "Error updating profile: " . $conn->error;
    }
    $stmt_user->close();
}

// Fetch User Data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch Address Data
$sql_addr = "SELECT * FROM addresses WHERE user_id = ? LIMIT 1";
$stmt_addr = $conn->prepare($sql_addr);
$stmt_addr->bind_param("i", $user_id);
$stmt_addr->execute();
$res_addr = $stmt_addr->get_result();
$address = $res_addr->fetch_assoc();
$stmt_addr->close();
?>
<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-5">
                    <div class="text-center mb-5">
                        <div class="position-relative d-inline-block">
                            <!-- Placeholder Profile Image -->
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=800000&color=fff&size=150" alt="Profile Picture" class="rounded-circle shadow-sm" width="150" height="150">
                        </div>
                        <h2 class="mt-3 fw-bold"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                        <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                        <span class="badge bg-secondary"><?php echo ucfirst($user['role']); ?> Account</span>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form action="profile.php" method="post">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="fullName" class="form-label fw-bold">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="fullName" name="fullName" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                </div>
                                <small class="text-muted">Email cannot be changed.</small>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-bold">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone text-muted"></i></span>
                                    <input type="tel" class="form-control border-start-0 ps-0" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Enter phone number">
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label fw-bold">Shipping Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                    <textarea class="form-control border-start-0 ps-0" id="address" name="address" rows="3" placeholder="Enter your address"><?php echo htmlspecialchars($address['address_line'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary-custom w-100 py-3">
                                    <i class="fas fa-save me-2"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
