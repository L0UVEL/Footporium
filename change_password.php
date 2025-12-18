<?php
include 'includes/db_connect.php';
include 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Siguraduhing naka-login ang user bago pumasok dito
check_login();

$error = '';
$success = '';
$user_id = $_SESSION['user_id'];

// Kapag nag-submit ng change password form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Kunin ang current password hash mula sa database
    $sql = "SELECT password FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        // I-verify kung tama ang current password na ininput
        if (password_verify($current_password, $hashed_password)) {
            // Validation para sa bagong password (gaya sa registration)
            if (strlen($new_password) < 8) {
                $error = "Password must be at least 8 characters long.";
            } elseif (!preg_match("/[A-Z]/", $new_password)) {
                $error = "Password must contain at least one uppercase letter.";
            } elseif (!preg_match("/[a-z]/", $new_password)) {
                $error = "Password must contain at least one lowercase letter.";
            } elseif (!preg_match("/[0-9]/", $new_password)) {
                $error = "Password must contain at least one number.";
            } elseif (!preg_match("/[\W_]/", $new_password)) {
                $error = "Password must contain at least one special character.";
            } elseif ($new_password !== $confirm_password) {
                $error = "New passwords do not match.";
            } else {
                // Update ang password sa database gamit ang bagong hash
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_sql = "UPDATE users SET password = ? WHERE id = ?";
                if ($update_stmt = $conn->prepare($update_sql)) {
                    $update_stmt->bind_param("si", $new_hashed_password, $user_id);
                    if ($update_stmt->execute()) {
                        $success = "Password updated successfully.";
                    } else {
                        $error = "Error updating password: " . $conn->error;
                    }
                    $update_stmt->close();
                }
            }
        } else {
            $error = "Incorrect current password.";
        }
    } else {
        $error = "Error fetching user data.";
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5">
                    <h3 class="text-center fw-bold mb-4">Change Password</h3>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <form action="change_password.php" method="post">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password"
                                    name="current_password" required>
                                <!-- Toggle Button -->
                                <button class="btn btn-outline-secondary" type="button"
                                    style="cursor: pointer; z-index: 100;"
                                    onclick="togglePassword('current_password', this)"
                                    onmousedown="event.preventDefault();">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password"
                                    minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}"
                                    title="Must contain at least one number, one uppercase and lowercase letter, one special character, and at least 8 or more characters"
                                    required>
                                <button class="btn btn-outline-secondary" type="button"
                                    style="cursor: pointer; z-index: 100;"
                                    onclick="togglePassword('new_password', this)"
                                    onmousedown="event.preventDefault();">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted" style="font-size: 0.8rem;">
                                Must contain 8+ chars, uppercase, lowercase, number, and special char.
                            </small>
                        </div>
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password"
                                    name="confirm_password" minlength="8" required>
                                <button class="btn btn-outline-secondary" type="button"
                                    style="cursor: pointer; z-index: 100;"
                                    onclick="togglePassword('confirm_password', this)"
                                    onmousedown="event.preventDefault();">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary-custom w-100 py-3 mb-3">Update Password</button>
                        <div class="text-center">
                            <a href="profile.php" class="text-decoration-none text-muted">Back to Profile</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>