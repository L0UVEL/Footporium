<?php
session_start();
include '../includes/db_connect.php';
include '../includes/functions.php';

check_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);
    $order_id = intval($_POST['order_id']); // Helps redirection pabalik sa order details
    $rating = intval($_POST['rating']);
    $comment = sanitize_input($_POST['comment']);

    if ($rating < 1 || $rating > 5) {
        header("Location: ../order_details.php?id=$order_id&error=invalid_rating");
        exit;
    }

    // Insert review: I-save ang review sa database
    $sql = "INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);

    if ($stmt->execute()) {
        header("Location: ../order_details.php?id=$order_id&msg=review_success");
    } else {
        header("Location: ../order_details.php?id=$order_id&error=failed");
    }
} else {
    redirect('../index.php');
}
?>