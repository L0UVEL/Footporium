<?php
include 'includes/db_connect.php';
include 'includes/functions.php';

// Check kung wala pang session na nag-start, then simulan ito
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kung naka-login na ang user, idirekta agad sila sa homepage
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$error = '';

// Kapag nag-submit ang user ng login form (POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Linisin ang input para iwas sa hacking (Sanitize)
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];

    // Hanapin ang user sa database gamit ang email
    $sql = "SELECT id, first_name, last_name, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Kung may nahanap na user
    if ($result && $result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // I-verify kung tama ang password (compare sa naka-hash sa database)
        if (password_verify($password, $row['password'])) {
            // Login successful! I-save ang user info sa session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['first_name'] . ' ' . $row['last_name'];
            $_SESSION['role'] = $row['role'];

            // Kung admin, sa admin dashboard; kung user, sa homepage
            if ($row['role'] == 'admin') {
                redirect('admin/dashboard.php');
            } else {
                redirect('index.php');
            }
        } else {
            // Mali ang password
            $error = "Invalid password.";
        }
    } else {
        // Walang user na may ganitong email
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
                                <!-- Toggle Password Button (Eye Icon) -->
                                <button class="btn btn-outline-secondary" type="button"
                                    style="cursor: pointer; z-index: 100;" onclick="togglePassword('password', this)"
                                    onmousedown="event.preventDefault();"> <!-- Prevent focus loss sa mobile -->
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

<?php include 'includes/footer.php'; ?>