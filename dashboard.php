<?php
include 'config/db.php'; // Assuming you have a file for database connection

// Ensure user is logged in
if (!isset($_SESSION['idno'])) {
    header("Location: login.php");
    exit();
}

// Fetch announcements from the database
$query = "SELECT title, content, created_at FROM announcement ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$announcements = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Fetch session count and user details
$idno = $_SESSION['idno'];
$query = "SELECT session AS session_count, firstname, lastname, profile_pic FROM users WHERE idno = '$idno'";
$result = mysqli_query($conn, $query);
$session_data = mysqli_fetch_assoc($result);
$session_count = $session_data['session_count'] ?? 'N/A';
$full_name = ($session_data['firstname'] ?? '') . ' ' . ($session_data['lastname'] ?? '');
$profile_pic = !empty($session_data['profile_pic']) ? $session_data['profile_pic'] : 'img/default.png';

// Fetch unread notifications count
$query = "SELECT COUNT(*) AS unread_count FROM notifications WHERE idno = '$idno' AND is_read = 0";
$result = mysqli_query($conn, $query);
$notification_data = mysqli_fetch_assoc($result);
$unread_count = $notification_data['unread_count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
            width: 200px;
            background: rgba(255, 255, 255, 0.1); /* Transparent background */
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Soft shadow */
            backdrop-filter: blur(1px); /* Frosted glass effect */
            background-color:rgba(119, 152, 95, 0.54);
            color:rgb(11, 27, 3);
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
            display: flex;
            gap: 20px;
            max-width: 900px;
            margin: auto;
            justify-content: center;
            align-items: stretch;
            height: 100vh;
            width: 100%;        
        }

        .box {
            margin: 90px auto;
            height: 70%;
            padding: 25px;
            border-radius: 10px;
            flex: 1;
            border: 3px solid #475E53;
            display: flex;
            flex-direction: column;
            text-align: center;
            background: rgba(255, 255, 255, 0.1); /* Transparent background */
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Soft shadow */
            backdrop-filter: blur(10px); /* Frosted glass effect */
            overflow: hidden;
            overflow: auto;             /* Vertical scrollbar appears when needed */
                
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            background-color: #475E53;
            border-radius: 5px 5px 0 0;
            padding: 10px;
            color: white;
        }

        .announcement p, .rules p {
            margin: 10px 0;
            text-align: justify;
        }
    </style>
</head>
<body>

    <div class="nav-container">
        <div class="logo">
            <input type="file" id="fileUpload" accept="img/*" style="display: none;">
            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile">
            <p style="text-align: center;">
                <?php echo htmlspecialchars($full_name); ?>
            </p>
            <p><strong>Session:</strong> <?php echo htmlspecialchars($session_count); ?></p>
        </div> 
        
        <a href="dashboard.php" class="active"><i class="fas fa-user"></i><span>Home</span></a>
        <a href="edit.php"><i class="fas fa-edit"></i><span>Profile</span></a>
        <a href="reservation.php"><i class="fas fa-calendar-check"></i><span> Reservation</span></a>
        <a href="history.php"><i class="fas fa-history"></i><span> History</span></a>
        <a href="notification.php"><i class="fas fa-bell"></i><span>Notifications</span><?php if ($unread_count > 0) echo " ($unread_count)"; ?></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
    </div>

    <div class="container">
        <div class="box announcement">
            <h2>Announcements</h2>
            <?php if (!empty($announcements)): ?>
                <?php foreach ($announcements as $announcement): ?>
                    <p><strong><?php echo htmlspecialchars($announcement['title']); ?></strong><br>
                    <?php echo htmlspecialchars($announcement['content']); ?><br>
                    <small><?php echo htmlspecialchars($announcement['created_at']); ?></small></p>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No announcements available.</p>
            <?php endif; ?>
        </div>
        
        <div class="box rules">
            <h2>Rules and Regulation</h2>
            <h3><strong>University of Cebu</strong><br><strong>COLLEGE OF INFORMATION & COMPUTER STUDIES</strong></h3>
            <h3><strong>LABORATORY RULES AND REGULATIONS</strong></h3>
            <p>To avoid embarrassment and maintain camaraderie with your friends and superiors at our laboratories, please observe the following:</p>
            <p>1. Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones, walkmans and other personal pieces of equipment must be switched off.</p>
            <p>2. Games are not allowed inside the lab. This includes computer-related games, card games, and other games that may disturb the operation of the lab.</p>
            <p>3. Surfing the Internet is allowed only with the permission of the instructor. Downloading and installing of software are strictly prohibited.</p>
        </div>
    </div>

    </body>
</html>