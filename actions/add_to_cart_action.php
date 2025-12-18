<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if user is logged in (Kailangan naka-login para makapag-add to cart)
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }

    // Kunin ang product ID at quantity mula sa form
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Validations: Siguraduhing valid ang ID at quantity
    if ($product_id > 0 && $quantity > 0) {
        // Initialize cart if not exists (Kung wala pang cart session, gumawa ng bago)
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Add or update quantity: Check kung nasa cart na, dagdagan lang ang qty. Kung wala, i-add bilang bagong item.
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    }

    // AJAX Response: Kung via AJAX ang request (walang page reload), mag-return ng JSON count
    if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
        $total_items = 0;
        foreach ($_SESSION['cart'] as $qty) {
            $total_items += $qty;
        }
        echo json_encode(['status' => 'success', 'cart_count' => $total_items]);
        exit();
    }

    // Redirect pabalik sa previous page (fallback kung hindi AJAX)
    session_write_close();
    header("Location: ../product_details.php?id=" . $product_id . "&added=1");
    exit();
} else {
    // Kung hindi POST request, ibalik sa home
    header("Location: ../index.php");
    exit();
}
?>