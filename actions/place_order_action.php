<?php
// Turn off error reporting for output, log it instead
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '../php_errors.log'); // Log to project root

// Start buffering to catch unwanted output
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

try {
    include '../includes/db_connect.php';
    include '../includes/functions.php';

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        throw new Exception('Cart is empty');
    }

    $user_id = $_SESSION['user_id'];
    $cart = $_SESSION['cart'];

    // Debug Log
    error_log("Checkout initiated for User ID: $user_id");

    // Calculate Total
    $ids = implode(',', array_keys($cart));
    if (empty($ids))
        throw new Exception('Invalid cart data');

    $sql_cart = "SELECT * FROM products WHERE id IN ($ids)";
    $result_cart = $conn->query($sql_cart);
    $cart_items = [];
    $total_price = 0;

    if ($result_cart) {
        while ($row = $result_cart->fetch_assoc()) {
            $row['qty'] = $cart[$row['id']];
            $row['subtotal'] = $row['price'] * $row['qty'];
            $total_price += $row['subtotal'];
            $cart_items[] = $row;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $address_id = null;
        $address_line = sanitize_input($_POST['address']);
        $country = sanitize_input($_POST['country']);
        $province = sanitize_input($_POST['province']);
        $city = sanitize_input($_POST['city']);
        $barangay = sanitize_input($_POST['barangay']);
        $postal_code = sanitize_input($_POST['postal_code']);
        $payment_method = sanitize_input($_POST['payment_method']);

        error_log("Input received. Processing address...");

        // 1. Address Logic
        $check_addr = "SELECT id FROM addresses WHERE user_id = ?";
        $stmt = $conn->prepare($check_addr);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $addr_row = $res->fetch_assoc();
            $address_id = $addr_row['id'];
            $sql_addr = "UPDATE addresses SET address_line = ?, country=?, province=?, city=?, barangay=?, postal_code = ? WHERE id = ?";
            $stmt_addr = $conn->prepare($sql_addr);
            $stmt_addr->bind_param("ssssssi", $address_line, $country, $province, $city, $barangay, $postal_code, $address_id);
            $stmt_addr->execute();
            $stmt_addr->close();
        } else {
            $sql_addr = "INSERT INTO addresses (user_id, address_line, country, province, city, barangay, postal_code) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_addr = $conn->prepare($sql_addr);
            $stmt_addr->bind_param("issssss", $user_id, $address_line, $country, $province, $city, $barangay, $postal_code);
            $stmt_addr->execute();
            $address_id = $stmt_addr->insert_id;
            $stmt_addr->close();
        }

        error_log("Address processed (ID: $address_id). Creating order...");

        // 2. Create Order
        $order_status = 'pending';
        // Check if orders table exists first? Let's assume yes or catch error.
        $sql_order = "INSERT INTO orders (user_id, address_id, total_amount, status) VALUES (?, ?, ?, ?)";
        $stmt_order = $conn->prepare($sql_order);
        if (!$stmt_order)
            throw new Exception("Prepare failed: " . $conn->error);

        $stmt_order->bind_param("iids", $user_id, $address_id, $total_price, $order_status);

        if ($stmt_order->execute()) {
            $order_id = $stmt_order->insert_id;
            error_log("Order created (ID: $order_id). Adding items...");

            // 3. Order Items
            $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt_item = $conn->prepare($sql_item);

            foreach ($cart_items as $item) {
                $stmt_item->bind_param("iiid", $order_id, $item['id'], $item['qty'], $item['price']);
                $stmt_item->execute();
            }
            $stmt_item->close();

            // 4. Payment
            error_log("Recording payment...");
            $payment_status = 'pending';
            // Check if payments table exists
            $check_table = $conn->query("SHOW TABLES LIKE 'payments'");
            if ($check_table && $check_table->num_rows > 0) {
                $sql_pay = "INSERT INTO payments (order_id, payment_method, amount, status) VALUES (?, ?, ?, ?)";
                if ($stmt_pay = $conn->prepare($sql_pay)) {
                    $stmt_pay->bind_param("isds", $order_id, $payment_method, $total_price, $payment_status);
                    $stmt_pay->execute();
                    $stmt_pay->close();
                }
            } else {
                error_log("Warning: 'payments' table missing. Skipping payment record.");
            }

            // 5. Clear Cart
            unset($_SESSION['cart']);

            // CLEAR BUFFER before JSON
            ob_clean();
            echo json_encode(['status' => 'success', 'order_id' => $order_id]);
        } else {
            throw new Exception("Error executing order: " . $stmt_order->error);
        }
        $stmt_order->close();
    } else {
        throw new Exception('Invalid request method');
    }

} catch (Exception $e) {
    error_log("Checkout Error: " . $e->getMessage());
    // CLEAR BUFFER before JSON
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

ob_end_flush();
?>