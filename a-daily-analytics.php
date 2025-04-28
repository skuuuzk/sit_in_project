<?php
require_once('config/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

// Fetch admin username
$admin_id = $_SESSION['admin_id'];
$query = "SELECT username FROM admins WHERE id = ?";
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

$username = $admin['username'] ?? 'Admin';

// Fetch daily statistics
$today = date('Y-m-d');

// Total sit-ins today
$query = "SELECT COUNT(*) AS total_sit_ins FROM reservations WHERE DATE(time_in) = ? AND status = 'completed'";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
$total_sit_ins = $result->fetch_assoc()['total_sit_ins'] ?? 0;
$stmt->close();

// Total feedback received today
$query = "SELECT COUNT(*) AS total_feedback FROM reservations WHERE DATE(feedback_timestamp) = ? AND feedback IS NOT NULL";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
$total_feedback = $result->fetch_assoc()['total_feedback'] ?? 0;
$stmt->close();

// Average rating today
$query = "SELECT AVG(rating) AS average_rating FROM reservations WHERE DATE(feedback_timestamp) = ? AND rating IS NOT NULL";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
$average_rating = round($result->fetch_assoc()['average_rating'] ?? 0, 2);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="path/to/your/script.js" defer></script>
    <link rel="stylesheet" href="style.css"> 
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <style>
        .main-content {
            margin-left: 15rem; /* Match the width of the navbar */
            padding: 1.5rem;
        }

        .stat-card {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-card h2 {
            font-size: 2rem;
            font-weight: bold;
            color: #065f46;
        }

        .stat-card p {
            font-size: 1.25rem;
            color: #1a202c;
        }
        <!-- Dropdown CSS -->
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
<body class="bg-cover bg-center h-screen" style="background-image: url('img/5.jpg');">
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
    <div class="main-content">
        <div class="text-center text-2xl font-bold text-green-900 mb-6">Daily Analytics</div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="stat-card">
                <h2><?php echo $total_sit_ins; ?></h2>
                <p>Total Sit-ins Today</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $total_feedback; ?></h2>
                <p>Feedback Received Today</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $average_rating; ?></h2>
                <p>Average Rating Today</p>
            </div>
        </div>
    </div>
</body>
</html>
