<?php
include 'includes/db_connect.php';

// Columns to add
$columns = [
    'region' => 'VARCHAR(100)',
    'province' => 'VARCHAR(100)',
    'barangay' => 'VARCHAR(100)'
];

foreach ($columns as $col => $type) {
    // Check if column exists
    $check_sql = "SHOW COLUMNS FROM addresses LIKE '$col'";
    $result = $conn->query($check_sql);

    if ($result && $result->num_rows == 0) {
        // Add column
        $sql = "ALTER TABLE addresses ADD COLUMN $col $type AFTER city";
        if ($conn->query($sql) === TRUE) {
            echo "Column '$col' added successfully.\n";
        } else {
            echo "Error adding column '$col': " . $conn->error . "\n";
        }
    } else {
        echo "Column '$col' already exists.\n";
    }
}

echo "Migration completed.\n";
?>