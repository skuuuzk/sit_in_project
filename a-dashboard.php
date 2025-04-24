<?php
require_once('config/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}
// Fetch statistics
$students_count_query = "SELECT COUNT(*) AS count FROM users";
$students_count = mysqli_fetch_assoc(mysqli_query($conn, $students_count_query))['count'];

$current_sit_in_query = "SELECT COUNT(*) AS count FROM reservations WHERE time_out IS NULL";
$current_sit_in = mysqli_fetch_assoc(mysqli_query($conn, $current_sit_in_query))['count'];

// Fetch total sit-in count
$total_sit_in_query = "SELECT COUNT(*) AS count FROM reservations";
$total_sit_in_result = mysqli_query($conn, $total_sit_in_query);
$total_sit_in = mysqli_fetch_assoc($total_sit_in_result)['count'];

// Fetch activity statistics for graph
$activity_data_query = "SELECT purpose, COUNT(*) AS count FROM reservations GROUP BY purpose";
$activity_data_result = mysqli_query($conn, $activity_data_query);
$purpose_stats = [];
while ($row = mysqli_fetch_assoc($activity_data_result)) {
    $purpose_stats[$row['purpose']] = $row['count'];
}

// Fetch announcements
$announcements_query = "SELECT title, content, created_at FROM announcement ORDER BY created_at DESC";
$announcements_result = mysqli_query($conn, $announcements_query);
$announcements = mysqli_fetch_all($announcements_result, MYSQLI_ASSOC);

// Handle announcement submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['announcement'])) {
    $announcement_content = trim($_POST['announcement']);
    $admin_id = $_SESSION['admin_id'];

    if (!empty($announcement_content)) {
        $stmt = $conn->prepare("INSERT INTO announcement (content, admin_id) VALUES (?, ?)");
        $stmt->bind_param("si", $announcement_content, $admin_id);
        $stmt->execute();
        $stmt->close();

        header("Location: a-dashboard.php?success=1");
        exit();
    }
}

// Handle points assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_points'])) {
    $reservation_id = $_POST['reservation_id'];
    $points = $_POST['points'];

    $stmt = $conn->prepare("UPDATE reservations SET points = ? WHERE id = ?");
    $stmt->bind_param("ii", $points, $reservation_id);
    $stmt->execute();
    $stmt->close();

    // Redirect with success message
    header("Location: a-dashboard.php?points_success=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-cover bg-center h-screen flex" style="background-image: url('img/5.jpg');">
    <nav class="w-60 bg-green-700 bg-opacity-60 text-green-900 p-5 rounded-r-2xl shadow-lg fixed top-0 left-0 h-full">
        <div class="logo text-center mb-6">
            <img src="img/ccs.png" alt="Logo" class="w-20 h-20 object-cover rounded-full border-2 border-green-800 mx-auto">
        </div>
        <a href="a-dashboard.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700 active:bg-green-300">
            <i class="fas fa-user mr-3"></i> Home
        </a>
        <a href="#" onclick="openModal('searchModal')" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-search mr-3"></i> Search
        </a>
        <a href="a-students.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-users mr-3"></i> Students
        </a>
        <a href="a-currents.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-user-clock mr-3"></i> Current Sit-in
        </a>
        <a href="a-vrecords.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-book mr-3"></i> Visit Records
        </a>
        <a href="a-feedback.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-comments mr-3"></i> Feedback
        </a>
        <a href="a-reports.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-chart-line mr-3"></i> Reports
        </a>
        <a href="a-leaderboard.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-trophy mr-3"></i> Leaderboard
        </a>
        <a href="a-computer-control.php" class="flex items-center text-white font-medium mb-5 p-3 rounded bg-green-800">
            <i class="fas fa-desktop mr-3"></i> Computer Control
        </a>
        <a href="a-logout.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
    </nav>

    <div class="flex-1 p-6 space-y-6 ml-60">
        <div class="text-center text-2xl font-bold text-green-900">Admin Dashboard</div>

        <div class="grid grid-cols-3 gap-6">
            <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg text-center text-green-900">
                <i class="fas fa-user-graduate text-4xl mb-3 text-green-700"></i>
                <p class="text-lg">Total Students</p>
                <h2 class="text-2xl font-bold"><?php echo $students_count; ?></h2>
            </div>
            <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg text-center text-green-900">
                <i class="fas fa-desktop text-4xl mb-3 text-green-700"></i>
                <p class="text-lg">Active Sessions</p>
                <h2 class="text-2xl font-bold"><?php echo $current_sit_in; ?></h2>
            </div>
            <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg text-center text-green-900">
                <i class="fas fa-undo text-4xl mb-3 text-green-700"></i>
                <p class="text-lg">Total Sessions</p>
                <h2 class="text-2xl font-bold"><?php echo $total_sit_in; ?></h2>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg">
                <h3 class="text-xl font-bold text-green-900 mb-4">Create Announcement</h3>
                <form action="a-dashboard.php" method="post">
                    <textarea name="announcement" placeholder="Enter your announcement..." required class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                    <button type="submit" class="mt-3 px-6 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800">Post</button>
                </form>
                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                    <p class="text-green-700 mt-3">Announcement posted successfully.</p>
                <?php endif; ?>
            </div>

            <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg">
                <h3 class="text-xl font-bold text-green-900 mb-4">Posted Announcements</h3>
                <div class="space-y-4 max-h-72 overflow-y-auto">
                    <?php foreach ($announcements as $announcement): ?>
                        <p>
                            <strong class="block text-sm text-green-700"><?php echo htmlspecialchars($announcement['created_at']); ?></strong>
                            <?php echo htmlspecialchars($announcement['content']); ?>
                        </p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'common-modals.php'; ?>
</body>
</html>