<?php
require_once('config/db.php'); // Assuming you have a file for database connection

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

// Fetch student information
$query = "SELECT user_id, firstname, lastname, year, course, session FROM users";
$result = mysqli_query($conn, $query);
$students = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Handle reset all sessions action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_sessions'])) {
    $stmt = $conn->prepare("UPDATE users SET session = 30"); // Assuming 30 is the default session count
    $stmt->execute();
    $stmt->close();

    // Refresh the page to show the updated sessions
    header("Location: a-students.php");
    exit();
}

// Handle delete student action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_student'])) {
    $user_id = $_POST['user_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Refresh the page to show the updated student list
    header("Location: a-students.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List</title>
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

        .actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .actions button {
            padding: 10px 20px;
            background-color: #4d5572;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .actions button:hover {
            background-color: #3a4256;
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

        .action-buttons button {
            padding: 5px 10px;
            background-color: #4d5572;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 5px;
        }

        .action-buttons button:hover {
            background-color: #3a4256;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h2>Admin Dashboard</h2>
        <ul>
            <li><a href="a-dashboard.php"><i class="fas fa-user"></i>Home</a></li>
            <li><a href="a-students.php">Students</a></li>
            <li><a href="a-currents.php">Current Sit-in</a></li>
            <li><a href="a-vrecords.php">Visit Records</a></li>
            <li><a href="a-logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <h2 style="text-align: center;">STUDENT LIST</h2>
        <div class="actions">
            <button onclick="window.location.href='add_student.php'">Add Students</button>
            <form action="a-students.php" method="post" style="display:inline;">
                <button type="submit" name="reset_sessions">Reset All Sessions</button>
            </form>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID Number</th>
                    <th>Name</th>
                    <th>Year</th>
                    <th>Course</th>
                    <th>Remaining Sessions</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></td>
                        <td><?php echo htmlspecialchars($student['year']); ?></td>
                        <td><?php echo htmlspecialchars($student['course']); ?></td>
                        <td><?php echo htmlspecialchars($student['session']); ?></td>
                        <td class="action-buttons">
                            <button onclick="window.location.href='edit_student.php?id=<?php echo $student['user_id']; ?>'">Edit</button>
                            <form action="a-students.php" method="post" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($student['user_id']); ?>">
                                <button type="submit" name="delete_student">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
