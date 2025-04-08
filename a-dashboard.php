<?php
require_once('config/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}
// Fetch statistics
$students_count_query = "SELECT COUNT(*) AS count FROM users";
$students_count = mysqli_fetch_assoc(mysqli_query($conn, $students_count_query))['count'];

$current_sit_in_query = "SELECT COUNT(*) AS count FROM reservations WHERE time_out IS NULL";
$current_sit_in = mysqli_fetch_assoc(mysqli_query($conn, $current_sit_in_query))['count'];

$total_sit_in_query = "SELECT COUNT(*) AS count FROM reservations";
$total_sit_in = mysqli_fetch_assoc(mysqli_query($conn, $total_sit_in_query))['count'];

// Fetch activity statistics for graph
$activity_data_query = "SELECT purpose, COUNT(*) AS count FROM reservations GROUP BY purpose";
$activity_data_result = mysqli_query($conn, $activity_data_query);
$purpose_stats = [];
while ($row = mysqli_fetch_assoc($activity_data_result)) {
    $purpose_stats[$row['purpose']] = $row['count'];
}

// Fetch announcements
$announcements_query = "SELECT title, content, created_at FROM announcement ORDER BY created_at DESC";
$announcements_result = mysqli_query($conn, $announcements_query);
$announcements = mysqli_fetch_all($announcements_result, MYSQLI_ASSOC);

// Handle announcement submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['announcement'])) {
    $announcement_content = trim($_POST['announcement']);
    $admin_id = $_SESSION['admin_id'];

    if (!empty($announcement_content)) {
        $stmt = $conn->prepare("INSERT INTO announcement (content, admin_id) VALUES (?, ?)");
        $stmt->bind_param("si", $announcement_content, $admin_id);
        $stmt->execute();
        $stmt->close();

        header("Location: a-dashboard.php?success=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #D1B8E1;
        }

        .navbar {
            background: #7F60A8;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .navbar ul {
            list-style: none;
            display: flex;
            gap: 15px;
        }

        .navbar ul li a {
            text-decoration: none;
            color: white;
            font-weight: bold;
        }

        .container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 20px;
            justify-content: center;
        }

        .top-section {
            display: flex;
            gap: 10px;
        }

        .box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 45%;
            border: 3px solid #929292; /* Purple Border */
        }

        .announcement textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
        }

        .announcement button {
            width: 100%;
            padding: 10px;
            background:rgb(187, 0, 255);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .announcement button:hover {
            background:rgb(85, 104, 123);
        }

        .announcement-list {
            max-height: 200px;
            overflow-y: auto;
            border-top: 1px solid #ccc;
            margin-top: 10px;
            padding-top: 10px;
        }

        .success-message {
            color: green;
            margin-top: 10px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 30%;
            border-radius: 8px;
        }

        .close {
            float: right;
            font-size: 28px;
            cursor: pointer;
        }

        button { cursor: pointer; }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 30%;
            border-radius: 8px;
        }

        .close {
            float: right;
            font-size: 28px;
            cursor: pointer;
        }

        .modal h2 {
            text-align: center;
            margin-bottom: 20px;
            background-color: #4d5572;
            color: white;
            padding: 10px;
            border-radius: 5px;
        }

        .modal label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        .modal input, .modal select, .modal button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .modal button {
            background-color: #4d5572;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 20px;
        }

        .modal button:hover {
            background-color: #3a4256;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h2>Admin Dashboard</h2>        
        <ul>
            <li><a href="a-dashboard.php">Home</a></li>
            <li><a href="a-students.php">Students</a></li>
            <li><a href="a-currents.php">Current Sit-in</a></li>
            <li><a href="a-vrecords.php">Visit Records</a></li>
            <li><a href="a-feedback.php">Feedback</a></li>
            <li><a href="a-reports.php">Reports</a></li>

            <li><a href="a-logout.php">Logout</a></li>
            <li><a href="#" onclick="openModal('searchModal')">Search</a></li>
        </ul>
    </nav>

    <div class="container">
    <div class="top-section">
        <!-- Statistics Section -->
        <div class="box stats">
            <h3>Statistics</h3>
            <h2><?php echo $current_sit_in; ?></h2>
            <p>Currently Sit-in Students</p>
            <h2><?php echo $students_count; ?></h2>
            <p>Registered Students</p>
            <canvas id="purposeChart" width="300" height="200"></canvas>
            <script>
                const ctx = document.getElementById('purposeChart').getContext('2d');
                const purposeChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: <?php echo json_encode(array_keys($purpose_stats)); ?>,
                        datasets: [{
                            label: 'No. of Students',
                            data: <?php echo json_encode(array_values($purpose_stats)); ?>,
                            backgroundColor: [
                                '#4d5572', '#6c757d', '#007bff', '#28a745', '#ffc107', '#dc3545'
                            ],
                            borderColor: '#fff',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        }
                    }
                });
            </script>
        </div>

        <!-- Announcement Section (moved beside stats) -->
        <div class="box announcement">
            <h3>Create Announcement</h3>
            <form action="a-dashboard.php" method="post">
                <textarea name="announcement" placeholder="Enter your announcement..." required></textarea>
                <button type="submit">Post</button>
            </form>
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <p class="success-message">Announcement posted successfully.</p>
            <?php endif; ?>

            <h4>Posted Announcements</h4>
            <div class="announcement-list">
                <?php foreach ($announcements as $announcement): ?>
                    <p>
                        <strong><?php echo htmlspecialchars($announcement['created_at']); ?></strong><br>
                        <?php echo htmlspecialchars($announcement['content']); ?>
                    </p>
                <?php endforeach; ?>
            </div>
        </div>
    </div> <!-- END of top-section -->
</div> <!-- END of container -->

    <?php include 'common-modals.php'; ?>
</body>
</html>