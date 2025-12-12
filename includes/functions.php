<?php
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

function check_admin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        redirect('../index.php');
    }
}
?>
