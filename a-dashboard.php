<?php
require_once('config/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}
// Fetch statistics
$students_count_query = "SELECT COUNT(*) AS count FROM users";
$students_count = mysqli_fetch_assoc(mysqli_query($conn, $students_count_query))['count'];

$current_sit_in_query = "SELECT COUNT(*) AS count FROM reservations WHERE time_out IS NULL";
$current_sit_in = mysqli_fetch_assoc(mysqli_query($conn, $current_sit_in_query))['count'];

$total_sit_in_query = "SELECT COUNT(*) AS count FROM reservations";
$total_sit_in = mysqli_fetch_assoc(mysqli_query($conn, $total_sit_in_query))['count'];

// Fetch activity statistics for graph
$activity_data_query = "SELECT purpose, COUNT(*) AS count FROM reservations GROUP BY purpose";
$activity_data_result = mysqli_query($conn, $activity_data_query);
$purpose_stats = [];
while ($row = mysqli_fetch_assoc($activity_data_result)) {
    $purpose_stats[$row['purpose']] = $row['count'];
}

// Fetch announcements
$announcements_query = "SELECT title, content, created_at FROM announcement ORDER BY created_at DESC";
$announcements_result = mysqli_query($conn, $announcements_query);
$announcements = mysqli_fetch_all($announcements_result, MYSQLI_ASSOC);

// Handle announcement submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['announcement'])) {
    $announcement_content = trim($_POST['announcement']);
    $admin_id = $_SESSION['admin_id'];

    if (!empty($announcement_content)) {
        $stmt = $conn->prepare("INSERT INTO announcement (content, admin_id) VALUES (?, ?)");
        $stmt->bind_param("si", $announcement_content, $admin_id);
        $stmt->execute();
        $stmt->close();

        header("Location: a-dashboard.php?success=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-image: url(img/5.jpg); /* Background image */
            background-size: cover; /* Cover the entire viewport */
            display: flex;
        }

        .nav-container {
            width: 240px;
            background: rgba(255, 255, 255, 0.1); /* Transparent background */
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Soft shadow */
            backdrop-filter: blur(1px); /* Frosted glass effect */
            background-color:rgba(119, 152, 95, 0.54);
            color:rgb(11, 27, 3);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 10px 20px;
            border-radius: 0 20px 20px 0;
            justify-content: stretch;
        }

        .nav-container a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color:rgb(1, 23, 13);
            font-size: 16px;
            margin: 23.5px 0;
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
            margin: 25px auto;
            text-align: center;
        }

        .logo img {
            width: 70px;
            height: 70px; /* Set height to make it circular */
            object-fit: cover; /* Ensure the image covers the area */
            border-radius: 50%;
            border: 2px solid #475E53; /* Border around the image */
        }
        .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #4d5572; /* Background color */
            color: white; /* Text color */
            padding: 10px 0;
            text-align: center;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Add shadow for better visibility */
        }
        .container {
            flex-direction: column;
            gap: 20px;
            padding: 50px;
            justify-content: center;
            position: relative;
        }

        .top-section {
            display: flex;
            gap: 50px;
            justify-content: center;
            align-items: stretch;
            margin-top: 30px;
        }


        .box {
            padding: 35px;
            border-radius: 10px;
            width: 50%;
            flex: 1;
            border: 3px solid #475E53;
            display: flex;
            flex-direction: column;
            text-align: justify;
            background: rgba(255, 255, 255, 0.1); /* Transparent background */
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Soft shadow */
            backdrop-filter: blur(10px); /* Frosted glass effect */
        }

        .announcement textarea {
            width: 100%;
            padding: 10px;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .announcement button {
            width: 100%;
            padding: 10px;
            background-color:#475E53;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;;
        }

        .announcement button:hover {
            background-color:#DEE9DC;
            color: seagreen;
        }

        .announcement-list {
            max-height: 200px;
            overflow-y: auto;
            border-top: 2px solid #475E53;
            margin-top: 10px;
            padding-top: 10px;
        }

        .success-message {
            color: green;
            margin: 10px 0 10px 0;
            text-align: justify;;
        }

    </style>
</head>
<body>    
    <nav class="nav-container"> 
        <div class="logo">
            <img src="img/ccs.png" alt="Logo" style="width: 100px; height: auto; margin-bottom: 20px;">
        </div>      
            <a href="a-dashboard.php"class="active"><i class="fas fa-user"></i><span>Home</span></a>
            <a href="#" onclick="openModal('searchModal')"><i class="fas fa-search"></i> <span>Search</span></a>
            <a href="a-students.php"><i class="fas fa-users"></i> <span>Students</span></a>
            <a href="a-currents.php"><i class="fas fa-user-clock"></i> <span>Current Sit-in</span></a>
            <a href="a-vrecords.php"><i class="fas fa-book"></i> <span>Visit Records</span></a>
            <a href="a-feedback.php"><i class="fas fa-comments"></i> <span>Feedback</span></a>
            <a href="a-reports.php"><i class="fas fa-chart-line"></i> <span>Reports</span></a>
            <a href="a-logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
    </nav>

    <div class="container">
        <div class="header-container">
            <header>
                <h1 >Admin Dashboard</h1>
            </header>
        </div>

    <div class="top-section">
        <!-- Statistics Section --> 
        <div class="box stats">
            <h1>Statistics</h1>
            <p>Currently Sit-in Students:  <?php echo $current_sit_in; ?></p>            
            <p> Registered Students     :  <?php echo $students_count; ?></p>
            <p> Total Sit-in            :  <?php echo $total_sit_in; ?></p>
            
            <canvas id="purposeChart" width="300" height="200" style="margin: 20px;"></canvas>
            <script>
                const ctx = document.getElementById('purposeChart').getContext('2d');
                const purposeChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: <?php echo json_encode(array_keys($purpose_stats)); ?>,
                        datasets: [{
                            label: 'No. of Students',
                            data: <?php echo json_encode(array_values($purpose_stats)); ?>,
                            backgroundColor: [
                                '#DEE9DC', '#C5D4C3', '#9AAE97', '#81967F', '#000000', '#475E53 '
                            ],
                            borderColor: '#475E53',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        }
                    }
                });
            </script>
        </div>

        <!-- Announcement Section (moved beside stats) -->
        <div class="box announcement">
            <h3>Create Announcement</h3>
            <form action="a-dashboard.php" method="post">
                <textarea name="announcement" placeholder="Enter your announcement..." required></textarea>
                <button type="submit">Post</button>
            </form>
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <p class="success-message">Announcement posted successfully.</p>
            <?php endif; ?>

            <h4>Posted Announcements</h4>
            <div class="announcement-list">
                <?php foreach ($announcements as $announcement): ?>
                    <p>
                        <strong><?php echo htmlspecialchars($announcement['created_at']); ?></strong><br>
                        <?php echo htmlspecialchars($announcement['content']); ?>
                    </p>
                <?php endforeach; ?>
            </div>
        </div>
    </div> <!-- END of top-section -->
</div> <!-- END of container -->

    <?php include 'common-modals.php'; ?>
</body>
</html>