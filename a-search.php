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
    <title>Search</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-cover bg-center h-screen flex" style="background-image: url('img/5.jpg');">
    <nav class="w-60 bg-green-700 bg-opacity-60 text-green-900 p-5 rounded-r-2xl shadow-lg">
        <h2 class="text-2xl font-bold">Admin Dashboard</h2>
        <ul class="mt-4 space-y-2">
            <li><a href="a-dashboard.php" class="block text-white font-bold">Home</a></li>
            <li><a href="#" id="openSearch" class="block text-white font-bold">Search</a></li>
            <li><a href="a-students.php" class="block text-white font-bold">Students</a></li>
            <li><a href="a-currents.php" class="block text-white font-bold">Current Sit-in</a></li>
            <li><a href="a-vrecords.php" class="block text-white font-bold">Visit Records</a></li>
            <li><a href="a-logout.php" class="block text-white font-bold">Logout</a></li>
        </ul>
    </nav>

    <div class="flex-1 p-6 space-y-6">
        <div class="text-center text-2xl font-bold text-green-900">Search</div>
        <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg">
            <h3 class="text-xl font-bold text-green-900 mb-4">Search Records</h3>
            <div class="search-container">
                <h2 class="text-xl font-bold text-green-900 mb-4">Search Results</h2>
                <?php if (!empty($search_results)): ?>
                    <div class="search-results">
                        <table class="min-w-full bg-white bg-opacity-20 rounded-xl shadow-lg">
                            <thead>
                                <tr>
                                    <th class="py-2 px-4 border-b">ID</th>
                                    <th class="py-2 px-4 border-b">First Name</th>
                                    <th class="py-2 px-4 border-b">Last Name</th>
                                    <th class="py-2 px-4 border-b">Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $result): ?>
                                    <tr>
                                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($result['idno']); ?></td>
                                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($result['firstname']); ?></td>
                                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($result['lastname']); ?></td>
                                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($result['email']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-red-500">No results found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>