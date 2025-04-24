<?php
require_once('config/db.php'); // Assuming you have a file for database connection

// Ensure user is logged in
if (!isset($_SESSION['idno'])) {
    header("Location: login.php");
    exit();
}

$idno = $_SESSION['idno'];

// Fetch user details
$query = "SELECT firstname, lastname, session AS remaining_sessions, profile_pic FROM users WHERE idno = '$idno'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$student_name = $user['firstname'] . " " . $user['lastname'];
$remaining_sessions = $user['remaining_sessions'];
$profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'img/default.png';

// Handle reservation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purpose'], $_POST['lab'], $_POST['time_in'], $_POST['date'])) {
    $purpose = trim($_POST['purpose']);
    $lab = trim($_POST['lab']);
    $time_in = trim($_POST['time_in']);
    $date = trim($_POST['date']);

    if (!empty($purpose) && !empty($lab) && !empty($time_in) && !empty($date)) {
        $stmt = $conn->prepare("INSERT INTO reservations (idno, purpose, lab, time_in, date, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("issss", $idno, $purpose, $lab, $time_in, $date);
        $stmt->close();

        // Redirect with success message
        header("Location: reservation.php?status=success");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-cover bg-center h-screen flex" style="background-image: url('img/5.jpg');">
    <nav class="w-60 bg-green-700 bg-opacity-60 text-green-900 p-5 rounded-r-2xl shadow-lg fixed top-0 left-0 h-full">
        <div class="logo text-center mb-6">
            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" class="w-24 h-24 object-cover rounded-full border-2 border-green-800 mx-auto">
            <p class="mt-2 text-white font-bold"><?php echo htmlspecialchars($student_name); ?></p>
            <p class="text-sm text-gray-200"><strong>Session:</strong> <?php echo htmlspecialchars($remaining_sessions); ?></p>
        </div>
        <a href="dashboard.php" class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800">
            <i class="fas fa-user mr-3"></i> Home
        </a>
        <a href="edit.php" class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800">
            <i class="fas fa-edit mr-3"></i> Profile
        </a>
        <a href="reservation.php" class="flex items-center text-white font-medium mb-5 p-3 rounded bg-green-800">
            <i class="fas fa-calendar-check mr-3"></i> Reservation
        </a>
        <a href="history.php" class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800">
            <i class="fas fa-history mr-3"></i> History
        </a>
        <a href="notification.php" class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800">
            <i class="fas fa-bell mr-3"></i> Notifications
        </a>
        <a href="logout.php" class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800">
            <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
    </nav>

    <div class="flex-1 p-6 ml-60 space-y-6">
        <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg">
            <h2 class="text-xl font-bold text-green-900 mb-4">Make a Reservation</h2>
            <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                <p class="text-green-700 text-center">Reservation submitted successfully!</p>
            <?php endif; ?>
            <form method="POST" action="reservation.php" class="space-y-4">
                <div>
                    <label for="idno" class="block font-bold text-green-900">ID Number:</label>
                    <input type="text" id="idno" name="idno" value="<?php echo htmlspecialchars($idno); ?>" class="w-full p-2 border rounded bg-gray-100" readonly>
                </div>
                <div>
                    <label for="student_name" class="block font-bold text-green-900">Student Name:</label>
                    <input type="text" id="student_name" name="student_name" value="<?php echo htmlspecialchars($student_name); ?>" class="w-full p-2 border rounded bg-gray-100" readonly>
                </div>
                <div>
                    <label for="remaining_sessions" class="block font-bold text-green-900">Remaining Sessions:</label>
                    <input type="text" id="remaining_sessions" name="remaining_sessions" value="<?php echo htmlspecialchars($remaining_sessions); ?>" class="w-full p-2 border rounded bg-gray-100" readonly>
                </div>
                <div>
                    <label for="purpose" class="block font-bold text-green-900">Purpose:</label>
                    <textarea id="purpose" name="purpose" rows="3" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required></textarea>
                </div>
                <div>
                    <label for="lab" class="block font-bold text-green-900">Laboratory:</label>
                    <select id="lab" name="lab" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
                        <option value="">Select Laboratory</option>
                        <option value="Lab 1">Lab 1</option>
                        <option value="Lab 2">Lab 2</option>
                        <option value="Lab 3">Lab 3</option>
                    </select>
                </div>
                <div>
                    <label for="time_in" class="block font-bold text-green-900">Time In:</label>
                    <input type="time" id="time_in" name="time_in" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
                <div>
                    <label for="date" class="block font-bold text-green-900">Date:</label>
                    <input type="date" id="date" name="date" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
                <button type="submit" class="w-full bg-green-700 text-white p-2 rounded hover:bg-green-800">Submit Reservation</button>
            </form>
        </div>
    </div>
</body>
</html>
