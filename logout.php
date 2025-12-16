<?php
session_start();
// Alisin lahat ng session variables
session_unset();

// Delete the session cookie (Para siguradong logged out)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// I-destroy ang session sa server
session_destroy();
// Redirect pabalik sa homepage
header("Location: index.php");
exit();
?>