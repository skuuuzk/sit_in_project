<?php
require_once('config/db.php');

// Ensure the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $idno = $data['idno'] ?? null;
    $purpose = $data['purpose'] ?? null;
    $lab = $data['lab'] ?? null;

    if ($idno && $purpose && $lab) {
        // Update the student's status or log the sit-in session
        $stmt = $conn->prepare("UPDATE users SET session = session - 1 WHERE idno = ? AND session > 0");
        $stmt->bind_param("s", $idno);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No remaining sessions or invalid student ID.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
