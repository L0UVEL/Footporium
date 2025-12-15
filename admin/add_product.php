<?php
session_start();
include '../includes/db_connect.php';
include '../includes/functions.php';

check_admin();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize_input($_POST['name']);
    $price = floatval($_POST['price']);
    $description = sanitize_input($_POST['description']);

    // Image Upload
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        // Use helper function to upload to products directory (relative to admin file, need to step out)
        // Helper defaults to assets/uploads/, but we want assets/uploads/products/
        // Since helper takes path relative to where it was included (functions.php is in includes, but called from admin/)
        // Actually, helper uses absolute path for move_uploaded_file, but return relative path.
        // We just need to pass the target directory string we want in the DB/Path.

        $uploadResult = uploadImage($_FILES["image"], "assets/uploads/products/");

        if ($uploadResult['success']) {
            $image_url = $uploadResult['path'];

            $sql = "INSERT INTO products (name, price, image_url, description) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdss", $name, $price, $image_url, $description);

            if ($stmt->execute()) {
                $success = "Product added successfully!";
            } else {
                $error = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = $uploadResult['message'];
        }
    } else {
        $error = "Please select an image.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product | Footporium Admin</title>
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
            <a href="add_product.php" class="nav-link active">
                <i class="fas fa-plus-circle"></i> Add Product
            </a>
            <a href="orders.php" class="nav-link">
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

    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid">

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold mb-1">Add New Product</h2>
                            <p class="text-muted">Create a new item for your inventory.</p>
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

                            <form action="add_product.php" method="post" enctype="multipart/form-data">
                                <div class="mb-4">
                                    <label for="name" class="form-label fw-bold">Product Name</label>
                                    <input type="text" class="form-control form-control-lg" id="name" name="name"
                                        required placeholder="e.g. Bionic Foot X1">
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="price" class="form-label fw-bold">Price (₱)</label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-light text-muted">₱</span>
                                            <input type="number" step="0.01" class="form-control" id="price"
                                                name="price" required placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="image" class="form-label fw-bold">Product Image</label>
                                        <input type="file" class="form-control form-control-lg" id="image" name="image"
                                            required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="description" class="form-label fw-bold">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="5"
                                        placeholder="Enter product details..."></textarea>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg"
                                        style="background: var(--primary-color); border: none;">
                                        <i class="fas fa-save me-2"></i> Save Product
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>