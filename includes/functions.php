<?php
// Function para linisin ang input data at iwasan ang SQL injection o hacking
// Importante ito para safe ang data na papasok sa database at hindi makasira ng system
function sanitize_input($data)
{
    $data = trim($data); // Tanggalin ang mga extra space sa simula at dulo
    $data = stripslashes($data); // Tanggalin ang mga backslash (\) na pwedeng mag-cause ng error
    $data = htmlspecialchars($data); // Gawing HTML entities ang mga special characters (<, >, &)
    return $data;
}

// Helper function para ilipat (redirect) ang user sa ibang page
// Huminto agad ang script pagkatapos mag-redirect `exit()` para sure
function redirect($url)
{
    header("Location: $url");
    exit();
}

// Check kung naka-login ang user
// Kung hindi pa naka-login, ilipat sila sa login page
// Ginagamit ito sa mga pages na pang-members lang
function check_login()
{
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

// Check kung admin ang user
// Kung hindi admin, ibalik sila sa homepage (Security measure ito)
// Para hindi ma-access ng regular user ang admin panel
function check_admin()
{
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        redirect('../index.php');
    }
}

// Helper function para sa pag-upload ng pictures (Profile pic, Products)
function uploadImage($file, $targetDir = "assets/uploads/")
{
    // Tukuyin kung anong klase ng files ang pwede (Allowed types only)
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // Kunin ang pangalan at extension ng file (halimbawa: .jpg, .png)
    $fileName = basename($file['name']);
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Check kung tama ang file type (kung nasa allowed list)
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.'];
    }

    // Check ang file size (Limitado lang sa 10MB para kaya ang mobile photos pero di masyadong mabigat)
    if ($file['size'] > 10000000) {
        return ['success' => false, 'message' => 'File is too large. Max 10MB allowed.'];
    }

    // Gumawa ng unique na filename para hindi magkapalit-palit ang mga pictures kung pareho ng pangalan
    // uniqid() generates a unique ID based on current time
    $newFileName = uniqid() . '.' . $fileType;

    // Siguraduhing malinis ang path ng folder (may slash sa dulo)
    $targetDir = rtrim($targetDir, '/') . '/';
    $targetPath = $targetDir . $newFileName;

    // Ayusin ang full path (Absolute path) para sa server (C:/xampp/htdocs/...)
    // Ito ay mahalaga para gumana nang tama ang `move_uploaded_file` permissions
    $rootPath = dirname(__DIR__);
    $absoluteTargetDir = $rootPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $targetDir);
    $absoluteTargetFile = $rootPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $targetPath);

    // Gumawa ng folder kung wala pa ito
    if (!is_dir($absoluteTargetDir)) {
        // Subukang gumawa ng directory na may full permissions (0777) para makapagsulat
        if (!mkdir($absoluteTargetDir, 0777, true)) {
            return ['success' => false, 'message' => 'Failed to create upload directory: ' . $targetDir];
        }
    }

    // Check kung pwede sulatan ang folder (Writable permission)
    if (!is_writable($absoluteTargetDir)) {
        return ['success' => false, 'message' => 'Directory not writable. Please CHMOD 777: ' . $targetDir];
    }

    // Subukang i-move ang uploaded file mula sa temp folder papunta sa tamang folder
    if (move_uploaded_file($file['tmp_name'], $absoluteTargetFile)) {
        return ['success' => true, 'path' => $targetPath];
    } else {
        // Mag-balik ng error kung nabigo ang pag-move
        return ['success' => false, 'message' => 'Failed to move uploaded file. Check folder permissions for: ' . $targetDir];
    }
}
?>