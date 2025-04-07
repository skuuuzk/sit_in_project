<?php
require_once('config/db.php'); // Assuming you have a file for database connection

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

$search_results = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_query'])) {
    $search_query = $_POST['search_query'];
    $stmt = $conn->prepare("SELECT idno, firstname, lastname, email FROM users WHERE firstname LIKE ? OR lastname LIKE ? OR email LIKE ?");
    $like_query = '%' . $search_query . '%';
    $stmt->bind_param("sss", $like_query, $like_query, $like_query);
    $stmt->execute();
    $result = $stmt->get_result();
    $search_results = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Search</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #92929288;
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
        .search-container {
            margin: 20px;
        }
        .search-results {
            margin-top: 20px;
        }
        .search-results table {
            width: 100%;
            border-collapse: collapse;
        }
        .search-results th, .search-results td {
            border: 1px solid #333;
            padding: 10px;
            text-align: left;
        }
        .search-results th {
            background-color: whitesmoke;
            color: #333;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h2>Admin Dashboard</h2>
        <ul>
            <li><a href="a-dashboard.php">Home</a></li>
            <li><a href="#" id="openSearch">Search</a></li>
            <li><a href="a-students.php">Students</a></li>
            <li><a href="a-currents.php">Current Sit-in</a></li>
            <li><a href="a-vrecords.php">Visit Records</a></li>
            <li><a href="a-logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="search-container">
        <h2>Search Results</h2>
        <?php if (!empty($search_results)): ?>
            <div class="search-results">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($search_results as $result): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($result['idno']); ?></td>
                                <td><?php echo htmlspecialchars($result['firstname']); ?></td>
                                <td><?php echo htmlspecialchars($result['lastname']); ?></td>
                                <td><?php echo htmlspecialchars($result['email']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No results found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
