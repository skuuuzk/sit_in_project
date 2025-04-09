<?php
session_start(); // Ensure session is started
require_once('config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback = $_POST['feedback'] ?? '';
    $idno = $_SESSION['idno'] ?? null; // Assuming student ID is stored in session
    $reservation_id = $_POST['reservation_id'] ?? null;

    if (!empty($feedback) && $idno && !empty($reservation_id)) {
        $stmt = $conn->prepare("UPDATE reservations SET feedback = ?, feedback_timestamp = NOW() WHERE id = ? AND idno = ?");
        $stmt->bind_param('sis', $feedback, $reservation_id, $idno);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $stmt->close();
            header("Location: history.php?feedback_status=success");
            exit();
        } else {
            $stmt->close();
            header("Location: history.php?feedback_status=error");
            exit();
        }
    } else {
        header("Location: history.php?feedback_status=invalid");
        exit();
    }
} else {
    header("Location: history.php?feedback_status=method_error");
    exit();
}
?>
