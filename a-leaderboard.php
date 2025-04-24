<?php
require_once('config/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

// Fetch leaderboard data
$leaderboard_query = "SELECT u.idno, u.firstname, u.lastname, SUM(r.points) AS total_points 
                      FROM reservations r 
                      JOIN users u ON r.idno = u.idno 
                      GROUP BY r.idno 
                      ORDER BY total_points DESC";
$leaderboard_result = mysqli_query($conn, $leaderboard_query);
$leaders = mysqli_fetch_all($leaderboard_result, MYSQLI_ASSOC);

// Handle points assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_points'])) {
    $idno = $_POST['idno'];
    $points = $_POST['points'];

    $stmt = $conn->prepare("UPDATE reservations SET points = points + ? WHERE idno = ?");
    $stmt->bind_param("ii", $points, $idno);
    $stmt->execute();
    $stmt->close();

    // Redirect with success message
    header("Location: a-leaderboard.php?points_success=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-cover bg-center h-screen flex" style="background-image: url('img/5.jpg');">
    <nav class="w-60 bg-green-700 bg-opacity-60 text-green-900 p-5 rounded-r-2xl shadow-lg">
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
        <a href="a-logout.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
    </nav>

    <div class="flex-1 p-6 space-y-6">
        <div class="text-center text-2xl font-bold text-green-900">Leaderboard</div>
        <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg">
            <h3 class="text-xl font-bold text-green-900 mb-4">Top Users</h3>
            <?php if (!empty($leaders)): ?>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="border-b p-3 text-green-900">Rank</th>
                            <th class="border-b p-3 text-green-900">Name</th>
                            <th class="border-b p-3 text-green-900">Total Points</th>
                            <th class="border-b p-3 text-green-900">Redeemable Sessions</th>
                            <th class="border-b p-3 text-green-900">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leaders as $index => $leader): ?>
                            <tr>
                                <td class="border-b p-3"><?php echo $index + 1; ?></td>
                                <td class="border-b p-3"><?php echo htmlspecialchars($leader['firstname'] . ' ' . $leader['lastname']); ?></td>
                                <td class="border-b p-3"><?php echo htmlspecialchars($leader['total_points']); ?></td>
                                <td class="border-b p-3"><?php echo floor($leader['total_points'] / 3); ?></td>
                                <td class="border-b p-3">
                                    <form method="post" class="flex items-center space-x-2">
                                        <input type="hidden" name="idno" value="<?php echo htmlspecialchars($leader['idno']); ?>">
                                        <input type="number" name="points" placeholder="Points" required class="w-20 p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <button type="submit" name="assign_points" class="px-4 py-2 bg-green-700 text-white rounded hover:bg-green-800">Assign</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center">No data available for the leaderboard.</p>
            <?php endif; ?>
        </div>

        <?php if (isset($_GET['points_success']) && $_GET['points_success'] == 1): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-lg mt-4">Points assigned successfully!</div>
        <?php endif; ?>
    </div>
</body>
</html>
