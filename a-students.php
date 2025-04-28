<?php
require_once('config/db.php'); // Assuming you have a file for database connection

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

// Fetch admin username
$admin_id = $_SESSION['admin_id'];
$query = "SELECT username FROM admins WHERE id = ?";
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

$username = $admin['username'] ?? 'Admin';

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
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $email = $_POST['email'];
    $session = 30; // Default session count

    $stmt = $conn->prepare("INSERT INTO users (idno, firstname, midname, lastname, year, course, username, password, email, session) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssi", $idno, $firstname, $midname, $lastname, $year, $course, $username, $password, $email, $session);
    $stmt->execute();
    $stmt->close();

    // Redirect with success message
    header("Location: a-students.php?success=1");
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
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash the new password
        $stmt = $conn->prepare("UPDATE users SET firstname = ?, midname = ?, lastname = ?, year = ?, course = ?, username = ?, email = ?, password = ? WHERE idno = ?");
        $stmt->bind_param("ssssssssi", $firstname, $midname, $lastname, $year, $course, $username, $email, $hashed_password, $idno);
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
    <title>Students</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="path/to/your/script.js" defer></script>
    <link rel="stylesheet" href="style.css"> 
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
       <!-- Dropdown CSS -->
       <style>
        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #f9f9f9;
            min-width: 200px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            z-index: 10;
        }

        .dropdown-content li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .dropdown-content li a {
            text-decoration: none;
            color: black;
        }

        .dropdown-content li a:hover {
            background-color: #ddd;
        }

        .dropdown-content.show {
            display: block;
        }

        /* Adjust the main content to avoid overlapping the navbar */
        .main-content {
            margin-left: 15rem; /* Match the width of the navbar */
            padding: 1.5rem;
        }

        /* Fixed header and action bar */
        .fixed-header {
            position: sticky;
            top: 0; /* Stick to the top of the container */
            background-color: rgba(255, 255, 255, 0.2); /* Match the background */
            z-index: 10;
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px 12px 0 0;
        }

        /* Table container for scrollable body */
        .table-container {
            max-height: 400px; /* Set a fixed height for the table container */
            overflow-y: auto; /* Make the table body scrollable */
        }

        /* Ensure the table and other content are responsive */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            position: sticky;
            top: 0; /* Stick the header row to the top of the table container */
            background-color: #d1fae5; /* Match the header background */
            color: #065f46;
            z-index: 5;
            padding: 0.75rem;
            text-align: left;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        td {
            padding: 0.75rem;
            text-align: left;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        tr:nth-child(even) {
            background-color: rgba(209, 250, 229, 0.5);
        }

        tr:hover {
            background-color: #bbf7d0;
        }

        .bg-opacity-20 {
            background-color: rgba(255, 255, 255, 0.2);
        }
    </style>
    <script>
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const isVisible = dropdown.classList.contains('show');
            closeAllDropdowns(); // Close other dropdowns
            if (!isVisible) {
                dropdown.classList.add('show');
            }
        }

        function closeAllDropdowns() {
            const dropdowns = document.querySelectorAll('.dropdown-content');
            dropdowns.forEach(dropdown => dropdown.classList.remove('show'));
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.relative')) {
                closeAllDropdowns();
            }
        });
    </script>
