<?php
session_start();
include '../includes/db_connect.php';
include '../includes/functions.php';

check_admin();

$error = '';
$success = '';
$product = null;

// Get Product ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        header("Location: dashboard.php");
        exit;
    }
} else {
    // If no ID and not a POST request (which might carry ID), redirect
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        header("Location: dashboard.php");
        exit;
    }
}

// Handle Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $name = sanitize_input($_POST['name']);
    $price = floatval($_POST['price']);
    $description = sanitize_input($_POST['description']);

    // Check if image is being updated
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $uploadResult = uploadImage($_FILES["image"], "assets/uploads/products/");

        if ($uploadResult['success']) {
            $image_url = $uploadResult['path'];

            $sql = "UPDATE products SET name=?, price=?, description=?, image_url=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdssi", $name, $price, $description, $image_url, $id);
        } else {
            $error = $uploadResult['message'];
        }
    } else {
        // No image update
        $sql = "UPDATE products SET name=?, price=?, description=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdsi", $name, $price, $description, $id);
    }

    if (empty($error)) {
        if ($stmt->execute()) {
            $success = "Product updated successfully!";
            // Refresh product data
            $stmt_refresh = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt_refresh->bind_param("i", $id);
            $stmt_refresh->execute();
            $product = $stmt_refresh->get_result()->fetch_assoc();
        } else {
            $error = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product | Footporium Admin</title>
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
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="add_product.php" class="nav-link">
                <i class="fas fa-plus-circle"></i> Add Product
            </a>
            <a href="orders.php" class="nav-link">
                <i class="fas fa-box"></i> Orders
            </a>

            <!-- Using active class for context, though not strictly in sidebar links -->
            <a href="#" class="nav-link active">
                <i class="fas fa-edit"></i> Edit Product
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

    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid">

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold mb-1">Edit Product</h2>
                            <p class="text-muted">Update product details.</p>
                        </div>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back
                        </a>
                    </div>

                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-5">
                            <?php if ($error): ?>
                                <div class="alert alert-danger rounded-3"><i class="fas fa-exclamation-circle me-2"></i>
                                    <?php echo $error; ?></div>
                            <?php endif; ?>
                            <?php if ($success): ?>
                                <div class="alert alert-success rounded-3"><i class="fas fa-check-circle me-2"></i>
                                    <?php echo $success; ?></div>
                            <?php endif; ?>

                            <?php if ($product): ?>
                                <form action="edit_product.php?id=<?php echo $product['id']; ?>" method="post"
                                    enctype="multipart/form-data">
                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">

                                    <div class="mb-4">
                                        <label for="name" class="form-label fw-bold">Product Name</label>
                                        <input type="text" class="form-control form-control-lg" id="name" name="name"
                                            required value="<?php echo htmlspecialchars($product['name']); ?>">
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label for="price" class="form-label fw-bold">Price (₱)</label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-light text-muted">₱</span>
                                                <input type="number" step="0.01" class="form-control" id="price"
                                                    name="price" required value="<?php echo $product['price']; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label for="image" class="form-label fw-bold">Update Image (Optional)</label>
                                            <input type="file" class="form-control form-control-lg" id="image" name="image">
                                            <div class="mt-2">
                                                <small class="text-muted">Current Image:</small><br>
                                                <?php
                                                $currImg = !empty($product['image_url']) ? '../' . $product['image_url'] : '../assets/img/placeholder.png';
                                                ?>
                                                <img src="<?php echo htmlspecialchars($currImg); ?>" height="60"
                                                    class="rounded border p-1 mt-1">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="description" class="form-label fw-bold">Description</label>
                                        <textarea class="form-control" id="description" name="description"
                                            rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg"
                                            style="background: var(--primary-color); border: none;">
                                            <i class="fas fa-save me-2"></i> Update Product
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-warning">Product not found.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>