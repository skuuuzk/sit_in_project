<?php
require_once('config/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

// Ensure the `points` column exists in the `users` table
$check_column_query = "SHOW COLUMNS FROM users LIKE 'points'";
$check_column_result = mysqli_query($conn, $check_column_query);
if (mysqli_num_rows($check_column_result) == 0) {
    $add_column_query = "ALTER TABLE users ADD COLUMN points INT DEFAULT 0";
    mysqli_query($conn, $add_column_query);
}

// Ensure the `session` column exists in the `users` table
$check_column_query = "SHOW COLUMNS FROM users LIKE 'session'";
$check_column_result = mysqli_query($conn, $check_column_query);
if (mysqli_num_rows($check_column_result) == 0) {
    $add_column_query = "ALTER TABLE users ADD COLUMN session INT DEFAULT 0";
    mysqli_query($conn, $add_column_query);
}

// Fetch leaderboard data
$leaderboard_query = "SELECT u.idno, u.firstname, u.lastname, u.year, u.profile_pic, SUM(r.points) AS total_points 
                      FROM reservations r 
                      JOIN users u ON r.idno = u.idno 
                      GROUP BY r.idno 
                      ORDER BY total_points DESC 
                      LIMIT 3"; // Limit to top 3 students for cards
$leaderboard_result = mysqli_query($conn, $leaderboard_query);
$leaders = mysqli_fetch_all($leaderboard_result, MYSQLI_ASSOC);
// Fetch admin username
$admin_id = $_SESSION['admin_id'];
$query = "SELECT username FROM admins WHERE id = ?";
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

$username = $admin['username'] ?? 'Admin';

// Handle points assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_points'])) {
    $idno = $_POST['idno'];

    // Fetch current points and sessions
    $query = "SELECT points, session FROM users WHERE idno = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $idno);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        $current_points = $user['points'];
        $current_sessions = $user['session'];

        // Add 1 point
        $current_points += 1;

        // Check if points reach 3
        if ($current_points >= 3) {
            $current_points = 0; // Reset points
            $current_sessions += 1; // Add 1 session
        }

        // Update points and sessions in the database
        $update_query = "UPDATE users SET points = ?, session = ? WHERE idno = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("iii", $current_points, $current_sessions, $idno);
        $stmt->execute();
        $stmt->close();

        // Redirect with success message
        header("Location: a-leaderboard.php?points_success=1");
        exit();
    } else {
        // Redirect with error message if user not found
        header("Location: a-leaderboard.php?points_error=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Leaderboard</title>
        <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="path/to/your/script.js" defer></script>
    <link rel="stylesheet" href="style.css"> 
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
           <!-- Dropdown CSS -->
    <style>
        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #f9f9f9;
            min-width: 200px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            z-index: 10;
        }

        .dropdown-content li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .dropdown-content li a {
            text-decoration: none;
            color: black;
        }

        .dropdown-content li a:hover {
            background-color: #ddd;
        }

        .dropdown-content.show {
            display: block;
        }
    </style>
    <script>
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const isVisible = dropdown.classList.contains('show');
            closeAllDropdowns(); // Close other dropdowns
            if (!isVisible) {
                dropdown.classList.add('show');
            }
        }

        function closeAllDropdowns() {
            const dropdowns = document.querySelectorAll('.dropdown-content');
            dropdowns.forEach(dropdown => dropdown.classList.remove('show'));
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.relative')) {
                closeAllDropdowns();
            }
        });
    </script>
    </head>
    <body class="bg-cover bg-center h-screen flex" style="background-image: url('img/5.jpg');">
    <nav class="w-60 bg-green-700 bg-opacity-60 text-green-900 p-5 rounded-r-2xl shadow-lg fixed top-0 left-0 h-full">
        <div class="logo text-center mb-6">
            <img src="img/ccs.png" alt="Logo" class="w-20 h-20 object-cover rounded-full border-2 border-green-800 mx-auto">
            <p class="mt-2 text-white font-bold"><?php echo htmlspecialchars($username); ?></p>
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

        <!-- Dropdown for View (clickable) -->
        <div class="relative">
                <a href="#" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700" onclick="toggleDropdown('viewDropdown'); return false;">
                    <i class="fas fa-eye mr-3"></i> View <i class="fas fa-caret-down ml-2"></i>
                </a>
                <ul id="viewDropdown" class="dropdown-content bg-green-200 text-green-900 w-full p-2 rounded-lg shadow-md">
                    <li><a href="a-currents.php" class="block p-3">Current Sit-in</a></li>
                    <li><a href="a-vrecords.php" class="block p-3">Visit Records</a></li>
                    <li><a href="a-feedback.php" class="block p-3">Feedback</a></li>
                    <li><a href="a-daily-analytics.php" class="block p-3">Daily Analytics</a></li>
                </ul>
            </div>

            <!-- Dropdown for Lab (clickable) -->
            <div class="relative">
                <a href="#" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700" onclick="toggleDropdown('labDropdown'); return false;">
                    <i class="fas fa-laptop mr-3"></i> Lab <i class="fas fa-caret-down ml-2"></i>
                </a>
                <ul id="labDropdown" class="dropdown-content bg-green-200 text-green-900 w-full p-2 rounded-lg shadow-md">
                    <li><a href="a-computer-control.php" class="block p-3">Computer Control</a></li>
                    <li><a href="a-leaderboard.php" class="block p-3">Leaderboard</a></li>
                    <li><a href="a-resources.php" class="block p-3">Resources</a></li>
                </ul>
            </div>
                
        <a href="a-reports.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-chart-line mr-3"></i> Reports
        </a>
        <a href="a-logout.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
    </nav>

        <div class="flex-1 p-6 ml-60 space-y-6">
            <div class="text-center text-2xl font-bold text-green-900">Student Leaderboard</div>

            <!-- Student Cards -->
            <div class="grid grid-cols-3 gap-6">
                <?php foreach ($leaders as $index => $leader): ?>
                    <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg text-center relative">
                        <?php if ($index == 0): ?>
                            <!-- First Place: Big Gold Trophy -->
                            <i class="fas fa-trophy text-yellow-400 text-5xl drop-shadow-md absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2"></i>
                        <?php elseif ($index == 1): ?>
                            <!-- Second Place: Silver Medal -->
                            <i class="fas fa-medal text-gray-400 text-5xl drop-shadow-md absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2"></i>
                        <?php elseif ($index == 2): ?>
                            <!-- Third Place: Bronze Award -->
                            <i class="fas fa-award text-yellow-700 text-5xl drop-shadow-md absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2"></i>
                        <?php endif; ?>
                        
                        <img src="<?php echo htmlspecialchars($leader['profile_pic'] ?? 'img/default.png'); ?>" alt="Profile" class="w-24 h-24 object-cover rounded-full border-2 border-green-800 mx-auto mt-6">
                        <h3 class="text-xl font-bold text-green-900 mt-4"><?php echo htmlspecialchars($leader['firstname'] . ' ' . $leader['lastname']); ?></h3>
                        <p class="text-sm text-gray-700"><?php echo htmlspecialchars($leader['year']); ?></p>
                        <p class="text-lg text-green-700 font-bold mt-2"><?php echo htmlspecialchars($leader['total_points']); ?> Points</p>
                        <p class="text-sm text-gray-700"><?php echo floor($leader['total_points'] / 3); ?> Sessions</p>
                    </div>
                <?php endforeach; ?>
            </div>


            <!-- Leaderboard Table -->
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
                                            <button type="submit" name="assign_points" class="px-4 py-2 bg-green-700 text-white rounded hover:bg-green-800">Add 1 Point</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (isset($_GET['points_success']) && $_GET['points_success'] == 1): ?>
                        <div id="toast-success" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-green-500 text-white px-6 py-3 rounded shadow-lg z-50">
                            Points assigned successfully!
                        </div>

                        <script>
                            // Auto-hide after 3 seconds
                            setTimeout(function() {
                                var toast = document.getElementById('toast-success');
                                if (toast) {
                                    toast.style.display = 'none';
                                }
                            }, 3000); // 3 seconds
                        </script>
                    <?php elseif (isset($_GET['points_error']) && $_GET['points_error'] == 1): ?>
                        <div id="toast-error" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-red-500 text-white px-6 py-3 rounded shadow-lg z-50">
                            Error: User not found!
                        </div>
                        <script>
                            setTimeout(function() {
                                var toast = document.getElementById('toast-error');
                                if (toast) {
                                    toast.style.display = 'none';
                                }
                            }, 3000); // 3 seconds
                        </script>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-center">No data available for the leaderboard.</p>
                <?php endif; ?>
            </div>
        </div>
    </body>
</html>