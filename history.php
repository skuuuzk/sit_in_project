<?php
require_once('config/db.php'); // Assuming you have a file for database connection

// Ensure user is logged in
if (!isset($_SESSION['idno'])) {
    header("Location: login.php");
    exit();
}

$idno = $_SESSION['idno'];

// Fetch user information
$query = "SELECT firstname, lastname, session AS remaining_sessions, profile_pic FROM users WHERE idno = '$idno'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$student_name = $user['firstname'] . " " . $user['lastname'];
$remaining_sessions = $user['remaining_sessions'];
$profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'img/default.png';

// Fetch reservation history
$query = "SELECT r.id, r.purpose, r.lab, r.time_in, r.time_out, r.feedback 
          FROM reservations r 
          WHERE r.idno = '$idno' AND r.status = 'completed' 
          ORDER BY r.time_in DESC";
$result = mysqli_query($conn, $query);

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['feedback'], $_POST['reservation_id'])) {
    $feedback = trim($_POST['feedback']);
    $reservation_id = $_POST['reservation_id'];
    $rating = $_POST['rating'];

    if (!empty($feedback)) {
        $stmt = $conn->prepare("UPDATE reservations SET feedback = ?, rating = ? WHERE id = ? AND idno = ?");
        $stmt->bind_param("sisi", $feedback, $rating, $reservation_id, $idno);
        $stmt->execute();
        $stmt->close();

        // Refresh the page to show the updated feedback
        header("Location: history.php?feedback_status=success");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Information</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
        <a href="reservation.php" class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800">
            <i class="fas fa-calendar-check mr-3"></i> Reservation
        </a>
        <a href="history.php" class="flex items-center text-white font-medium mb-5 p-3 rounded bg-green-800">
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
            <h2 class="text-xl font-bold text-green-900 mb-4">History</h2>
            <?php if (isset($_GET['feedback_status']) && $_GET['feedback_status'] === 'success'): ?>
                <p class="text-green-700 text-center">Feedback submitted successfully!</p>
            <?php endif; ?>
            <table class="w-full border-collapse bg-white bg-opacity-50 rounded-lg shadow-lg">
                <thead>
                    <tr class="bg-green-700 text-white">
                        <th class="border p-2">Date</th>
                        <th class="border p-2">Purpose</th>
                        <th class="border p-2">Laboratory</th>
                        <th class="border p-2">Login</th>
                        <th class="border p-2">Logout</th>
                        <th class="border p-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="bg-white bg-opacity-50">
                            <td class="border p-2"><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['time_in']))); ?></td>
                            <td class="border p-2"><?php echo htmlspecialchars($row['purpose']); ?></td>
                            <td class="border p-2"><?php echo htmlspecialchars($row['lab']); ?></td>
                            <td class="border p-2"><?php echo htmlspecialchars($row['time_in']); ?></td>
                            <td class="border p-2"><?php echo htmlspecialchars($row['time_out'] ?? 'N/A'); ?></td>
                            <td class="border p-2">
                                <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 feedback-btn" data-reservation-id="<?php echo $row['id']; ?>">
                                    <i class="fas fa-comment-dots"></i> Feedback
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Feedback Modal -->
    <div id="feedbackModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl" onclick="document.getElementById('feedbackModal').classList.add('hidden')">&times;</button>
            <h3 class="text-xl font-bold text-green-900 mb-4">Submit Feedback</h3>
            <form method="POST" action="history.php" class="space-y-4">
                <div class="flex justify-center space-x-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fa fa-star text-gray-300 cursor-pointer text-2xl" data-value="<?php echo $i; ?>"></i>
                    <?php endfor; ?>
                </div>
                <textarea name="feedback" rows="4" placeholder="Add your comments here..." class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required></textarea>
                <input type="hidden" name="reservation_id" id="reservationId">
                <input type="hidden" name="rating" id="rating">
                <button type="submit" class="w-full bg-green-700 text-white p-2 rounded hover:bg-green-800">Submit</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('feedbackModal');
        const feedbackBtns = document.querySelectorAll('.feedback-btn');
        const stars = document.querySelectorAll('.fa-star');
        let selectedRating = 0;

        feedbackBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const reservationId = btn.getAttribute('data-reservation-id');
                document.getElementById('reservationId').value = reservationId;
                modal.classList.remove('hidden');
                selectedRating = 0;
                document.getElementById('rating').value = '';
                stars.forEach(star => star.classList.remove('text-yellow-500'));
            });
        });

        stars.forEach(star => {
            star.addEventListener('mouseover', () => {
                const hoverValue = star.getAttribute('data-value');
                stars.forEach(s => s.classList.remove('text-yellow-500'));
                for (let i = 0; i < hoverValue; i++) {
                    stars[i].classList.add('text-yellow-500');
                }
            });

            star.addEventListener('click', () => {
                selectedRating = star.getAttribute('data-value');
                document.getElementById('rating').value = selectedRating;
                stars.forEach(s => s.classList.remove('text-yellow-500'));
                for (let i = 0; i < selectedRating; i++) {
                    stars[i].classList.add('text-yellow-500');
                }
            });
        });
    </script>
</body>
</html>
