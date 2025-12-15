<?php
session_start();
include '../includes/db_connect.php';
include '../includes/functions.php';

check_admin();

// Fetch Current Admin User
$admin_id = $_SESSION['user_id'];
$sql_user = "SELECT first_name, last_name, profile_image FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $admin_id);
$stmt_user->execute();
$current_user = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

// Stats Queries
$stats = [
    'products' => $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'],
    'orders' => $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'],
    'users' => $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'],
    'revenue' => $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'delivered'")->fetch_assoc()['total'] ?? 0
];

// Fetch products
$sql = "SELECT * FROM products ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Footporium Admin</title>
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

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-brand">
                <i class="fas fa-shoe-prints"></i> Footporium
            </a>
        </div>
        <div class="d-flex flex-column gap-2 px-3">
            <a href="dashboard.php" class="nav-link active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="add_product.php" class="nav-link">
                <i class="fas fa-plus-circle"></i> Add Product
            </a>
            <a href="orders.php" class="nav-link">
                <i class="fas fa-box"></i> Orders
            </a>
            <a href="../reset_data.php" class="nav-link text-warning" target="_blank">
                <i class="fas fa-exclamation-triangle"></i> Reset Data
            </a>
            <hr class="text-white opacity-25">
            <a href="../index.php" class="nav-link">
                <i class="fas fa-external-link-alt"></i> View Website
            </a>
            <a href="../logout.php" class="nav-link text-danger" onclick="window.location.href='../logout.php'; return false;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Dashboard</h2>
                    <p class="text-muted">Welcome back, Admin.</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white p-2 rounded shadow-sm">
                        <i class="fas fa-bell text-muted"></i>
                    </div>
                    <a href="../profile.php" class="text-decoration-none">
                        <?php if (!empty($current_user['profile_image'])): ?>
                            <img src="../<?php echo htmlspecialchars($current_user['profile_image']); ?>" alt="Admin" width="40" height="40" class="rounded-circle bg-white p-1 shadow-sm" style="object-fit: cover;">
                        <?php else: ?>
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($current_user['first_name'] . ' ' . $current_user['last_name']); ?>&background=800000&color=fff" alt="Admin" width="40" height="40" class="rounded-circle bg-white p-1 shadow-sm">
                        <?php endif; ?>
                    </a>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div class="stats-info">
                            <h3><?php echo $stats['products']; ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon text-success"
                            style="color: #198754; background: rgba(25, 135, 84, 0.1);">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stats-info">
                            <h3><?php echo $stats['orders']; ?></h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon text-info" style="color: #0dcaf0; background: rgba(13, 202, 240, 0.1);">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stats-info">
                            <h3>₱<?php echo number_format($stats['revenue'], 2); ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">Recent Products</h4>
                <a href="add_product.php" class="btn btn-primary"
                    style="background: var(--primary-color); border: none;">
                    <i class="fas fa-plus me-2"></i> Add New
                </a>
            </div>

            <div class="admin-table-card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category (Desc)</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($result && $result->num_rows > 0): 
                                // Reset pointer just in case
                                $result->data_seek(0);
                                while($row = $result->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <?php 
                                                $imgSrc = !empty($row['image_url']) ? '../' . $row['image_url'] : '../assets/img/placeholder.png';
                                            ?>
                                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="product-thumb">
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($row['name']); ?></h6>
                                                <small class="text-muted">ID: #<?php echo $row['id']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;">
                                            <?php echo htmlspecialchars($row['description']); ?>
                                        </div>
                                    </td>
                                    <td class="fw-bold">₱<?php echo number_format($row['price'], 2); ?></td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Active</span></td>
                                    <td class="text-end">
                                        <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="action-btn btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="delete_product.php?id=<?php echo $row['id']; ?>" class="action-btn btn-delete" title="Delete" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-5">No products found.</td></tr>
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