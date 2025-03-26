<?php
session_start();
include 'config/db.php'; // Assuming you have a file for database connection

// Fetch announcements from the database
$query = "SELECT title AS TITLE, created_at AS CREATED_AT, content AS CONTENT FROM announcement ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$announcements = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Fetch session count
$user_id = $_SESSION['user_id'];
$query = "SELECT session AS session_count FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$session_data = mysqli_fetch_assoc($result);
$session_count = $session_data['session_count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #92929288;
            display: flex;
            height: 100vh;
        }        
        .nav-container {
            width: 200px;
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
            background: whitesmoke;
            height: 70%;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            flex: 1;
            border: 3px solid #929292;
            display: flex;
            flex-direction: column;
            text-align: center;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            background-color: #4d5572;
            border-radius: 5px 5px 0 0;
            padding: 5px;
            color: white;
        }

        .announcement p, .rules p {
            margin: 10px 0;
        }

        .announcement span {
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>

    <div class="nav-container">
        <div class="logo">
            <input type="file" id="fileUpload" accept="img/*" style="display: none;">
            <img src="img/default.png" alt="Profile">
            <p style="text-align: center;"><?php echo $_SESSION['user']; ?></p>
            <p><strong>Session:</strong> <?php echo $session_count; ?></p>
        </div>
        
        <a href="dashboard.php" class="active"><i class="fas fa-user"></i><span>Home</span></a>
        <a href="edit.php"><i class="fas fa-edit"></i><span>Profile</span></a>
        <a href="reservation.php"><i class="fas fa-calendar-check"></i><span> Reservation</span></a>
        <a href="history.php"><i class="fas fa-history"></i><span> History</span></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
    </div>

    <div class="container">
        <div class="box announcement">
            <h2>Announcement</h2>
            <?php if (!empty($announcements)): ?>
                <?php foreach ($announcements as $announcement): ?>
                    <p><strong><?php echo htmlspecialchars($announcement['TITLE']); ?> | <?php echo htmlspecialchars($announcement['CREATED_AT']); ?></strong><br><?php echo htmlspecialchars($announcement['CONTENT']); ?></p>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No announcements available.</p>
            <?php endif; ?>
        </div>
        
        <div class="box rules">
            <h2>Rules and Regulation</h2>
            <p><strong>University of Cebu</strong><br><strong>COLLEGE OF INFORMATION & COMPUTER STUDIES</strong></p>
            <p><strong>LABORATORY RULES AND REGULATIONS</strong></p>
            <p>To avoid embarrassment and maintain camaraderie with your friends and superiors at our laboratories, please observe the following:</p>
            <p>1. Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones, walkmans and other personal pieces of equipment must be switched off.</p>
            <p>2. Games are not allowed inside the lab. This includes computer-related games, card games, and other games that may disturb the operation of the lab.</p>
            <p>3. Surfing the Internet is allowed only with the permission of the instructor. Downloading and installing of software are strictly prohibited.</p>
        </div>
    </div>

</body>
</html>