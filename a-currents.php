<?php
require_once('config/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

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
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-cover bg-center h-screen flex" style="background-image: url('img/5.jpg');">
    <nav class="w-60 bg-green-700 bg-opacity-60 text-green-900 p-5 rounded-r-2xl shadow-lg">
        <div class="logo mb-5 text-center">
            <img src="img/ccs.png" alt="Logo" class="w-24 h-24 rounded-full border-2 border-green-900 mx-auto">
        </div>      
        <a href="a-dashboard.php" class="flex items-center text-lg mb-6 p-2 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-user mr-2"></i><span>Home</span>
        </a>
        <a href="#" onclick="openModal('searchModal')" class="flex items-center text-lg mb-6 p-2 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-search mr-2"></i> <span>Search</span>
        </a>
        <a href="a-students.php" class="flex items-center text-lg mb-6 p-2 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-users mr-2"></i> <span>Students</span>
        </a>
        <a href="a-currents.php" class="flex items-center text-lg mb-6 p-2 rounded bg-green-200 text-green-700 font-bold">
            <i class="fas fa-user-clock mr-2"></i> <span>Current Sit-in</span>
        </a>
        <a href="a-vrecords.php" class="flex items-center text-lg mb-6 p-2 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-book mr-2"></i> <span>Visit Records</span>
        </a>
        <a href="a-feedback.php" class="flex items-center text-lg mb-6 p-2 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-comments mr-2"></i> <span>Feedback</span>
        </a>
        <a href="a-reports.php" class="flex items-center text-lg mb-6 p-2 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-chart-line mr-2"></i> <span>Reports</span>
        </a>
        <a href="a-logout.php" class="flex items-center text-lg mb-6 p-2 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-sign-out-alt mr-2"></i> <span>Logout</span>
        </a>
    </nav>

    <div class="flex-1 p-6 space-y-6">
        <div class="text-center text-2xl font-bold text-green-900">Current Sit-in</div>
        <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg">
            <h3 class="text-xl font-bold text-green-900 mb-4">Active Sessions</h3>
            <table class="w-full border-collapse mt-4">
                <thead>
                    <tr class="bg-green-200 text-green-900">
                        <th class="border p-2">ID Number</th>
                        <th class="border p-2">Name</th>
                        <th class="border p-2">Purpose</th>
                        <th class="border p-2">Sit-in Lab Room</th>
                        <th class="border p-2">Time-in</th>
                        <th class="border p-2">Time-out</th>
                        <th class="border p-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($current_sit_ins as $sit_in): ?>
                        <tr class="bg-white bg-opacity-50">
                            <td class="border p-2"><?php echo htmlspecialchars($sit_in['idno']); ?></td>
                            <td class="border p-2"><?php echo htmlspecialchars($sit_in['student_name']); ?></td>
                            <td class="border p-2"><?php echo htmlspecialchars($sit_in['purpose']); ?></td>
                            <td class="border p-2"><?php echo htmlspecialchars($sit_in['lab']); ?></td>
                            <td class="border p-2"><?php echo htmlspecialchars($sit_in['time_in']); ?></td>
                            <td class="border p-2"><?php echo htmlspecialchars($sit_in['time_out'] ?? 'N/A'); ?></td>
                            <td class="border p-2">
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
