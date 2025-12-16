<?php
session_start();
include '../includes/db_connect.php';
include '../includes/functions.php';

check_login();

if (!isset($_GET['id'])) {
    redirect('../my_orders.php');
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

$user_id = $_SESSION['user_id'];

// Check ownership and status: Siguraduhin na sa user ang order at kunin ang status
$sql = "SELECT status FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $order = $result->fetch_assoc();

    // Only allow cancelling if 'pending': Bawal na i-cancel kung shipped o delivered na
    if ($order['status'] === 'pending') {
        $updateImg = "UPDATE orders SET status = 'cancelled' WHERE id = ?";
        $stmtUpdate = $conn->prepare($updateImg);
        $stmtUpdate->bind_param("i", $order_id);

        if ($stmtUpdate->execute()) {
            // Success
            header("Location: ../order_details.php?id=" . $order_id . "&msg=cancelled");
            exit;
        } else {
            // DB Error
            header("Location: ../order_details.php?id=" . $order_id . "&error=db_error");
            exit;
        }
    } else {
        // Status not pending
        header("Location: ../order_details.php?id=" . $order_id . "&error=not_pending");
        exit;
    }
} else {
    // Order not found or not owned
    redirect('../my_orders.php');
}
?>