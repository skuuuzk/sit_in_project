<?php
require_once('config/db.php'); // Include database connection

if (isset($_GET['lab'])) {
    $lab = $_GET['lab'];

    // Generate 50 PCs dynamically for the selected lab
    $pcs = [];
    for ($i = 1; $i <= 50; $i++) {
        $pcs[] = "PC $i";
    }

    // Return the list of PCs as JSON
    header('Content-Type: application/json');
    echo json_encode($pcs);
    exit();
}

// Return an empty array if no lab is selected
header('Content-Type: application/json');
echo json_encode([]);
exit();
