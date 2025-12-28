<?php
include 'includes/db_connect.php';
include 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$token = $_GET['token'] ?? '';
$error = '';

if (empty($token)) {
    $error = "Invalid or missing token.";
} else {
    // Check if token exists and is valid
    $stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $error = "Invalid reset token.";
    } else {
        $row = $result->fetch_assoc();
        if (strtotime($row['expires_at']) < time()) {
            $error = "This link has expired. Please request a new one.";
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5">
                    <h2 class="text-center fw-bold mb-4">Reset Password</h2>

                    <?php if ($error): ?>
                        <div class="alert alert-danger text-center">
                            <?php echo $error; ?>
                            <br><br>
                            <a href="forgot_password.php" class="btn btn-primary-custom btn-sm">Request New Link</a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center mb-4">Create a new secure password for your account.</p>

                        <?php if (isset($_GET['err'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['err']); ?></div>
                        <?php endif; ?>

                        <form action="actions/update_password.php" method="post">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required
                                        minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}"
                                        title="Must be 8+ chars and contain uppercase, lowercase, number, and special char.">
                                    <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePassword('password', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                                        required>
                                    <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePassword('confirmPassword', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary-custom w-100 py-3 mb-3">Update Password</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>