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

// Handle add student action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $year = $_POST['year'];
    $course = $_POST['course'];
    $session = 30; // Default session count

    $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, year, course, session) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $firstname, $lastname, $year, $course, $session);
    $stmt->execute();
    $stmt->close();

    // Refresh the page to show the updated student list
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
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        input, select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
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

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
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
        button:hover {
            background-color:rgb(74, 137, 33);
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
        <h2 style="text-align: center;">STUDENT LIST</h2>
        <div class="actions">
            <button onclick="openModal('addStudentModal')">Add Students</button>
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

    <div id="addStudentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addStudentModal')">&times;</span>
            <h2 style="text-align: center; color: #333;">Add Student</h2>
            <form action="a-students.php" method="post">
                <label for="firstname">First Name:</label>
                <input type="text" id="firstname" name="firstname" required>
                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" name="lastname" required>
                <label for="year">Year:</label>
                    <select id="year" name="year" required>
                        <option value="">Select Year</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                    </select>
                </label>
                <label for="course">Course:</label>
                    <select id="course" name="course" required>
                        <option value="">Select Course</option>
                        <option value="BSIT">Bachelor of Science in Information Technology</option>
                        <option value="BSCS">Bachelor of Science in Computer Science</option>
                        <option value="BSECE">Bachelor of Science in Electronics Engineering</option>
                        <option value="BSCE">Bachelor of Science in Civil Engineering</option>
                        <option value="BSME">Bachelor of Science in Mechanical Engineering</option>
                        <option value="BSEE">Bachelor of Science in Electrical Engineering</option>
                        <option value="BSBA">Bachelor of Science in Business Administration</option>
                        <option value="BSA">Bachelor of Science in Accountancy</option>
                        <option value="BSHM">Bachelor of Science in Hospitality Management</option>
                        <option value="BSTM">Bachelor of Science in Tourism Management</option>
                        <option value="BSN">Bachelor of Science in Nursing</option>
                        <option value="BSED">Bachelor of Secondary Education</option>
                        <option value="BEED">Bachelor of Elementary Education</option>
                        <option value="BSPSY">Bachelor of Science in Psychology</option>
                    </select>
                </label>
                <button type="submit" name="add_student" style="            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            margin-top: 15px;
            cursor: pointer;">Add Student</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).style.display = 'block';
        }
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }
    </script>
</body>
</html>
