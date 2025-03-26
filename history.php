<?php
require_once('config/db.php'); // Assuming you have a file for database connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user information
$query = "SELECT firstname, lastname, session AS remaining_sessions, profile_pic FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$student_name = $user['firstname'] . " " . $user['lastname'];
$remaining_sessions = $user['remaining_sessions'];
$profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'img/default.png';

// Fetch reservation history
$query = "SELECT user_id, CONCAT(firstname, ' ', lastname) AS student_name, purpose, lab, time_in, time_out, date FROM reservations WHERE user_id = '$user_id' ORDER BY date DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Information</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            display: flex;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #92929288;
        }
        .nav-container {
            width: 237px;
            background-color: whitesmoke;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 10px 20px;
            border-right: #4d5572  2px solid;
        }

        .nav-container a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #333;
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
            background-color: #929292;
            color: white;
        }

        .nav-container a span {
            font-weight: normal;
        }

        .nav-container a.active {
            font-weight: bold;
            background-color: #e0e0e0;
        }
        .logo {
            margin: 50px auto;
            text-align: center;
        }

        .logo img {
            width: 80px;
        }
        .container {
            margin:auto; /* Adjust for fixed sidebar */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            width: 100%;
        }

        .form-container {
            background: whitesmoke;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            margin-top: 20px;
            border: 3px solid #929292;
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            padding: 5px;
            background-color: #4d5572;
            border-radius: 5px 5px 0 0;
            color: white;
            
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }
        select, button {
            width: 78%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        input{
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            background-color: #929292;
            color: whitesmoke;
            border: none;
            cursor: pointer;
            margin-top: 20px;
            padding: 15px;
            font-size: 16px;
        }
        button:hover {
            background-color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;border-radius: 10px;
            
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
        .pagination {
            margin-top: 10px;
            display: flex;
            justify-content: flex-end;
        }
        .pagination button {
            background-color: #4d5572;
            color: white;
            border: none;
            padding: 5px 10px;
            margin: 0 2px;
            cursor: pointer;
        }
        .pagination button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .search-container {
            display: flex;
            margin-bottom: 10px;
        }
        .search-container input {
            padding: 5px;
            border: 1px solid #929292;
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
            <table>
                <thead>
                    <tr>
                        <th>ID Number</th>
                        <th>Name</th>
                        <th>Purpose</th>
                        <th>Laboratory</th>
                        <th>Login</th>
                        <th>Logout</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                            <td><?php echo htmlspecialchars($row['lab']); ?></td>
                            <td><?php echo htmlspecialchars($row['time_in']); ?></td>
                            <td><?php echo htmlspecialchars($row['time_out'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['date']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
