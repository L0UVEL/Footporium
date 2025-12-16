<?php
include 'includes/db_connect.php';
include 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    // Simulan ang session para magamit ang global $_SESSION variable
    session_start();
}

if (isset($_SESSION['user_id'])) {
    // Kung naka-login na ang user, redirect agad sa homepage
    redirect('index.php');
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input para iwas SQL injection
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];

    // Query para hanapin ang user gamit ang email
    $sql = "SELECT id, first_name, last_name, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $row = $result->fetch_assoc();
        // I-verify kung tama ang password gamit ang password_verify (dahil naka-hash ito)
        if (password_verify($password, $row['password'])) {
            // Set session variables pagkatapos ng successful login
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['first_name'] . ' ' . $row['last_name'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'admin') {
                redirect('admin/dashboard.php');
            } else {
                redirect('index.php');
            }
        } else {
            $error = "Invalid password.";
        }
    } else {
        if (!$result) {
            $error = "Database Error: " . $conn->error;
        } else {
            $error = "No user found with that email.";
        }
    }
    $stmt->close();
}
?>
<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5">
                    <h2 class="text-center fw-bold mb-4">Welcome Back</h2>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form action="login.php" method="post">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePassword('password', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary-custom w-100 py-3 mb-3">Login</button>
                        <p class="text-center mb-0">Don't have an account? <a href="register.php"
                                class="text-primary fw-bold">Register here</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');

        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
</div>
</div>
</div>
</div>
</div>

<?php include 'includes/footer.php'; ?>