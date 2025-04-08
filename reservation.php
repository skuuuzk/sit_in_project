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
            flex: 1; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            padding: 20px;
        }
        .form-container { 
            padding: 30px; 
            border-radius: 10px; 
            background: rgba(255, 255, 255, 0.1); /* Transparent background */
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Soft shadow */
            backdrop-filter: blur(10px); /* Frosted glass effect */            max-width: 450px; 
            width: 100%;
            border: 2px solid #475E53; 
        }
        .form-container h2 { 
            text-align: center;
            background-color: #475E53;
            border-radius: 5px 5px 0 0;
            padding: 5px;
            color: white;
            margin: -30px -30px 20px -30px;
        }
        p { 
            text-align: center; 
            font-size: 16px; 
             
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
