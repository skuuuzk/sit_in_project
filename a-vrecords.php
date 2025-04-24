<?php
require_once('config/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

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
</head>
<body class="bg-cover bg-center h-screen flex" style="background-image: url('img/5.jpg');">
    <nav class="w-60 bg-green-700 bg-opacity-60 text-green-900 p-5 rounded-r-2xl shadow-lg fixed top-0 left-0 h-full">
        <div class="logo text-center mb-6">
            <img src="img/ccs.png" alt="Logo" class="w-24 h-24 rounded-full border-2 border-green-900 mx-auto">
        </div>      
        <a href="a-dashboard.php" class="flex items-center text-lg mb-6 p-2 rounded hover:bg-green-200"><i class="fas fa-user mr-2"></i><span>Home</span></a>
        <a href="#" onclick="openModal('searchModal')" class="flex items-center text-lg mb-6 p-2 rounded hover:bg-green-200"><i class="fas fa-search mr-2"></i> <span>Search</span></a>
        <a href="a-students.php" class="flex items-center text-lg mb-6 p-2 rounded hover:bg-green-200"><i class="fas fa-users mr-2"></i> <span>Students</span></a>
        <a href="a-currents.php" class="flex items-center text-lg mb-6 p-2 rounded hover:bg-green-200"><i class="fas fa-user-clock mr-2"></i> <span>Current Sit-in</span></a>
        <a href="a-vrecords.php" class="flex items-center text-lg mb-6 p-2 rounded bg-green-200 font-bold"><i class="fas fa-book mr-2"></i> <span>Visit Records</span></a>
        <a href="a-feedback.php" class="flex items-center text-lg mb-6 p-2 rounded hover:bg-green-200"><i class="fas fa-comments mr-2"></i> <span>Feedback</span></a>
        <a href="a-reports.php" class="flex items-center text-lg mb-6 p-2 rounded hover:bg-green-200"><i class="fas fa-chart-line mr-2"></i> <span>Reports</span></a>
        <a href="a-logout.php" class="flex items-center text-lg mb-6 p-2 rounded hover:bg-green-200"><i class="fas fa-sign-out-alt mr-2"></i> <span>Logout</span></a>
    </nav>

    <div class="flex-1 p-6 ml-60 space-y-6">
        <div class="text-center text-2xl font-bold text-green-900">Visit Records</div>
        <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg">
            <h3 class="text-xl font-bold text-green-900 mb-4">Visit History</h3>
            <!-- Search Bar -->
            <form method="GET" action="a-vrecords.php" class="mb-6 text-center">
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