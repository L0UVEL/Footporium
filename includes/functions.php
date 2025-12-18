<?php
// Function to clean input data to prevent SQL injection and XSS
// Lilinisin nito yung input para safe sa database
function sanitize_input($data)
{
    $data = trim($data); // Remove extra spaces
    $data = stripslashes($data); // Remove backslashes
    $data = htmlspecialchars($data); // Convert special chars to HTML entities
    return $data;
}

// Helper para makapag-redirect sa ibang page
// Stops script execution after redirect (Huminto agad pagkatapos lumipat)
function redirect($url)
{
    header("Location: $url");
    exit();
}

// Check kung naka-login si user
// If hindi, redirect sa login page
function check_login()
{
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

// Check kung admin ang user
// If hindi admin, ibabalik sa homepage (Security measure)
function check_admin()
{
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        redirect('../index.php');
    }
}
// Helper to upload images: Function para sa pag-upload ng pictures
// Helper to upload images: Function para sa pag-upload ng pictures
function uploadImage($file, $targetDir = "assets/uploads/")
{
    // Define allowed file types
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // Get file extension
    $fileName = basename($file['name']);
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Validate type
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.'];
    }

    // Validate size (e.g., max 10MB to accommodate mobile photos)
    if ($file['size'] > 10000000) {
        return ['success' => false, 'message' => 'File is too large. Max 10MB allowed.'];
    }

    // Generate unique filename to avoid overwrites
    $newFileName = uniqid() . '.' . $fileType;

    // Ensure separate paths are clean
    $targetDir = rtrim($targetDir, '/') . '/';
    $targetPath = $targetDir . $newFileName;

    // Resolve absolute path
    $rootPath = dirname(__DIR__);
    $absoluteTargetDir = $rootPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $targetDir);
    $absoluteTargetFile = $rootPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $targetPath);

    // Create directory if it doesn't exist
    if (!is_dir($absoluteTargetDir)) {
        if (!mkdir($absoluteTargetDir, 0777, true)) {
            return ['success' => false, 'message' => 'Failed to create upload directory: ' . $targetDir];
        }
    }

    // Check if writable
    if (!is_writable($absoluteTargetDir)) {
        return ['success' => false, 'message' => 'Directory not writable. Please CHMOD 777: ' . $targetDir];
    }

    // Try to upload
    if (move_uploaded_file($file['tmp_name'], $absoluteTargetFile)) {
        return ['success' => true, 'path' => $targetPath];
    } else {
        // Debug info included in message (safe for now as it's local/dev)
        return ['success' => false, 'message' => 'Failed to move uploaded file. Check folder permissions for: ' . $targetDir];
    }
}
?>