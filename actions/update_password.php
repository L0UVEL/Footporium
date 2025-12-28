<?php
include '../includes/db_connect.php';
include '../includes/functions.php'; // For sanitize_input if needed

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // 1. Basic Validation
    if ($password !== $confirmPassword) {
        header("Location: ../reset_password.php?token=$token&err=Passwords do not match");
        exit();
    }

    // Validate password strength again (optional but good)
    if (
        strlen($password) < 8 ||
        !preg_match("/[A-Z]/", $password) ||
        !preg_match("/[a-z]/", $password) ||
        !preg_match("/[0-9]/", $password) ||
        !preg_match("/[\W_]/", $password)
    ) {
        header("Location: ../reset_password.php?token=$token&err=Password is too weak");
        exit();
    }

    // 2. Validate Token again
    $stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: ../forgot_password.php?error=Token invalid or expired");
        exit();
    }

    $row = $result->fetch_assoc();
    $email = $row['email'];

    // Check expiry logic in PHP to avoid Timezone mismatch with DB
    if (strtotime($row['expires_at']) < time()) {
        header("Location: ../forgot_password.php?error=Token expired. Please request a new one.");
        exit();
    }

    // 3. Update User Password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $update_stmt->bind_param("ss", $hashed_password, $email);

    if ($update_stmt->execute()) {
        // 4. Delete Token (so it can't be used again)
        $del_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $del_stmt->bind_param("s", $email);
        $del_stmt->execute();

        header("Location: ../login.php?success=Password updated successfully due to reset. Please login.");
    } else {
        header("Location: ../reset_password.php?token=$token&err=Database error occurred");
    }

    $conn->close();
} else {
    header("Location: ../index.php");
}
?>