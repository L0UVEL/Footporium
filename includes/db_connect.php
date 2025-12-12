<?php
$servername = "localhost";
$username = "root"; // Default XAMPP/WAMP username
$password = ""; // Default XAMPP/WAMP password
$dbname = "if0_40664766_db_footporium";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully"; // Uncomment for testing
?>