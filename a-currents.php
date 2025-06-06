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

// Handle sit-in approval
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_sit_in'])) {
    $idno = $_POST['idno'];
    $purpose = $_POST['purpose'];
    $lab = $_POST['lab'];
    $time_in = date('Y-m-d H:i:s'); // Current timestamp

    // Insert or update the reservation to make it active
    $stmt = $conn->prepare("INSERT INTO reservations (idno, purpose, lab, time_in, status) VALUES (?, ?, ?, ?, 'approved') 
                            ON DUPLICATE KEY UPDATE purpose = VALUES(purpose), lab = VALUES(lab), time_in = VALUES(time_in), status = 'approved'");
    $stmt->bind_param("ssss", $idno, $purpose, $lab, $time_in);
    $stmt->execute();
    $stmt->close();

    // Deduct one session from the student's available sessions
    $stmt = $conn->prepare("UPDATE users SET session = session - 1 WHERE idno = ? AND session > 0");
    $stmt->bind_param("s", $idno);
    $stmt->execute();
    $stmt->close();

    // Send notification to the student
    $notification = "Your sit-in reservation has been approved.";
    $stmt = $conn->prepare("INSERT INTO notifications (idno, message) VALUES (?, ?)");
    $stmt->bind_param("is", $idno, $notification);
    $stmt->execute();
    $stmt->close();

    // Redirect to Current Sit-ins page
    header("Location: a-currents.php");
    exit();
}

// Handle time-out action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['time_out'])) {
    $reservation_id = $_POST['reservation_id'];
    $time_out = date('Y-m-d H:i:s'); // Current timestamp

    // Update the reservation with the time_out value
    $stmt = $conn->prepare("UPDATE reservations SET time_out = ?, status = 'completed' WHERE id = ? AND time_out IS NULL");
    $stmt->bind_param("si", $time_out, $reservation_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to Current Sit-ins page
    header("Location: a-currents.php");
    exit();
}

// Fetch current sit-ins
$query = "SELECT r.id, r.idno, CONCAT(u.firstname, ' ', u.lastname) AS student_name, r.purpose, r.lab, r.time_in, r.time_out 
          FROM reservations r 
          JOIN users u ON r.idno = u.idno 
          WHERE r.time_out IS NULL AND r.status = 'approved'";
$result = mysqli_query($conn, $query);
$current_sit_ins = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Sit-in</title>
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

        /* Adjust the main content to avoid overlapping the navbar */
        .main-content {
            margin-left: 15rem; /* Match the width of the navbar */
            padding: 1.5rem;
        }

        /* Ensure the table and other content are responsive */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 0.75rem;
            text-align: left;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        th {
            background-color: #d1fae5;
            color: #065f46;
        }

        tr:nth-child(even) {
            background-color: rgba(209, 250, 229, 0.5);
        }

        tr:hover {
            background-color: #bbf7d0;
        }

        .bg-opacity-20 {
            background-color: rgba(255, 255, 255, 0.2);
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
        <div class="text-center text-2xl font-bold text-green-900">Current Sit-in</div>
        <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg">
            <h3 class="text-xl font-bold text-green-900 mb-4">Active Sessions</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID Number</th>
                        <th>Name</th>
                        <th>Purpose</th>
                        <th>Sit-in Lab Room</th>
                        <th>Time-in</th>
                        <th>Time-out</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($current_sit_ins as $sit_in): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sit_in['idno']); ?></td>
                            <td><?php echo htmlspecialchars($sit_in['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($sit_in['purpose']); ?></td>
                            <td><?php echo htmlspecialchars($sit_in['lab']); ?></td>
                            <td><?php echo htmlspecialchars($sit_in['time_in']); ?></td>
                            <td><?php echo htmlspecialchars($sit_in['time_out'] ?? 'N/A'); ?></td>
                            <td>
                                <form action="a-currents.php" method="post">
                                    <input type="hidden" name="reservation_id" value="<?php echo $sit_in['id']; ?>">
                                    <button type="submit" name="time_out" class="bg-green-700 text-white p-2 rounded hover:bg-green-800">Time Out</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'common-modals.php'; ?>
</body>
</html>
