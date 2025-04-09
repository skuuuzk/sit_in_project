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

    if (!empty($feedback)) {
        $stmt = $conn->prepare("UPDATE reservations SET feedback = ? WHERE id = ? AND idno = ?");
        $stmt->bind_param("sis", $feedback, $reservation_id, $idno);
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background-image: url(img/5.jpg); /* Background image */
            background-size: cover; /* Cover the entire viewport */
            display: flex;
            height: 100vh;
        }
        .nav-container {
            width: 245px;
            background: rgba(255, 255, 255, 0.1); /* Transparent background */
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Soft shadow */
            backdrop-filter: blur(1px); /* Frosted glass effect */
            background-color:rgba(119, 152, 95, 0.54);
            color:rgb(11, 27, 3);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 10px 20px;
            border-radius: 0 20px 20px 0;
        }

        .nav-container a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color:rgb(1, 23, 13);
            font-size: 16px;
            margin: 30px 0;
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
            margin: 50px auto;
            text-align: center;
        }

        .logo img {
            width: 90px;
            height: 90px; /* Set height to make it circular */
            object-fit: cover; /* Ensure the image covers the area */
            border-radius: 50%;
            border: 2px solid #475E53; /* Border around the image */
        }
        .container {
            margin: auto;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 20px;
        }

        .form-container {
            padding: 30px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1); /* Transparent background */
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Soft shadow */
            backdrop-filter: blur(10px); /* Frosted glass effect */
            width: 100%;
            max-width: 800px;
            border: 3px solid #475E53;
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            background-color: #475E53;
            border-radius: 5px 5px 0 0;
            padding: 10px;
            color: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 10px;
        }
        th, td {
            border: 2px solid #475E53;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #DEE9DC;
            color: #475E53;
        }
        .feedback-form textarea {
            width: 100%;
            border: 1px solid #475E53;
            border-radius: 5px;
            resize: none;
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
<div class="nav-container">
        <div class="logo">
            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile">
            <p><?php echo htmlspecialchars($student_name); ?></p>
            <p><strong>Session:</strong> <?php echo htmlspecialchars($remaining_sessions); ?></p>
        </div>
        <a href="dashboard.php"><i class="fas fa-user"></i> Home</a>
        <a href="edit.php"><i class="fas fa-edit"></i> Profile</a>
        <a href="reservation.php"><i class="fas fa-calendar-check"></i> Reservation</a>
        <a href="history.php" class="active"><i class="fas fa-history"></i> History</a>
        <a href="notification.php"><i class="fas fa-bell"></i><span>Notifications</span></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <div class="container">
        <div class="form-container">
            <h2>History</h2>
            <?php if (isset($_GET['feedback_status']) && $_GET['feedback_status'] === 'success'): ?>
                <p style="color: green; text-align: center;">Feedback submitted successfully!</p>
            <?php endif; ?>
            <table>
                <thead>
                    <tr>
                        <th>Purpose</th>
                        <th>Laboratory</th>
                        <th>Login</th>
                        <th>Logout</th>
                        <th>Feedback</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                            <td><?php echo htmlspecialchars($row['lab']); ?></td>
                            <td><?php echo htmlspecialchars($row['time_in']); ?></td>
                            <td><?php echo htmlspecialchars($row['time_out'] ?? 'N/A'); ?></td>
                            <td>
                                <form method="POST" action="submit_feedback.php" class="feedback-form">
                                    <textarea name="feedback" rows="2" placeholder="Leave your feedback here..."></textarea>
                                    <input type="hidden" name="reservation_id" value="<?php echo $row['id']; ?>">
                            </td>
                            <td>
                                    <div class="action-buttons">
                                        <button type="submit">Submit Feedback</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
        </div>
    </div>
   
</body>
</html>
