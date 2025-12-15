<?php
// Prevent multiple connections
if (isset($conn) && $conn instanceof mysqli) {
    return;
}

$servername = "localhost"; // Revert to standard localhost
$username = "root";
$password = "";
$dbname = "db_footporium";

// Enable error reporting for debugging (turn off in production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4"); // Ensure proper encoding
} catch (mysqli_sql_exception $e) {
    // Show exact error for debugging
    die("Database Connection Failed: " . $e->getMessage() . " (Host: $servername)");
}
?>