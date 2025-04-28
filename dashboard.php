<?php
include 'config/db.php'; // Assuming you have a file for database connection

// Ensure user is logged in
if (!isset($_SESSION['idno'])) {
    header("Location: login.php");
    exit();
}

// Fetch announcements from the database
$query = "SELECT title, content, created_at FROM announcement ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$announcements = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Fetch session count and user details
$idno = $_SESSION['idno'];
$query = "SELECT session AS session_count, firstname, lastname, profile_pic FROM users WHERE idno = '$idno'";
$result = mysqli_query($conn, $query);
$session_data = mysqli_fetch_assoc($result);
$session_count = $session_data['session_count'] ?? 'N/A';
$full_name = ($session_data['firstname'] ?? '') . ' ' . ($session_data['lastname'] ?? '');
$profile_pic = !empty($session_data['profile_pic']) ? $session_data['profile_pic'] : 'img/default.png';

// Fetch unread notifications count
$query = "SELECT COUNT(*) AS unread_count FROM notifications WHERE idno = '$idno' AND is_read = 0";
$result = mysqli_query($conn, $query);
$notification_data = mysqli_fetch_assoc($result);
$unread_count = $notification_data['unread_count'] ?? 0;
$currentPage = basename($_SERVER['PHP_SELF']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
        <a href="dashboard.php"    class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800 <?php echo ($currentPage == 'dashboard.php') ? 'bg-green-800' : ''; ?>">
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
        <div class="flex-1 space-y-6">
            <div class="text-left text-2xl font-bold text-green-900">Dashboard</div>
        </div>
        <div class="grid grid-cols-2 gap-6">
            <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg">
                <h2 class="text-xl font-bold text-green-900 mb-4">Announcements</h2>
                <div class="space-y-4 max-h-72 overflow-y-auto">
                    <?php if (!empty($announcements)): ?>
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="p-4 bg-white bg-opacity-50 rounded-lg shadow">
                                <p class="font-bold text-green-900"><?php echo htmlspecialchars($announcement['title']); ?></p>
                                <p class="text-sm text-gray-700"><?php echo htmlspecialchars($announcement['content']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($announcement['created_at']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-700">No announcements available.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg">
                <h2 class="text-xl font-bold text-green-900 mb-4">Rules and Regulations</h2>
                <div class="space-y-4 max-h-72 overflow-y-auto">
                    <p class="text-sm text-gray-700">To avoid embarrassment and maintain camaraderie with your friends and superiors at our laboratories, please observe the following:</p>
                    <p class="text-sm text-gray-700">1. Maintain silence, proper decorum, and discipline inside the laboratory.</p>
                    <p class="text-sm text-gray-700">2. Games are not allowed inside the lab.</p>
                    <p class="text-sm text-gray-700">3. Surfing the Internet is allowed only with the permission of the instructor.</p>
                    <p class="text-sm text-gray-700">4. Getting access to other websites not related to the course (especially pornographic and illicit sites) is strictly prohibited.</p>
                    <p class="text-sm text-gray-700">5. Deleting computer files and changing the set-up of the computer is a major offense.</p>
                    <p class="text-sm text-gray-700">6. Observe computer time usage carefully. A fifteen-minute allowance is given for each use. Otherwise, the unit will be given to those who wish to "sit-in".</p>
                    <p class="text-sm text-gray-700">7. Observe proper decorum while inside the laboratory.</p>
                    <p class="text-sm text-gray-700">a. Do not get inside the lab unless the instructor is present.</p>
                    <p class="text-sm text-gray-700">b. All bags, knapsacks, and the likes must be deposited at the counter.</p>
                    <p class="text-sm text-gray-700">c. Follow the seating arrangement of your instructor.</p>
                    <p class="text-sm text-gray-700">d. At the end of class, all software programs must be closed.</p>
                    <p class="text-sm text-gray-700">e. Return all chairs to their proper places after using.</p>
                    <br>
                    <p class="text-sm text-gray-700">8. Chewing gum, eating, drinking, smoking, and other forms of vandalism are prohibited inside the lab.</p>
                    <p class="text-sm text-gray-700">9. Anyone causing a continual disturbance will be asked to leave the lab. Acts or gestures offensive to the members of the community, including public display of physical intimacy, are not tolerated.</p>
                    <p class="text-sm text-gray-700">10. Persons exhibiting hostile or threatening behavior such as yelling, swearing, or disregarding requests made by lab personnel will be asked to leave the lab.</p>
                    <p class="text-sm text-gray-700">11. For serious offense, the lab personnel may call the Civil Security Office (CSU) for assistance.</p>
                    <p class="text-sm text-gray-700">12. Any technical problem or difficulty must be addressed to the laboratory supervisor, student assistant or instructor immediately. </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>