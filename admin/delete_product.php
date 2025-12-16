<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/db_connect.php';
include '../includes/functions.php';

check_admin();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $id = intval($_GET['id']);

    // Perform delete: Burahin ang product sa database gamit ang ID
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: dashboard.php?msg=deleted");
    } else {
        header("Location: dashboard.php?err=delete_failed");
    }
    $stmt->close();
} else {
    header("Location: dashboard.php");
}
exit;
