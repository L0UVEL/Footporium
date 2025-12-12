<?php
session_start();
include '../includes/db_connect.php';
include '../includes/functions.php';

check_admin();

// Fetch products
$sql = "SELECT * FROM products ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Footporium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f8; }
        .sidebar { min-height: 100vh; background: #343a40; color: white; }
        .sidebar a { color: rgba(255,255,255,.8); text-decoration: none; padding: 10px 20px; display: block; }
        .sidebar a:hover { background: rgba(255,255,255,.1); color: white; }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar col-md-2 d-none d-md-block">
        <div class="p-3">
            <h4 class="fw-bold">Admin Panel</h4>
        </div>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        <a href="add_product.php"><i class="fas fa-plus-circle me-2"></i> Add Product</a>
        <a href="../index.php"><i class="fas fa-home me-2"></i> View Site</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="col-md-10 col-12 p-4">
        <h2 class="mb-4">Product Management</h2>
        <a href="add_product.php" class="btn btn-success mb-3"><i class="fas fa-plus"></i> Add New Product</a>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><img src="../<?php echo htmlspecialchars($row['image_url']); ?>" width="50" height="50" style="object-fit:cover; border-radius:5px;"></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td>₱<?php echo number_format($row['price'], 2); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <?php if (!$result): ?>
                                    <tr><td colspan="5" class="text-center text-danger">Database Error: <?php echo $conn->error; ?></td></tr>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center">No products found.</td></tr>
                                <?php endif; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
