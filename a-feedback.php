<?php
require_once('config/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

// Fetch feedback records
$query = "SELECT r.idno, CONCAT(u.firstname, ' ', u.lastname) AS student_name, r.time_in, r.feedback, r.feedback_timestamp 
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
    <title>Student Feedback</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #333;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: whitesmoke;
            color: #333;
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
            <li><a href="a-logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <h2 style="text-align: center;">Student Feedback</h2>
        <table>
            <thead>
                <tr>
                    <th>ID Number</th>
                    <th>Name</th>
                    <th>Sit-in Date/Time</th>
                    <th>Feedback</th>
                    <th>Feedback Submitted At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($feedback_records)): ?>
                    <?php foreach ($feedback_records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['idno']); ?></td>
                            <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['time_in']); ?></td>
                            <td><?php echo htmlspecialchars($record['feedback']); ?></td>
                            <td><?php echo htmlspecialchars($record['feedback_timestamp']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No feedback records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
