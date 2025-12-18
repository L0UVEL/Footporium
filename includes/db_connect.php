<?php
// Iwasan ang paulit-ulit na connection (Check kung connected na)
// Para hindi mag-error kung na-include nang dalawang beses ang file na 'to
if (isset($conn) && $conn instanceof mysqli) {
    return;
}

// Database configuration settings
// IMPORTANT: Update these credentials kapag ilalagay na sa live server (Production)
$servername = "localhost"; // Local host server
$username = "root";        // Default username sa XAMPP
$password = "";            // Default password (walang laman sa XAMPP)
$dbname = "db_footporium"; // Pangalan ng database natin

// I-on ang error reporting para madaling makita kung may problema sa connection
// [WARNING] Turn off ito kapag live na sa production environment para hindi makita ng users ang error details (Security Risk)
// Advice: Remove or comment out `mysqli_report` in production.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Subukang mag-connect sa database gamit ang settings sa itaas
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Set ang character set sa utf8mb4 para suportado ang lahat ng characters (pati emojis at special symbols)
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    // Kapag nag-fail ang connection, ipakita ang error message at itigil ang script
    // Note: Sa production, mas maganda kung i-log na lang ang error sa file imbis na i-display sa screen.
    die("Database Connection Failed: " . $e->getMessage() . " (Host: $servername)");
}
?>