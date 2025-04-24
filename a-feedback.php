<?php
require_once('config/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

// Fetch feedback records
$query = "SELECT r.idno, CONCAT(u.firstname, ' ', u.lastname) AS student_name, r.time_in, r.feedback, r.feedback_timestamp, r.rating 
          FROM reservations r 
          JOIN users u ON r.idno = u.idno 
          WHERE r.feedback IS NOT NULL 
          ORDER BY r.feedback_timestamp DESC";
$result = mysqli_query($conn, $query);
$feedback_records = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-cover bg-center h-screen flex" style="background-image: url('img/5.jpg');">
    <nav class="w-60 bg-green-700 bg-opacity-60 text-green-900 p-5 rounded-r-2xl shadow-lg">
        <div class="logo text-center mb-5">
            <img src="img/ccs.png" alt="Logo" class="w-24 h-auto mx-auto mb-5 rounded-full border-2 border-green-900">
        </div>      
        <a href="a-dashboard.php" class="flex items-center text-green-900 text-lg mb-6 p-2 rounded hover:bg-green-200 transition duration-300"><i class="fas fa-user mr-2"></i><span>Home</span></a>
        <a href="#" onclick="openModal('searchModal')" class="flex items-center text-green-900 text-lg mb-6 p-2 rounded hover:bg-green-200 transition duration-300"><i class="fas fa-search mr-2"></i> <span>Search</span></a>
        <a href="a-students.php" class="flex items-center text-green-900 text-lg mb-6 p-2 rounded hover:bg-green-200 transition duration-300"><i class="fas fa-users mr-2"></i> <span>Students</span></a>
        <a href="a-currents.php" class="flex items-center text-green-900 text-lg mb-6 p-2 rounded hover:bg-green-200 transition duration-300"><i class="fas fa-user-clock mr-2"></i> <span>Current Sit-in</span></a>
        <a href="a-vrecords.php" class="flex items-center text-green-900 text-lg mb-6 p-2 rounded hover:bg-green-200 transition duration-300"><i class="fas fa-book mr-2"></i> <span>Visit Records</span></a>
        <a href="a-feedback.php" class="flex items-center text-green-900 text-lg mb-6 p-2 rounded bg-green-200 font-bold"><i class="fas fa-comments mr-2"></i> <span>Feedback</span></a>
        <a href="a-reports.php" class="flex items-center text-green-900 text-lg mb-6 p-2 rounded hover:bg-green-200 transition duration-300"><i class="fas fa-chart-line mr-2"></i> <span>Reports</span></a>
        <a href="a-logout.php" class="flex items-center text-green-900 text-lg mb-6 p-2 rounded hover:bg-green-200 transition duration-300"><i class="fas fa-sign-out-alt mr-2"></i> <span>Logout</span></a>
    </nav>

    <div class="flex-1 p-6 space-y-6">
        <div class="text-center text-2xl font-bold text-green-900">Feedback</div>
        <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg">
            <h3 class="text-xl font-bold text-green-900 mb-4">User Feedback</h3>
            <table class="w-full border-collapse mt-5">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border p-2">ID Number</th>
                        <th class="border p-2">Name</th>
                        <th class="border p-2">Sit-in Date/Time</th>
                        <th class="border p-2">Feedback</th>
                        <th class="border p-2">Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($feedback_records)): ?>
                        <?php foreach ($feedback_records as $record): ?>
                            <tr class="bg-white bg-opacity-50">
                                <td class="border p-2"><?php echo htmlspecialchars($record['idno']); ?></td>
                                <td class="border p-2"><?php echo htmlspecialchars($record['student_name']); ?></td>
                                <td class="border p-2"><?php echo htmlspecialchars($record['time_in']); ?></td>
                                <td class="border p-2"><?php echo nl2br(htmlspecialchars($record['feedback'])); ?></td>
                                <td class="border p-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa fa-star <?php echo $i <= $record['rating'] ? 'text-yellow-500' : 'text-gray-300'; ?> text-lg mx-0.5"></i>
                                    <?php endfor; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center p-2">No feedback records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include 'common-modals.php'; ?>
</body>
</html>
