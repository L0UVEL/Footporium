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
    $target_dir = "../assets/img/";
    $file_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $file_name;
    $db_image_path = "assets/img/" . $file_name;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        $error = "File is not an image.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO products (name, price, image_url, description) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdss", $name, $price, $db_image_path, $description);
            
            if ($stmt->execute()) {
                $success = "Product added successfully!";
            } else {
                $error = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product | Footporium Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Add New Product</h4>
                    <a href="dashboard.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form action="add_product.php" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price (₱)</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="image" name="image" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Add Product</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
