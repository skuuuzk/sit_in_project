<?php

include 'config/db.php'; // Assuming you have a file for database connection

// Ensure user is logged in
if (!isset($_SESSION['idno'])) {
    header("Location: login.php");
    exit();
}

// Fetch user information from the database
$idno = $_SESSION['idno'];
$query = "SELECT idno AS idno, firstname AS FIRSTNAME, lastname AS LASTNAME, year AS YEAR, course AS COURSE, email AS EMAIL, address AS ADDRESS, profile_pic FROM users WHERE idno = '$idno'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Fetch session count
$query = "SELECT session AS session_count FROM users WHERE idno = '$idno'";
$result = mysqli_query($conn, $query);
$session_data = mysqli_fetch_assoc($result);
$session_count = $session_data['session_count'];
$full_name = $user['FIRSTNAME'] . ' ' . $user['LASTNAME'];
$profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'img/default.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
body { display: flex; margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #92929288; }        
        .nav-container { width: 237px; background-color: whitesmoke; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); padding: 10px 20px; border-right: #4d5572  2px solid; }
        .nav-container a { display: flex; align-items: center; text-decoration: none; color: #333; font-size: 16px; margin: 30px 0; padding: 10px; border-radius: 5px; transition: background-color 0.3s ease; }
        .nav-container a i { margin-right: 10px; font-size: 18px; }
        .nav-container a:hover { background-color: #929292; color: white; }
        .nav-container a.active { font-weight: bold; background-color: #e0e0e0; }
        .logo { margin: 50px auto; text-align: center; }
        .logo img { width: 80px; }
        .container { margin:auto; display: flex; justify-content: center; align-items: center; height: 100vh; width: 100%; }
        .edit-profile-card { background-color: whitesmoke; width: 600px; padding: 20px; text-align: center; border-radius: 10px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); border: 3px solid #929292; }

        .student {
            background-color: whitesmoke;
            width: 350px;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border: 3px solid #4d5572;
        }
        .title {
            background-color: #4d5572;
            color: white;
            padding: 5px;
            border-radius: 5px 5px 0 0;
            margin-bottom: 20px;
        }
        .profile-img img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            background-color: black;
        }
        .info p {
            margin: 5px 0;
            font-size: 14px;
        }
        .edit-btn {
            background-color: #4d5572;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            width: 100%;
            border-radius: 5px;
            font-size: 14px;
            margin-top: 10px;
            transition: 0.3s;
        }
        .edit-btn:hover {
            background-color: #929292;
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
                <p><strong>Session:</strong> <?php echo $session_count; ?></p>
            </div>
            
            <a href="dashboard.php" ><i class="fas fa-user"></i><span>Home</span></a>
            <a href="edit.php" class="active"><i class="fas fa-edit"></i><span>Profile</span></a>
            <a href="reservation.php"><i class="fas fa-calendar-check"></i><span> Reservation</span></a>
            <a href="history.php"><i class="fas fa-history"></i><span> History</span></a>
            <a href="notification.php"><i class="fas fa-bell"></i><span>Notifications</span></a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
        </div>
        <div class="container">
            <div class="student">
                <h2 class="title">Student Information</h2>
                <div class="profile-img">
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture">
                </div>
                <div class="info">
                    <p><strong>ID Number:</strong> <?php echo htmlspecialchars($user['idno']); ?></p>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($full_name); ?></p>
                    <p><strong>Year Level:</strong> <?php echo htmlspecialchars($user['YEAR']); ?></p>
                    <p><strong>Course:</strong> <?php echo htmlspecialchars($user['COURSE']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['EMAIL']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($user['ADDRESS']); ?></p>
                </div>
                    <button class="edit-btn" onclick="window.location.href='edit-profile.php'">Edit Profile</button>
            </div>
        </div>
    </div>
</body>
</html>
