<?php
include 'includes/db_connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Check kung products or users (profile) ang ihahandle - default to products for SEO
    // Pwede magdagdag ng type param kung kailangan. Defaulting to 'product' ngayon.

    $sql = "SELECT image_data FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($image_data);
        $stmt->fetch();

        if ($image_data) {
            // I-set ang content type header para marecognize ng browser na image ito
            header("Content-Type: image/png"); // Assuming PNG or JPEG
            echo $image_data;
        } else {
            // Serve placeholder if data empty but row exists (Optional)
            // header("Location: assets/img/placeholder.png");
        }
    } else {
        // Not found
        header("HTTP/1.0 404 Not Found");
    }
    $stmt->close();
}
?>