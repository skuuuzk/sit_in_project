<?php
require_once('config/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

// Handle search query
$search_id = $_GET['search_id'] ?? null;

if ($search_id) {
    $query = "SELECT r.idno, CONCAT(u.firstname, ' ', u.lastname) AS student_name, r.purpose, r.lab, r.time_in, r.time_out, r.date 
              FROM reservations r 
              JOIN users u ON r.idno = u.idno 
              WHERE r.status = 'completed' AND r.idno = ? 
              ORDER BY r.date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $search_id);
} else {
    $query = "SELECT r.idno, CONCAT(u.firstname, ' ', u.lastname) AS student_name, r.purpose, r.lab, r.time_in, r.time_out, r.date 
              FROM reservations r 
              JOIN users u ON r.idno = u.idno 
              WHERE r.status = 'completed' 
              ORDER BY r.date DESC";
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();
$visit_records = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visit Records</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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

        /* Search Bar Styles */
        #searchInput {
            padding: 10px;
            width: 70%;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        #searchButton {
            padding: 10px 20px;
            margin-left: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #searchButton:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <nav class="nav-container"> 
        <div class="logo">
            <img src="img/ccs.png" alt="Logo" style="width: 100px; height: auto; margin-bottom: 20px;">
        </div>      
        <a href="a-dashboard.php"><i class="fas fa-user"></i><span>Home</span></a>
        <a href="#" onclick="openModal('searchModal')"><i class="fas fa-search"></i> <span>Search</span></a>
        <a href="a-students.php"><i class="fas fa-users"></i> <span>Students</span></a>
        <a href="a-currents.php"><i class="fas fa-user-clock"></i> <span>Current Sit-in</span></a>
        <a href="a-vrecords.php" class="active"><i class="fas fa-book"></i> <span>Visit Records</span></a>
        <a href="a-feedback.php"><i class="fas fa-comments"></i> <span>Feedback</span></a>
        <a href="a-reports.php"><i class="fas fa-chart-line"></i> <span>Reports</span></a>
        <a href="a-logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
    </nav>

    <div class="container">
        <div class="header-container">
            <header>
                <h1>Records</h1>
            </header>
        </div>

        <div class="records-container">
            <!-- Search Bar -->
            <form method="GET" action="a-vrecords.php" style="margin-bottom: 20px; text-align: center;">
                <input type="text" name="search_id" placeholder="Enter ID Number" value="<?php echo htmlspecialchars($search_id ?? ''); ?>" 
                       style="padding: 10px; border: 1px solid #ccc; border-radius: 5px; width: 70%;">
                <button type="submit" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Search
                </button>
            </form>

            <!-- Table -->
            <table id="visitRecordsTable">
                <thead>
                    <tr>
                        <th>ID Number</th>
                        <th>Name</th>
                        <th>Purpose</th>
                        <th>Lab Room</th>
                        <th>Time-in</th>
                        <th>Time-out</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($visit_records)): ?>
                        <?php foreach ($visit_records as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['idno']); ?></td>
                                <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($record['purpose']); ?></td>
                                <td><?php echo htmlspecialchars($record['lab']); ?></td>
                                <td><?php echo htmlspecialchars($record['time_in']); ?></td>
                                <td><?php echo htmlspecialchars($record['time_out']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No visit records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>