<?php
require_once('config/db.php'); // Assuming you have a file for database connection

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

$search_query = '';
$search_results = [];
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Handle search
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_query'])) {
    $search_query = $_POST['search_query'];
}

// Fetch visit records
$query = "SELECT r.user_id, CONCAT(u.firstname, ' ', u.lastname) AS student_name, r.purpose, r.lab, r.time_in, r.time_out, r.date, u.session 
          FROM reservations r 
          JOIN users u ON r.user_id = u.user_id 
          WHERE r.user_id LIKE ? OR u.firstname LIKE ? OR u.lastname LIKE ? 
          LIMIT ?, ?";
$like_query = '%' . $search_query . '%';
$stmt = $conn->prepare($query);
$stmt->bind_param("sssss", $like_query, $like_query, $like_query, $offset, $records_per_page);
$stmt->execute();
$result = $stmt->get_result();
$search_results = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch total records count for pagination
$count_query = "SELECT COUNT(*) AS total FROM reservations r JOIN users u ON r.user_id = u.user_id WHERE r.user_id LIKE ? OR u.firstname LIKE ? OR u.lastname LIKE ?";
$stmt = $conn->prepare($count_query);
$stmt->bind_param("sss", $like_query, $like_query, $like_query);
$stmt->execute();
$result = $stmt->get_result();
$total_records = $result->fetch_assoc()['total'];
$stmt->close();

$total_pages = ceil($total_records / $records_per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visit Records</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #D1B8E1;
        }

        .navbar {
            background: #7F60A8;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .navbar ul {
            list-style: none;
            display: flex;
            gap: 15px;
        }

        .navbar ul li a {
            text-decoration: none;
            color: white;
            font-weight: bold;
        }

        .container {
            margin: 20px;
        }

        .search-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .search-container input {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px 0 0 5px;
        }

        .search-container button {
            padding: 10px;
            background-color: #4d5572;
            color: white;
            border: none;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
        }

        .search-container button:hover {
            background-color: #3a4256;
        }

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

        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .pagination button {
            padding: 10px 20px;
            background-color: #4d5572;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .pagination button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h2>Admin Dashboard</h2>
        <ul>
            <li><a href="a-dashboard.php"><i class="fas fa-user"></i>Home</a></li>
            <li><a href="a-students.php">Students</a></li>
            <li><a href="a-currents.php">Current Sit-in</a></li>
            <li><a href="a-vrecords.php">Visit Records</a></li>
            <li><a href="a-logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <h2 style="text-align: center;">Visit Records</h2>
        <div class="search-container">
            <form action="a-vrecords.php" method="post">
                <input type="text" name="search_query" placeholder="Search by ID, Name, or Email" value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit">Search</button>
            </form>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID Number</th>
                    <th>Name</th>
                    <th>Purpose</th>
                    <th>Lab Room</th>
                    <th>Remaining Sessions</th>
                    <th>Time-In</th>
                    <th>Time-Out</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($search_results)): ?>
                    <?php foreach ($search_results as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['purpose']); ?></td>
                            <td><?php echo htmlspecialchars($record['lab']); ?></td>
                            <td><?php echo htmlspecialchars($record['session']); ?></td>
                            <td><?php echo htmlspecialchars($record['time_in']); ?></td>
                            <td><?php echo htmlspecialchars($record['time_out'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($record['date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="pagination">
            <?php if ($current_page > 1): ?>
                <button onclick="window.location.href='a-vrecords.php?page=<?php echo $current_page - 1; ?>'">Previous</button>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <button onclick="window.location.href='a-vrecords.php?page=<?php echo $i; ?>'" <?php if ($i == $current_page) echo 'disabled'; ?>><?php echo $i; ?></button>
            <?php endfor; ?>
            <?php if ($current_page < $total_pages): ?>
                <button onclick="window.location.href='a-vrecords.php?page=<?php echo $current_page + 1; ?>'">Next</button>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
