<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }

    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    if ($product_id > 0 && $quantity > 0) {
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Add or update quantity
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    }

    // AJAX Response
    if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
        $total_items = 0;
        foreach ($_SESSION['cart'] as $qty) {
            $total_items += $qty;
        }
        echo json_encode(['status' => 'success', 'cart_count' => $total_items]);
        exit();
    }

    // Redirect back
    session_write_close();
    header("Location: ../product_details.php?id=" . $product_id . "&added=1");
    exit();
} else {
    header("Location: ../index.php");
    exit();
}
?>