<?php
require_once('config/db.php'); // Assuming you have a file for database connection

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

// Fetch statistics
$students_count_query = "SELECT COUNT(*) AS count FROM users";
$students_count_result = mysqli_query($conn, $students_count_query);
$students_count = mysqli_fetch_assoc($students_count_result)['count'];

$current_sit_in_query = "SELECT COUNT(*) AS count FROM reservations WHERE time_out IS NULL";
$current_sit_in_result = mysqli_query($conn, $current_sit_in_query);
$current_sit_in = mysqli_fetch_assoc($current_sit_in_result)['count'];

$total_sit_in_query = "SELECT COUNT(*) AS count FROM reservations";
$total_sit_in_result = mysqli_query($conn, $total_sit_in_query);
$total_sit_in = mysqli_fetch_assoc($total_sit_in_result)['count'];

// Fetch announcements
$announcements_query = "SELECT title, content, created_at FROM announcement ORDER BY created_at DESC";
$announcements_result = mysqli_query($conn, $announcements_query);
$announcements = mysqli_fetch_all($announcements_result, MYSQLI_ASSOC);

// Fetch pending reservations
$pending_reservations_query = "SELECT r.id, r.user_id, CONCAT(u.firstname, ' ', u.lastname) AS student_name, r.purpose, r.lab, r.time_in, r.date FROM reservations r JOIN users u ON r.user_id = u.user_id WHERE r.status = 'pending'";
$pending_reservations_result = mysqli_query($conn, $pending_reservations_query);
$pending_reservations = mysqli_fetch_all($pending_reservations_result, MYSQLI_ASSOC);

// Fetch student activity data for the graph
$activity_data_query = "SELECT purpose, COUNT(*) AS count FROM reservations GROUP BY purpose";
$activity_data_result = mysqli_query($conn, $activity_data_query);
$activity_data = [];
while ($row = mysqli_fetch_assoc($activity_data_result)) {
    $activity_data[$row['purpose']] = $row['count'];
}

$announcement_success = false;

// Handle announcement submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['announcement'])) {
    $announcement_content = $_POST['announcement'];
    $admin_id = $_SESSION['admin_id'];

    $stmt = $conn->prepare("INSERT INTO announcement (content, admin_id) VALUES (?, ?)");
    $stmt->bind_param("si", $announcement_content, $admin_id);
    $stmt->execute();
    $stmt->close();

    $announcement_success = true;

    // Refresh the page to show the new announcement
    header("Location: a-dashboard.php?success=1");
    exit();
}

