<?php
require_once('config/db.php'); // Assuming you have a file for database connection

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

// Fetch current sit-ins
$query = "SELECT r.id AS sit_id, r.user_id, CONCAT(u.firstname, ' ', u.lastname) AS student_name, r.purpose, r.lab, r.time_in, r.status FROM reservations r JOIN users u ON r.user_id = u.user_id WHERE r.status = 'approved' AND r.time_out IS NULL";
$result = mysqli_query($conn, $query);
$current_sit_ins = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Handle time out action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['time_out'])) {
    $sit_id = $_POST['sit_id'];
    $stmt = $conn->prepare("UPDATE reservations SET time_out = NOW(), status = 'completed' WHERE id = ?");
    $stmt->bind_param("i", $sit_id);
    $stmt->execute();
    $stmt->close();

    // Refresh the page to show the updated sit-ins
    header("Location: a-currents.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Sit-ins</title>
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
        button {
            padding: 5px 10px;
            background-color: #4d5572;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #3a4256;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h2>Admin Dashboard</h2>
        <ul>
            <li><a href="a-dashboard.php">Home</a></li>
            <li><a href="#" id="openSearch">Search</a></li>
            <li><a href="a-students.php">Students</a></li>
            <li><a href="a-currents.php">Current Sit-in</a></li>
            <li><a href="a-vrecords.php">Visit Records</a></li>
            <li><a href="a-logout.php">Logout</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <h1 style="text-align: center;">Current Sit-ins</h1>
        <table>
            <thead>
                <tr>
                    <th>SIT ID</th>
                    <th>ID Number</th>
                    <th>Name</th>
                    <th>Purpose</th>
                    <th>Sit-in Lab Room</th>
                    <th>Time-in</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($current_sit_ins as $sit_in): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sit_in['sit_id']); ?></td>
                        <td><?php echo htmlspecialchars($sit_in['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($sit_in['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($sit_in['purpose']); ?></td>
                        <td><?php echo htmlspecialchars($sit_in['lab']); ?></td>
                        <td><?php echo htmlspecialchars($sit_in['time_in']); ?></td>
                        <td><?php echo htmlspecialchars($sit_in['status']); ?></td>
                        <td>
                            <form action="a-currents.php" method="post">
                                <input type="hidden" name="sit_id" value="<?php echo htmlspecialchars($sit_in['sit_id']); ?>">
                                <button type="submit" name="time_out">Time Out</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
