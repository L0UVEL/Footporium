<?php
session_start();
include '../includes/db_connect.php';
include '../includes/functions.php';

check_admin();

if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit;
}

$order_id = intval($_GET['id']);

// Update Status Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $status = sanitize_input($_POST['status']);
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    if ($stmt->execute()) {
        $success = "Order status updated to " . ucfirst($status);
    } else {
        $error = "Failed to update status.";
    }
}

// Fetch Order Details
$sql = "SELECT o.*, u.full_name, u.email, u.phone, a.address_line, a.city, a.postal_code, a.country, a.province, a.barangay  
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN addresses a ON o.address_id = a.id
        WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

// Fetch Items
$sql_items = "SELECT oi.*, p.name, p.image_data 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items = $stmt_items->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order #<?php echo $order_id; ?> | Footporium Admin</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Order #<?php echo str_pad($order_id, 5, '0', STR_PAD_LEFT); ?></h2>
            <a href="orders.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i> Back to
                Orders</a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header fw-bold">Order Items</div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $items->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="data:image/png;base64,<?php echo base64_encode($item['image_data']); ?>"
                                                    width="50" height="50" class="rounded" style="object-fit:cover">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </div>
                                        </td>
                                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                        <td>x<?php echo $item['quantity']; ?></td>
                                        <td class="text-end fw-bold">
                                            ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot class="fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                                    <td class="text-end fw-bold fs-5">
                                        ₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header fw-bold">Customer Details</div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                        <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                        <hr>
                        <p class="mb-1"><strong>Shipping Address:</strong></p>
                        <p class="text-muted mb-0">
                            <?php echo htmlspecialchars($order['address_line']); ?><br>
                            <?php echo htmlspecialchars($order['barangay']) . ', ' . htmlspecialchars($order['city']); ?><br>
                            <?php echo htmlspecialchars($order['province']) . ', ' . htmlspecialchars($order['country']); ?><br>
                            <?php echo htmlspecialchars($order['postal_code']); ?>
                        </p>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header fw-bold">Order Status</div>
                    <div class="card-body">
                        <form action="" method="post">
                            <label class="form-label">Current Status</label>
                            <select name="status" class="form-select mb-3">
                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>
                                    Pending</option>
                                <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>
                                    Shipped</option>
                                <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" class="btn btn-primary w-100">Update Status</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>