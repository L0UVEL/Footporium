<?php
// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include '../includes/db_connect.php';
include '../includes/functions.php';

// Load PHPMailer files
// NOTE: Files are directly in PHPMailer folder
require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../forgot_password.php?error=Invalid email address");
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $del_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $del_stmt->bind_param("s", $email);
        $del_stmt->execute();

        $ins_stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $ins_stmt->bind_param("sss", $email, $token, $expires_at);
        $ins_stmt->execute();

        // Prepare Reset Link
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname(dirname($_SERVER['PHP_SELF']));
        $link = "$protocol://$host" . rtrim($path, '/\\') . "/reset_password.php?token=$token";

        // Initialize PHPMailer
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'footporiumbussines@gmail.com';
            $mail->Password = 'klsk jqml eghw nxdc';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            //Recipients
            $mail->setFrom('footporiumbussines@gmail.com', 'Footporium Security');
            $mail->addAddress($email);

            //Content
            $mail->isHTML(true);
            $mail->Subject = 'Reset your Password';
            $mail->Body = "
                <h3>Password Reset Request</h3>
                <p>We received a request to reset your password.</p>
                <p>Click the link below to set a new password:</p>
                <p><a href='$link' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a></p>
                <p>Or copy this link: $link</p>
                <p><small>This link expires in 1 hour.</small></p>
            ";
            $mail->AltBody = "Reset your password by visiting this link: $link";

            $mail->send();
            header("Location: ../forgot_password.php?success=Password reset link sent to your email.");
        } catch (Exception $e) {
            // Log error
            $log_file = '../php_errors.log';
            $log_message = "[" . date('Y-m-d H:i:s') . "] Mailer Error: {$mail->ErrorInfo}" . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND);

            // Fallback for debugging
            $log_link_file = '../reset_links.log';
            $log_link_msg = "[" . date('Y-m-d H:i:s') . "] (Fallback) To: $email | Link: $link" . PHP_EOL;
            file_put_contents($log_link_file, $log_link_msg, FILE_APPEND);

            header("Location: ../forgot_password.php?error=Message could not be sent. Check logs or verify PHPMailer is installed. (Fallback link saved locally)");
        }
    } else {
        header("Location: ../forgot_password.php?success=If registered, a reset link has been sent.");
    }

    $stmt->close();
    $conn->close();
} else {
    redirect('../forgot_password.php');
}
?>