// Handle reservation approval
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_reservation'])) {
    $reservation_id = $_POST['reservation_id'];

    // Update reservation status to approved
    $stmt = $conn->prepare("UPDATE reservations SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $stmt->close();

    // Notify user of approval
    $stmt = $conn->prepare("SELECT user_id FROM reservations WHERE id = ?");
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();

    $notification = "Your reservation has been approved.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $notification);
    $stmt->execute();
    $stmt->close();

    // Decrease user's remaining sessions
    $stmt = $conn->prepare("UPDATE users SET session = session - 1 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Refresh the page to show the updated reservations
    header("Location: a-dashboard.php");
    exit();
}

// Handle reservation disapproval
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['disapprove_reservation'])) {
    $reservation_id = $_POST['reservation_id'];

    $stmt = $conn->prepare("UPDATE reservations SET status = 'disapproved' WHERE id = ?");
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $stmt->close();

    // Notify user of disapproval
    $stmt = $conn->prepare("SELECT user_id FROM reservations WHERE id = ?");
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();

    $notification = "Your reservation has been disapproved.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $notification);
    $stmt->execute();
    $stmt->close();

    // Refresh the page to show the updated reservations
    header("Location: a-dashboard.php");
    exit();
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
            gap: 20px;
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
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .announcement button:hover {
            background: #0056b3;
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
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        button { cursor: pointer; }
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

    <div id="searchModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('searchModal')">&times;</span>
            <h2>Search Student</h2>
            <input type="text" id="searchQuery" placeholder="Enter ID Number">
            <button onclick="openSitInForm()">Search</button>
        </div>
    </div>

    <div id="sitInModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('sitInModal')">&times;</span>
            <h2>Sit-in Form</h2>
            <form action="a-currents.php" method="post">
                <label>ID Number:</label>
                <input type="text" id="idNumber" name="idNumber" readonly>
                <label>Student Name:</label>
                <input type="text" id="studentName" name="studentName" readonly>
                <label>Purpose:</label>
                <select name="purpose">
                    <option>C Programming</option>
                    <option>C++ Programming</option>
                    <option>Java Programming</option>
                    <option>Python Programming</option>
                    <option>C# Programming</option>
                    <option>Other</option>
                </select>
                <label>Lab:</label>
                <select name="lab">
                    <option>Lab 524</option>
                    <option>Lab 525</option>
                </select>
                <label>Remaining Session:</label>
                <input type="text" id="remainingSessions" name="remainingSessions" readonly>
                <button type="submit">Sit-in (Approved)</button>
                <button type="button" onclick="closeModal('sitInModal')">Close</button>
            </form>
        </div>
    </div>

    <div class="container">
        <div class="top-section">
            <!-- Statistics Section -->
            <div class="box stats">
                <h3>Statistics</h3>
                <p>Students Registered: <span id="studentsCount"><?php echo $students_count; ?></span></p>
                <p>Currently Sit-in: <span id="currentSitIn"><?php echo $current_sit_in; ?></span></p>
                <p>Total Sit-in: <span id="totalSitIn"><?php echo $total_sit_in; ?></span></p>
                <canvas id="activityChart"></canvas>
            </div>

            <!-- Announcement Section -->
            <div class="box announcement">
                <h3>Create Announcement</h3>
                <form action="a-dashboard.php" method="post">
                    <textarea id="announcementInput" name="announcement" placeholder="Enter your announcement here..."></textarea>
                    <button type="submit">Submit</button>
                </form>
                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                    <p class="success-message">Announcement posted successfully.</p>
                <?php endif; ?>
                <h4>Posted Announcements</h4>
                <div class="announcement-list" id="announcementList">
                    <?php foreach ($announcements as $announcement): ?>
                        <p><strong><?php echo htmlspecialchars($announcement['created_at']); ?></strong><br><?php echo htmlspecialchars($announcement['content']); ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Pending Reservations Section -->
        <div class="box pending-reservations">
            <h3>Pending Reservations</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Purpose</th>
                        <th>Lab</th>
                        <th>Time In</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_reservations as $reservation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['purpose']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['lab']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['time_in']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['date']); ?></td>
                            <td>
                                <form action="a-dashboard.php" method="post" style="display:inline;">
                                    <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($reservation['id']); ?>">
                                    <button type="submit" name="approve_reservation">Approve</button>
                                </form>
                                <form action="a-dashboard.php" method="post" style="display:inline;">
                                    <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($reservation['id']); ?>">
                                    <button type="submit" name="disapprove_reservation">Disapprove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).style.display = 'block';
        }
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }
        document.getElementById('searchBtn').addEventListener('click', function() {
            openModal('searchModal');
        });
        function openSitInForm() {
            let idNumber = document.getElementById('searchQuery').value;
            if (idNumber) {
                // Fetch student details from the server
                fetch(`fetch_student.php?id=${idNumber}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data) {
                            document.getElementById('idNumber').value = data.user_id;
                            document.getElementById('studentName').value = data.firstname + ' ' + data.lastname;
                            document.getElementById('remainingSessions').value = data.session;
                            closeModal('searchModal');
                            openModal('sitInModal');
                        } else {
                            alert('Student not found!');
                        }
                    });
            } else {
                alert('Please enter an ID number!');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Activity Chart
            const ctx = document.getElementById('activityChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Object.keys(<?php echo json_encode($activity_data); ?>),
                    datasets: [{
                        label: 'Student Activities',
                        data: Object.values(<?php echo json_encode($activity_data); ?>),
                        backgroundColor: '#4d5572'
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Modal functionality
            var modal = document.getElementById("searchModal");
            var btn = document.getElementById("searchBtn");
            var span = document.getElementsByClassName("close")[0];

            btn.onclick = function() {
                modal.style.display = "block";
            }

            span.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        });
    </script>    
</body>
</html>
