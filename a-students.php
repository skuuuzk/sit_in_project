<?php
require_once('config/db.php'); // Assuming you have a file for database connection

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

// Fetch student information
$query = "SELECT idno, firstname, lastname, year, course, session, username, email FROM users";
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
    $idno = $_POST['idno'];
    $firstname = $_POST['firstname'];
    $midname = $_POST['midname'];
    $lastname = $_POST['lastname'];
    $year = $_POST['year'];
    $course = $_POST['course'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $session = 30; // Default session count

    // Correct SQL and correct order of values
    $stmt = $conn->prepare("INSERT INTO users (idno, firstname, midname, lastname, year, course, username, password, email, session) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssi", $idno, $firstname, $midname, $lastname, $year, $course, $username, $password, $email, $session);
    $stmt->execute();
    $stmt->close();

    header("Location: a-students.php");
    exit();
}

// Handle delete student action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_student'])) {
    $idno = $_POST['idno'];
    $stmt = $conn->prepare("DELETE FROM users WHERE idno = ?");
    $stmt->bind_param("i", $idno);
    $stmt->execute();
    $stmt->close();

    // Refresh the page to show the updated student list
    header("Location: a-students.php");
    exit();
}

// Handle edit student action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_student'])) {
    $idno = $_POST['idno'];
    $firstname = $_POST['firstname'];
    $midname = $_POST['midname'];
    $lastname = $_POST['lastname'];
    $year = $_POST['year'];
    $course = $_POST['course'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $stmt = $conn->prepare("UPDATE users SET firstname = ?, midname = ?, lastname = ?, year = ?, course = ?, username = ?, email = ?, password = ? WHERE idno = ?");
        $stmt->bind_param("ssssssssi", $firstname, $midname, $lastname, $year, $course, $username, $email, $password, $idno);
    } else {
        $stmt = $conn->prepare("UPDATE users SET firstname = ?, midname = ?, lastname = ?, year = ?, course = ?, username = ?, email = ? WHERE idno = ?");
        $stmt->bind_param("sssssssi", $firstname, $midname, $lastname, $year, $course, $username, $email, $idno);
    }
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-image: url(img/5.jpg); /* Background image */
            background-size: cover; /* Cover the entire viewport */
            display: flex;
        }

        .nav-container {
            width: 240px;
            background: rgba(255, 255, 255, 0.1); /* Transparent background */
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Soft shadow */
            backdrop-filter: blur(1px); /* Frosted glass effect */
            background-color:rgba(119, 152, 95, 0.54);
            color:rgb(11, 27, 3);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 10px 20px;
            border-radius: 0 20px 20px 0;
            justify-content: stretch;
        }

        .nav-container a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color:rgb(1, 23, 13);
            font-size: 16px;
            margin: 23.5px 0;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .nav-container a i {
            margin-right: 10px;
            font-size: 18px;
        }

        .nav-container a:hover {
            background-color:#DEE9DC;
            color: seagreen;       
        }

        .nav-container a.active {
            font-weight: bold;
            background-color: #BACEAB;
        }

        .logo {
            margin: 25px auto;
            text-align: center;
        }

        .logo img {
            width: 70px;
            height: 70px; /* Set height to make it circular */
            object-fit: cover; /* Ensure the image covers the area */
            border-radius: 50%;
            border: 2px solid #475E53; /* Border around the image */
        }
        .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #4d5572; /* Background color */
            color: white; /* Text color */
            padding: 10px 0;
            text-align: center;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Add shadow for better visibility */
        }

        .container {
            flex-direction: column;
            gap: 20px;
            padding: 50px;
            justify-content: center;
            position: relative;
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
        z-index: 999; 
        left: 0;
        top: 0;
        width: 100%; 
        height: 100%; 
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5); 
    }

    .modal-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 30px;
        border: 1px solid #ccc;
        width: 450px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }

    .modal-content h2 {
        margin-top: 0;
        font-size: 24px;
        text-align: center;
    }

    .modal-content label {
        display: block;
        margin-top: 12px;
        font-weight: bold;
    }

    .modal-content input,
    .modal-content select {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    .modal-content button[type="submit"] {
        width: 100%;
        padding: 10px;
        background-color: #007bff;
        border: none;
        color: white;
        font-size: 16px;
        border-radius: 5px;
        margin-top: 20px;
        cursor: pointer;
    }

    .modal-content button[type="submit"]:hover {
        background-color: #0056b3;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover {
        color: #000;
    }
    </style>
</head>
<body>
<nav class="nav-container"> 
        <div class="logo">
            <img src="img/ccs.png" alt="Logo" style="width: 100px; height: auto; margin-bottom: 20px;">
        </div>      
            <a href="a-dashboard.php"><i class="fas fa-user"></i><span>Home</span></a>
            <a href="#" onclick="openModal('searchModal')"><i class="fas fa-search"></i> <span>Search</span></a>
            <a href="a-students.php"class="active"><i class="fas fa-users"></i> <span>Students</span></a>
            <a href="a-currents.php"><i class="fas fa-user-clock"></i> <span>Current Sit-in</span></a>
            <a href="a-vrecords.php"><i class="fas fa-book"></i> <span>Visit Records</span></a>
            <a href="a-feedback.php"><i class="fas fa-comments"></i> <span>Feedback</span></a>
            <a href="a-reports.php"><i class="fas fa-chart-line"></i> <span>Reports</span></a>
            <a href="a-logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
    </nav>

    <div class="container">
        <div class="header-container">
            <header>
                <h1 >STUDENT LIST</h1>
            </header>
        </div>
        <div class="student-container" style="border: 2px solid #ccc; padding: 20px; border-radius: 10px; box-shadow: 2px 2px 8px rgba(0,0,0,0.1); background-color: #f9f9f9; margin: 20px;">
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
                    <td><?php echo htmlspecialchars($student['idno']); ?></td>
                    <td><?php echo htmlspecialchars($student['firstname'] . ' '  . $student['lastname']); ?></td>
                    <td><?php echo htmlspecialchars($student['year']); ?></td>
                    <td><?php echo htmlspecialchars($student['course']); ?></td>
                    <td><?php echo htmlspecialchars($student['session']); ?></td>
                    <td class="action-buttons">
                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($student)); ?>)">Edit</button>
                        <form action="a-students.php" method="post" style="display:inline;">
                            <input type="hidden" name="idno" value="<?php echo htmlspecialchars($student['idno']); ?>">
                            <button type="submit" name="delete_student">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

 <!-- Your Add Student Modal -->
