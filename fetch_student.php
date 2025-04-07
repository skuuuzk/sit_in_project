<?php
require_once('config/db.php'); // Assuming you have a file for database connection

// Ensure the request contains the 'id' parameter
if (isset($_GET['id'])) {
    $idno = $_GET['id'];

    // Check if the database connection is valid
    if ($conn->connect_error) {
        echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
        exit();
    }

    // Prepare the query to fetch detailed student information
    $stmt = $conn->prepare("SELECT idno, firstname, lastname, email, course, year, session FROM users WHERE idno = ?");
    if ($stmt) {
        $stmt->bind_param("s", $idno);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the student exists
        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();
            echo json_encode($student);
        } else {
            // Send disapproval notification
            $notification = "Sit-in Disapproved";
            $stmt = $conn->prepare("INSERT INTO notifications (idno, message) VALUES (?, ?)");
            $stmt->bind_param("is", $idno, $notification);
            $stmt->execute();
            $stmt->close();

            echo json_encode(['error' => 'Student not found']);
        }

        $stmt->close();
    } else {
        echo json_encode(['error' => 'Failed to prepare the database query: ' . $conn->error]);
    }
} else {
    echo json_encode(['error' => 'Invalid request. Missing ID parameter.']);
}
?>
