<?php 
session_start();
require_once('../config/db.php'); 

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; 

// Fetch user details from database
$stmt = $conn->prepare("SELECT USERNAME, PROFILE_PIC FROM USERS WHERE USER_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found!";
    exit();
}

$username = $user['USERNAME'];
// Set up profile picture path handling - simplified approach
$default_pic = "images/snoopy.jpg";
$profile_pic = !empty($user['PROFILE_PIC']) ? $user['PROFILE_PIC'] : $default_pic;

// Mock data for notifications (this would come from a database in a real app)
$notifications = [
    [
        'id' => 1,
        'title' => 'Reservation Confirmed',
        'message' => 'Your reservation for CCS Lab 1, PC-15 on May 20, 2024 at 9:00 AM has been confirmed.',
        'date' => '2024-05-18 14:30:00',
        'read' => false
    ],
    [
        'id' => 2,
        'title' => 'Lab Schedule Update',
        'message' => 'CCS Lab 2 will be closed for maintenance on May 25, 2024. Please reschedule any reservations for that day.',
        'date' => '2024-05-15 10:15:00',
        'read' => true
    ],
    [
        'id' => 3,
        'title' => 'Session Reminder',
        'message' => 'Your sit-in session is scheduled to begin in 30 minutes.',
        'date' => '2024-05-10 08:30:00',
        'read' => true
    ],
];

// Format date helper function
function formatTimeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return "Just now";
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . " minute" . ($minutes > 1 ? "s" : "") . " ago";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    } else {
        return date("M j, Y", $timestamp);
    }
}

$pageTitle = "Notifications";
$bodyClass = "bg-light font-montserrat";
include('includes/header.php');
?>