<div class="addstudent-container">
    <div id="addStudentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addStudentModal')">&times;</span>
            <h2>Add Student</h2>
            <form action="a-students.php" method="post">
                <label for="idno">ID Number:</label>
                <input type="text" id="idno" name="idno" required>

                <label for="firstname">First Name:</label>
                <input type="text" id="firstname" name="firstname" required>

                <label for="midname">Middle Name:</label>
                <input type="text" id="midname" name="midname" required>

                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" name="lastname" required>

                <label for="year">Year:</label>
                <select id="year" name="year" required>
                    <option value="">Select Year</option>
                    <option value="1">1st</option>
                    <option value="2">2nd</option>
                    <option value="3">3rd</option>
                    <option value="4">4th</option>
                </select>

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

                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <button type="submit" name="add_student">Add Student</button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div id="editStudentModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editStudentModal')">&times;</span>
        <h2>Edit Student</h2>
        <form action="a-students.php" method="post">
            <input type="hidden" id="edit-idno" name="idno">

            <label for="edit-firstname">First Name:</label>
            <input type="text" id="edit-firstname" name="firstname" required>

            <label for="edit-midname">Middle Name:</label>
            <input type="text" id="edit-midname" name="midname" required>

            <label for="edit-lastname">Last Name:</label>
            <input type="text" id="edit-lastname" name="lastname" required>

            <label for="edit-year">Year:</label>
            <select id="edit-year" name="year" required>
                <option value="">Select Year</option>
                <option value="1">1st</option>
                <option value="2">2nd</option>
                <option value="3">3rd</option>
                <option value="4">4th</option>
            </select>

            <label for="edit-course">Course:</label>
            <select id="edit-course" name="course" required>
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

            <label for="edit-username">Username:</label>
            <input type="text" id="edit-username" name="username" required>

            <label for="edit-email">Email:</label>
            <input type="email" id="edit-email" name="email" required>

            <label for="edit-password">Password (leave blank to keep current):</label>
            <input type="password" id="edit-password" name="password">

            <button type="submit" name="edit_student">Save Changes</button>
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

    function openEditModal(student) {
        document.getElementById('edit-idno').value = student.idno;
        document.getElementById('edit-firstname').value = student.firstname;
        document.getElementById('edit-midname').value = student.midname || '';
        document.getElementById('edit-lastname').value = student.lastname;
        document.getElementById('edit-year').value = student.year;
        document.getElementById('edit-course').value = student.course;
        document.getElementById('edit-username').value = student.username || '';
        document.getElementById('edit-email').value = student.email || '';
        document.getElementById('edit-password').value = ''; // Leave blank for security
        document.getElementById('editStudentModal').style.display = 'block';
    }

    // Optional: close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById("editStudentModal");
        if (event.target === modal) {
            closeModal("editStudentModal");
        }
    }
</script>
</body>
</html>
