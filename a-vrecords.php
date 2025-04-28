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

// Handle search query
$search_id = $_GET['search_id'] ?? null;

if ($search_id) {
    $query = "SELECT r.idno, CONCAT(u.firstname, ' ', u.lastname) AS student_name, r.purpose, r.lab, r.time_in, r.time_out, r.date 
              FROM reservations r 
              JOIN users u ON r.idno = u.idno 
              WHERE r.status = 'completed' AND r.idno = ? 
              ORDER BY r.date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $search_id);
} else {
    $query = "SELECT r.idno, CONCAT(u.firstname, ' ', u.lastname) AS student_name, r.purpose, r.lab, r.time_in, r.time_out, r.date 
              FROM reservations r 
              JOIN users u ON r.idno = u.idno 
              WHERE r.status = 'completed' 
              ORDER BY r.date DESC";
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();
$visit_records = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visit Records</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="path/to/your/script.js" defer></script>
    <link rel="stylesheet" href="style.css"> 
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>       <!-- Dropdown CSS -->
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
        <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg">
            <h3 class="text-xl font-bold text-green-900 mb-4">Visit History</h3>
            <!-- Search Bar -->
            <form method="GET" action="a-vrecords.php" class="mb-6 flex items-center justify-center">
                <a href="a-vrecords.php" class="mr-2 text-green-700 hover:text-green-900">
                    <i class="fas fa-arrow-left text-2xl"></i>
                </a>
                <input type="text" name="search_id" placeholder="Enter ID Number" value="<?php echo htmlspecialchars($search_id ?? ''); ?>" 
                       class="p-2 border border-gray-300 rounded w-3/4">
                <button type="submit" class="p-2 bg-green-500 text-white rounded ml-2 hover:bg-green-600">
                    Search
                </button>
            </form>

            <!-- Table -->
            <table class="w-full border-collapse mt-4">
                <thead>
                    <tr>
                        <th class="border p-2 bg-gray-200">ID Number</th>
                        <th class="border p-2 bg-gray-200">Name</th>
                        <th class="border p-2 bg-gray-200">Purpose</th>
                        <th class="border p-2 bg-gray-200">Lab Room</th>
                        <th class="border p-2 bg-gray-200">Time-in</th>
                        <th class="border p-2 bg-gray-200">Time-out</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($visit_records)): ?>
                        <?php foreach ($visit_records as $record): ?>
                            <tr>
                                <td class="border p-2"><?php echo htmlspecialchars($record['idno']); ?></td>
                                <td class="border p-2"><?php echo htmlspecialchars($record['student_name']); ?></td>
                                <td class="border p-2"><?php echo htmlspecialchars($record['purpose']); ?></td>
                                <td class="border p-2"><?php echo htmlspecialchars($record['lab']); ?></td>
                                <td class="border p-2"><?php echo htmlspecialchars($record['time_in']); ?></td>
                                <td class="border p-2"><?php echo htmlspecialchars($record['time_out']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="border p-2 text-center">No visit records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include 'common-modals.php'; ?>

</body>
</html>