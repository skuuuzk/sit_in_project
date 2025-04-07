<?php
include 'config/db.php'; // Assuming you have a file for database connection

// Fetch user information from the database
$idno = $_SESSION['idno'];
$query = "SELECT idno AS idno, firstname AS FIRSTNAME, lastname AS LASTNAME, session AS remaining_sessions, profile_pic FROM users WHERE idno = '$idno'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$student_name = $user['FIRSTNAME'] . " " . $user['LASTNAME'];
$remaining_sessions = $user['remaining_sessions'];
$profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'img/default.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation</title>
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
            flex: 1; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            padding: 20px;
        }
        .form-container { 
            background: whitesmoke; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
            max-width: 450px; 
            width: 100%;
            border: 2px solid #4d5572; 
        }
        .form-container h2 { 
            text-align: center;
            background-color: #4d5572;
            border-radius: 5px 5px 0 0;
            padding: 5px;
            color: white;
            margin: -30px -30px 20px -30px;
        }
        p { 
            text-align: center; 
            font-size: 16px; 
            color: #333; 
        }
    </style>
</head>
<body>
    <div class="nav-container">
        <div class="logo">
            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Logo">
            <p style="text-align: center;"> <?php echo htmlspecialchars($student_name); ?> </p>
            <p><strong>Session:</strong> <?php echo htmlspecialchars($remaining_sessions); ?></p>
        </div>
        <a href="dashboard.php"><i class="fas fa-user"></i><span>Home</span></a>
        <a href="edit.php"><i class="fas fa-edit"></i><span>Profile</span></a>
        <a href="reservation.php" class="active"><i class="fas fa-calendar-check"></i><span> Reservation</span></a>
        <a href="history.php"><i class="fas fa-history"></i><span> History</span></a>
        <a href="notification.php"><i class="fas fa-bell"></i><span>Notifications</span></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
    </div>
    <div class="container">
        <div class="form-container">
            <h2>Reservation</h2>
            <p>The reservation feature is temporarily unavailable. Please contact the admin for assistance.</p>
        </div>
    </div>
</body>
</html>
