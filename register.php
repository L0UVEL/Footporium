<?php
include 'includes/db_connect.php';
include 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = sanitize_input($_POST['fullName']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirmPassword'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email exists
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result && $check_result->num_rows > 0) {
            $error = "Email already registered.";
        } else if (!$check_result) {
            $error = "Database Error: " . $conn->error;
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user';

            $sql = "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $full_name, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5">
                    <h2 class="text-center fw-bold mb-4">Create Account</h2>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <form action="register.php" method="post">
                        <div class="mb-3">
                            <label for="fullName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullName" name="fullName" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-4">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                        </div>
                        <button type="submit" class="btn btn-primary-custom w-100 py-3 mb-3">Register</button>
                        <p class="text-center mb-0">Already have an account? <a href="login.php" class="text-primary fw-bold">Login here</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
