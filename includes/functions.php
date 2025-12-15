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

// Helper paramakpag-redirect sa ibang page
// Stops script execution after redirect
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
// Helper to upload images
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

    // Validate size (e.g., max 5MB)
    if ($file['size'] > 5000000) {
        return ['success' => false, 'message' => 'File is too large. Max 5MB allowed.'];
    }

    // Generate unique filename to avoid overwrites
    $newFileName = uniqid() . '.' . $fileType;
    $targetPath = $targetDir . $newFileName;

    // We need to resolve relative path for move_uploaded_file, but return relative path for DB
    // Assuming $targetDir is relative from the script execution root or we pass absolute path?
    // Let's assume $targetDir is passed relative to public root (e.g. assets/uploads/products/)
    // But move_uploaded_file needs correct path relative to the executing script. 
    // Since scripts vary (admin/ vs /), we need to be careful.
    // Ideally we use absolute path for movement.

    // Let's resolve absolute path based on this file's location which is in includes/
    // __DIR__ is .../includes
    // root is dirname(__DIR__)
    $rootPath = dirname(__DIR__);
    $absoluteTarget = $rootPath . '/' . $targetPath;

    if (move_uploaded_file($file['tmp_name'], $absoluteTarget)) {
        return ['success' => true, 'path' => $targetPath];
    } else {
        return ['success' => false, 'message' => 'Failed to move uploaded file. Check permissions.'];
    }
}
?>