</head>
<body class="bg-cover bg-center h-screen" style="background-image: url('img/5.jpg');">
<nav class="w-60 bg-green-700 bg-opacity-60 text-green-900 p-5 rounded-r-2xl shadow-lg fixed top-0 left-0 h-full">
        <div class="logo text-center mb-6">
            <img src="img/ccs.png" alt="Logo" class="w-20 h-20 object-cover rounded-full border-2 border-green-800 mx-auto">
            <p class="mt-2 text-white font-bold"><?php echo htmlspecialchars($username); ?></p>
        </div>
        <a href="a-dashboard.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700 active:bg-green-300">
            <i class="fas fa-user mr-3"></i> Home
        </a>
        <a href="#" onclick="openModal('searchModal')" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-search mr-3"></i> Search
        </a>
        <a href="a-students.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-users mr-3"></i> Students
        </a>

        <!-- Dropdown for View (clickable) -->
        <div class="relative">
                <a href="#" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700" onclick="toggleDropdown('viewDropdown'); return false;">
                    <i class="fas fa-eye mr-3"></i> View <i class="fas fa-caret-down ml-2"></i>
                </a>
                <ul id="viewDropdown" class="dropdown-content bg-green-200 text-green-900 w-full p-2 rounded-lg shadow-md">
                    <li><a href="a-currents.php" class="block p-3">Current Sit-in</a></li>
                    <li><a href="a-vrecords.php" class="block p-3">Visit Records</a></li>
                    <li><a href="a-feedback.php" class="block p-3">Feedback</a></li>
                    <li><a href="a-daily-analytics.php" class="block p-3">Daily Analytics</a></li>
                </ul>
            </div>

            <!-- Dropdown for Lab (clickable) -->
            <div class="relative">
                <a href="#" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700" onclick="toggleDropdown('labDropdown'); return false;">
                    <i class="fas fa-laptop mr-3"></i> Lab <i class="fas fa-caret-down ml-2"></i>
                </a>
                <ul id="labDropdown" class="dropdown-content bg-green-200 text-green-900 w-full p-2 rounded-lg shadow-md">
                    <li><a href="a-computer-control.php" class="block p-3">Computer Control</a></li>
                    <li><a href="a-leaderboard.php" class="block p-3">Leaderboard</a></li>
                    <li><a href="a-resources.php" class="block p-3">Resources</a></li>
                </ul>
            </div>
                
        <a href="a-reports.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-chart-line mr-3"></i> Reports
        </a>
        <a href="a-logout.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
    </nav>

    <div class="main-content">
        <div class="bg-white bg-opacity-20 rounded-xl shadow-lg">
            <!-- Fixed header and action bar -->
            <div class="fixed-header">
                <h3 class="text-xl font-bold text-green-900 mb-4">Student Records</h3>
                <div class="flex justify-between">
                    <button onclick="openModal('addStudentModal')" class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800">Add Students</button>
                    <form action="a-students.php" method="post">
                        <button type="submit" name="reset_sessions" class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800">Reset All Sessions</button>
                    </form>
                </div>
            </div>
            <!-- Table content -->
            <div class="table-container">
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
                                <td class="flex space-x-2">
                                    <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($student)); ?>)" class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600">Edit</button>
                                    <form action="a-students.php" method="post">
                                        <input type="hidden" name="idno" value="<?php echo htmlspecialchars($student['idno']); ?>">
                                        <button type="submit" name="delete_student" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="addStudentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center overflow-auto hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96 relative">
            <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl" onclick="closeModal('addStudentModal')">&times;</button>
            <h2 class="text-xl font-bold mb-4 text-green-900">Add Student</h2>
            <form action="a-students.php" method="post" class="space-y-4">
                <div>
                    <label for="idno" class="block font-bold text-green-900">ID Number:</label>
                    <input type="text" id="idno" name="idno" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
                <div>
                    <label for="firstname" class="block font-bold text-green-900">First Name:</label>
                    <input type="text" id="firstname" name="firstname" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
                <div>
                    <label for="midname" class="block font-bold text-green-900">Middle Name:</label>
                    <input type="text" id="midname" name="midname" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label for="lastname" class="block font-bold text-green-900">Last Name:</label>
                    <input type="text" id="lastname" name="lastname" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
                <div>
                    <label for="year" class="block font-bold text-green-900">Year:</label>
                    <select id="year" name="year" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
                        <option value="">Select Year</option>
                        <option value="1">1st</option>
                        <option value="2">2nd</option>
                        <option value="3">3rd</option>
                        <option value="4">4th</option>
                    </select>
                </div>
                <div>
                    <label for="course" class="block font-bold text-green-900">Course:</label>
                    <select id="course" name="course" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
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
                </div>
                <div>
                    <label for="username" class="block font-bold text-green-900">Username:</label>
                    <input type="text" id="username" name="username" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
                <div>
                    <label for="password" class="block font-bold text-green-900">Password:</label>
                    <input type="password" id="password" name="password" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
                <div>
                    <label for="email" class="block font-bold text-green-900">Email:</label>
                    <input type="email" id="email" name="email" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
                <button type="submit" name="add_student" class="w-full bg-green-700 text-white p-2 rounded hover:bg-green-800">Add Student</button>
            </form>
        </div>
    </div>

    <div id="editStudentModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="modal-content bg-white p-6 rounded-lg shadow-lg w-96">
            <span class="close text-gray-500 text-2xl cursor-pointer" onclick="closeModal('editStudentModal')">&times;</span>
            <h2 class="text-xl font-bold mb-4">Edit Student</h2>
            <form action="a-students.php" method="post">
                <input type="hidden" id="edit-idno" name="idno">

                <label for="edit-firstname" class="block font-bold mb-2">First Name:</label>
                <input type="text" id="edit-firstname" name="firstname" class="w-full p-2 border rounded mb-4" required>

                <label for="edit-midname" class="block font-bold mb-2">Middle Name:</label>
                <input type="text" id="edit-midname" name="midname" class="w-full p-2 border rounded mb-4" required>

                <label for="edit-lastname" class="block font-bold mb-2">Last Name:</label>
                <input type="text" id="edit-lastname" name="lastname" class="w-full p-2 border rounded mb-4" required>

                <label for="edit-year" class="block font-bold mb-2">Year:</label>
                <select id="edit-year" name="year" class="w-full p-2 border rounded mb-4" required>
                    <option value="">Select Year</option>
                    <option value="1">1st</option>
                    <option value="2">2nd</option>
                    <option value="3">3rd</option>
                    <option value="4">4th</option>
                </select>

                <label for="edit-course" class="block font-bold mb-2">Course:</label>
                <select id="edit-course" name="course" class="w-full p-2 border rounded mb-4" required>
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

                <label for="edit-username" class="block font-bold mb-2">Username:</label>
                <input type="text" id="edit-username" name="username" class="w-full p-2 border rounded mb-4" required>

                <label for="edit-email" class="block font-bold mb-2">Email:</label>
                <input type="email" id="edit-email" name="email" class="w-full p-2 border rounded mb-4" required>

                <label for="edit-password" class="block font-bold mb-2">Password (leave blank to keep current):</label>
                <input type="password" id="edit-password" name="password" class="w-full p-2 border rounded mb-4">

                <button type="submit" name="edit_student" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
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
            document.getElementById('editStudentModal').classList.remove('hidden');
        }

        // Optional: close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById("editStudentModal");
            if (event.target === modal) {
                closeModal("editStudentModal");
            }
        }
    </script>
    <?php include 'common-modals.php'; ?>
    <div id="successPopup" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-green-500 text-white p-4 rounded-lg shadow-lg hidden">Student added successfully!</div>
</body>
</html>
