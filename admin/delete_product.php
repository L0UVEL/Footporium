<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/db_connect.php';
include '../includes/functions.php';

// Verify Admin Privileges
check_admin();

// Check kung may ID na pinasa
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Perform delete: Burahin ang product sa database gamit ang ID
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Success: Redirect sa dashboard with success message
        header("Location: dashboard.php?msg=deleted");
    } else {
        // Failed: Redirect with error
        header("Location: dashboard.php?err=delete_failed");
    }
    $stmt->close();
} else {
    // Kung walang ID, balik lang sa dashboard
    header("Location: dashboard.php");
}
exit;
?>