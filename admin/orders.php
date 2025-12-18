<?php
session_start();
include '../includes/db_connect.php';
include '../includes/functions.php';

check_admin();

// Fetch Current Admin User (for sidebar/header): Kunin ang admin info para sa UI display
$admin_id = $_SESSION['user_id'];
$sql_user = "SELECT first_name, last_name, profile_image FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $admin_id);
$stmt_user->execute();
$current_user = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

// Fetch Orders: Kunin lahat ng orders kasama ang user info (Join query)
$sql = "SELECT o.*, u.first_name, u.last_name, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | Footporium Admin</title>
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

    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-brand">
                <i class="fas fa-shoe-prints"></i> Footporium
            </a>
        </div>
        <div class="d-flex flex-column gap-2 px-3">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="add_product.php" class="nav-link">
                <i class="fas fa-plus-circle"></i> Add Product
            </a>
            <a href="orders.php" class="nav-link active">
                <i class="fas fa-box"></i> Orders
            </a>

            <hr class="text-white opacity-25">
            <a href="../index.php" class="nav-link">
                <i class="fas fa-external-link-alt"></i> View Website
            </a>
            <a href="../logout.php" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Order Management</h2>
                    <p class="text-muted">View and manage customer orders.</p>
                </div>
                <!-- Admin Profile/Notifications -->
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white p-2 rounded shadow-sm">
                        <i class="fas fa-bell text-muted"></i>
                    </div>
                    <a href="../profile.php" class="text-decoration-none">
                        <?php if (!empty($current_user['profile_image'])): ?>
                            <img src="../<?php echo htmlspecialchars($current_user['profile_image']); ?>" alt="Admin"
                                width="40" height="40" class="rounded-circle bg-white p-1 shadow-sm"
                                style="object-fit: cover;">
                        <?php else: ?>
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($current_user['first_name'] . ' ' . $current_user['last_name']); ?>&background=800000&color=fff"
                                alt="Admin" width="40" height="40" class="rounded-circle bg-white p-1 shadow-sm">
                        <?php endif; ?>
                    </a>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="admin-table-card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold">#<?php echo str_pad($row['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                        <td>
                                            <div>
                                                <h6 class="mb-0">
                                                    <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                                                </h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                                            </div>
                                        </td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></td>
                                        <td class="fw-bold">₱<?php echo number_format($row['total_amount'], 2); ?></td>
                                        <td>
                                            <?php
                                            $status = $row['status'];
                                            // Dynamic badge colors para sa status
                                            $badgeClass = match ($status) {
                                                'completed', 'delivered' => 'bg-success text-success',
                                                'shipped' => 'bg-info text-info',
                                                'processing' => 'bg-primary text-primary',
                                                'cancelled' => 'bg-danger text-danger',
                                                default => 'bg-warning text-warning'
                                            };
                                            ?>
                                            <span
                                                class="badge <?php echo $badgeClass; ?> bg-opacity-10 px-3 py-2 rounded-pill text-uppercase">
                                                <?php echo $status; ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="view_order.php?id=<?php echo $row['id']; ?>"
                                                class="btn btn-sm btn-outline-primary">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">No orders found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>