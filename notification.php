<?php
require_once('config/db.php'); // Assuming you have a file for database connection

// Ensure user is logged in
if (!isset($_SESSION['idno'])) {
    header("Location: login.php");
    exit();
}

$idno = $_SESSION['idno'];

// Fetch user details
$query = "SELECT firstname, lastname, session AS remaining_sessions, profile_pic FROM users WHERE idno = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idno);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$student_name = $user['firstname'] . " " . $user['lastname'];
$remaining_sessions = $user['remaining_sessions'];
$profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'img/default.png';

// Define $full_name and $session_count
$full_name = $student_name;
$session_count = $remaining_sessions;

// Fetch unread notifications count
$query = "SELECT COUNT(*) AS unread_count FROM notifications WHERE idno = ? AND is_read = 0";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idno);
$stmt->execute();
$result = $stmt->get_result();
$unread_data = $result->fetch_assoc();
$unread_count = $unread_data['unread_count'] ?? 0;
$stmt->close();

// Fetch user notifications
$query = "SELECT id, message, created_at, is_read FROM notifications WHERE idno = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idno);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Mark notifications as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE idno = ?");
    $stmt->bind_param("i", $idno);
    $stmt->execute();
    $stmt->close();

    // Refresh the page to show updated notifications
    header("Location: notification.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">  
</head>
<body class="bg-cover bg-center h-screen flex" style="background-image: url('img/5.jpg');">
<nav class="w-60 bg-green-700 bg-opacity-60 text-green-900 p-5 rounded-r-2xl shadow-lg fixed top-0 left-0 h-full">
        <div class="logo text-center mb-6">
            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" class="w-24 h-24 object-cover rounded-full border-2 border-green-800 mx-auto">
            <p class="mt-2 text-white font-bold"><?php echo htmlspecialchars($full_name); ?></p>
            <p class="text-sm text-gray-200"><strong>Session:</strong> <?php echo htmlspecialchars($session_count); ?></p>  
        </div>
        <a href="dashboard.php" class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800">
            <i class="fas fa-user mr-3"></i> Home
        </a>
        <a href="edit.php" class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800">
            <i class="fas fa-edit mr-3"></i> Profile
        </a>
        <a href="reservation.php" class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800">
            <i class="fas fa-calendar-check mr-3"></i> Reservation
        </a>
        <a href="history.php" class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800">
            <i class="fas fa-history mr-3"></i> History
        </a>
        <a href="notification.php" class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800">
            <i class="fas fa-bell mr-3"></i> Notifications <?php if ($unread_count > 0) echo "($unread_count)"; ?>
        </a>
        <a href="logout.php" class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800">
            <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
    </nav>

<div class="flex-1 p-6 ml-60 space-y-6">
    <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg">
        <h2 class="text-xl font-bold text-green-900 mb-4">Notifications</h2>
        <form method="POST" class="mb-4">
            <button type="submit" name="mark_all_read" class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800">Mark All as Read</button>
        </form>
        <div class="space-y-4 max-h-72 overflow-y-auto">
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="p-4 rounded-lg shadow <?php echo $notification['is_read'] ? 'bg-gray-200' : 'bg-green-100'; ?>">
                        <p class="text-sm text-gray-700"><?php echo htmlspecialchars($notification['message']); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($notification['created_at']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-700">No notifications available.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
