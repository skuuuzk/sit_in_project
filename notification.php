<?php
require_once('config/db.php'); // Assuming you have a file for database connection

// Ensure user is logged in
if (!isset($_SESSION['idno'])) {
    header("Location: login.php");
    exit();
}

$idno = $_SESSION['idno'];

// Fetch user information
$query = "SELECT firstname, lastname, session AS session_count, profile_pic FROM users WHERE idno = '$idno'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$full_name = $user['firstname'] . " " . $user['lastname'];
$session_count = $user['session_count'];
$profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'img/default.png';

// Fetch notifications
$query = "SELECT message, created_at FROM notifications WHERE idno = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idno);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Mark notifications as read
$query = "UPDATE notifications SET is_read = 1 WHERE idno = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idno);
$stmt->execute();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
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
            width: 237px;
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
            margin:auto;
            display: flex;
            justify-content: center;
            align-items: stretch;
            height: 100vh;
            width: 100%;
            overflow: hidden;
        }

        .notification-card {
            background-color: whitesmoke;
            width: 600px;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            border: 3px solid #475E53;
            background: rgba(255, 255, 255, 0.1); /* Transparent background */
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Soft shadow */
            backdrop-filter: blur(10px); /* Frosted glass effect */
            overflow: hidden;
            overflow: auto; 
        }

        .title {
            text-align: center;
            margin-bottom: 20px;
            background-color: #475E53;
            border-radius: 5px 5px 0 0;
            padding: 5px;
            color: white;
        }

        .notification-list {
            text-align: left;
        }

        .notification-item {
            margin-bottom: 10px;
            padding: 10px;
            border-bottom: 1px solid #475E53;
        }

        .notification-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="nav-container">
        <div class="logo">
            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture">
            <p style="text-align: center;"> <?= htmlspecialchars($full_name); ?></p>
            <p><strong>Session:</strong> <?= htmlspecialchars($session_count); ?></p>
        </div>
        <a href="dashboard.php"><i class="fas fa-user"></i><span>Home</span></a>
        <a href="edit.php"><i class="fas fa-edit"></i><span>Profile</span></a>
        <a href="reservation.php"><i class="fas fa-calendar-check"></i><span>Reservation</span></a>
        <a href="history.php"><i class="fas fa-history"></i><span>History</span></a>
        <a href="notification.php" class="active"><i class="fas fa-bell"></i><span>Notifications</span></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </div>

    <div class="container">
        <div class="notification-card">
            <h2 class="title">Notifications</h2>
            <div class="notification-list">
                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item">
                            <p><strong><?php echo htmlspecialchars($notification['created_at']); ?></strong></p>
                            <p><?php echo htmlspecialchars($notification['message']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No notifications available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
