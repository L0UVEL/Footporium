<?php
session_start();
include '../includes/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['product_id'])) {
    $action = $_POST['action'];
    $product_id = intval($_POST['product_id']);

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Handle Actions: remove, increase, decrease logic
    if ($action === 'remove') {
        unset($_SESSION['cart'][$product_id]);
    } elseif ($action === 'increase') {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]++;
        } else {
            $_SESSION['cart'][$product_id] = 1;
        }
    } elseif ($action === 'decrease') {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]--;
            if ($_SESSION['cart'][$product_id] <= 0) {
                unset($_SESSION['cart'][$product_id]);
            }
        }
    }

    // Recalculate Totals: Compute ulit ang total items at price para sa UI update
    $total_items = 0;
    $total_price = 0;
    $cart_empty = true;

    if (!empty($_SESSION['cart'])) {
        $cart_empty = false;
        $cart_empty = false;
        $ids = array_keys($_SESSION['cart']);

        if (!empty($ids)) {
            // Prepared statement for variable number of IDs
            $types = str_repeat('i', count($ids));
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            $sql = "SELECT id, price FROM products WHERE id IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$ids);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = false;
        }

        while ($row = $result->fetch_assoc()) {
            $qty = $_SESSION['cart'][$row['id']];
            $total_items += $qty;
            $total_price += $row['price'] * $qty;
        }
    }

    echo json_encode([
        'status' => 'success',
        'cart_count' => $total_items,
        'cart_total' => '₱' . number_format($total_price, 2),
        'cart_empty' => $cart_empty
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
