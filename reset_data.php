<?php
session_start();
include 'includes/db_connect.php';

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_data'])) {
    // Disable foreign key checks para pwede mag-truncate kahit may constraints
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    $tables = ['reviews', 'order_items', 'orders'];
    $success = true;

    foreach ($tables as $table) {
        // TRUNCATE table: Burahin lahat ng laman ng table at reset auto-increment ID
        if (!$conn->query("TRUNCATE TABLE $table")) {
            $success = false;
            $message = "Error truncating table $table: " . $conn->error;
            break;
        }
    }

    // Enable foreign key checks ulit
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    if ($success) {
        $message = "Successfully reset Reviews, Orders, and Order Items.";
        $messageType = "success";
    } elseif (empty($message)) {
        $message = "An unknown error occurred.";
        $messageType = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Data | Footporium</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .card {
            max-width: 500px;
            width: 100%;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 20px;
        }

        .btn-danger-custom {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-danger-custom:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }
    </style>
</head>

<body>

    <div class="card p-5 text-center">
        <h2 class="mb-4 fw-bold text-danger">⚠️ Reset Data</h2>
        <p class="text-muted mb-4">
            This action will <strong>PERMANENTLY DELETE</strong> all:
        <ul class="text-start d-inline-block text-muted">
            <li>User Reviews</li>
            <li>Orders</li>
            <li>Order Items</li>
        </ul>
        </p>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> mb-4"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" onsubmit="return confirm('Are you absolutely sure? This cannot be undone.');">
            <button type="submit" name="reset_data" class="btn btn-danger-custom mb-3">
                Yes, Delete All Data
            </button>
        </form>
        <a href="index.php" class="text-decoration-none text-secondary">Cancel and Go Home</a>
    </div>

</body>

</html>