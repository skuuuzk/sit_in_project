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
            <li><a href="a-students.php">Students</a></li>
            <li><a href="a-currents.php">Current Sit-in</a></li>
            <li><a href="a-vrecords.php">Visit Records</a></li>
            <li><a href="a-logout.php">Logout</a></li>
            <li><a href="#" onclick="openModal('searchModal')">Search</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <h1 style="text-align: center;">Current Sit-ins</h1>
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
                                <button type="submit" name="time_out">Time Out</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php include 'common-modals.php'; ?>
</body>
</html>
