<?php
session_start();
// Alisin lahat ng session variables para mawala ang user data
session_unset();

// Delete the session cookie (Para siguradong logged out na talaga)
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

// I-destroy ang session sa server side
session_destroy();
// I-redirect ang user pabalik sa homepage (index.php)
header("Location: index.php");
exit();
?>