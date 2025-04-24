<?php
require_once('config/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

// Fetch labs for the dropdown
$labs_query = "SELECT DISTINCT lab FROM reservations";
$labs_result = mysqli_query($conn, $labs_query);
$labs = mysqli_fetch_all($labs_result, MYSQLI_ASSOC);

// Handle lab selection
$selected_lab = $_GET['lab'] ?? '';
$computers = [];
if (!empty($selected_lab)) {
    // Simulate fetching computers for the selected lab
    for ($i = 1; $i <= 60; $i++) {
        $computers[] = [
            'id' => $i,
            'name' => "PC $i",
            'status' => rand(0, 1) ? 'Available' : 'In Use' // Randomly assign status for demonstration
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-cover bg-center h-screen flex" style="background-image: url('img/5.jpg');">
    <nav class="w-60 bg-green-700 bg-opacity-60 text-green-900 p-5 rounded-r-2xl shadow-lg fixed top-0 left-0 h-full">
        <div class="logo text-center mb-6">
            <img src="img/ccs.png" alt="Logo" class="w-20 h-20 object-cover rounded-full border-2 border-green-800 mx-auto">
        </div>
        <a href="a-dashboard.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700 active:bg-green-300">
            <i class="fas fa-user mr-3"></i> Home
        </a>
        <a href="#" onclick="openModal('searchModal')" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-search mr-3"></i> Search
        </a>
        <a href="a-students.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-users mr-3"></i> Students
        </a>
        <a href="a-currents.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-user-clock mr-3"></i> Current Sit-in
        </a>
        <a href="a-vrecords.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-book mr-3"></i> Visit Records
        </a>
        <a href="a-feedback.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-comments mr-3"></i> Feedback
        </a>
        <a href="a-reports.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-chart-line mr-3"></i> Reports
        </a>
        <a href="a-leaderboard.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-trophy mr-3"></i> Leaderboard
        </a>
        <a href="a-computer-control.php" class="flex items-center text-white font-medium mb-5 p-3 rounded bg-green-800">
            <i class="fas fa-desktop mr-3"></i> Computer Control
        </a>
        <a href="a-logout.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
    </nav>

    <div class="flex-1 p-6 ml-60 space-y-6">
        <div class="text-center text-2xl font-bold text-green-900">Computer Control</div>
        <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg">
            <form method="GET" action="a-computer-control.php" class="mb-6">
                <label for="lab" class="block text-green-900 font-bold mb-2">Select Lab:</label>
                <select name="lab" id="lab" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">-- Select Lab --</option>
                    <?php foreach ($labs as $lab): ?>
                        <option value="<?php echo htmlspecialchars($lab['lab']); ?>" <?php echo $selected_lab == $lab['lab'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lab['lab']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="mt-4 bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800">Filter</button>
            </form>

            <?php if (!empty($computers)): ?>
                <form method="POST" action="a-computer-control.php">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-green-700 text-white">
                                <th class="border p-2">Select</th>
                                <th class="border p-2">Computer</th>
                                <th class="border p-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($computers as $computer): ?>
                                <tr class="bg-white bg-opacity-50">
                                    <td class="border p-2 text-center">
                                        <input type="checkbox" name="computers[]" value="<?php echo $computer['id']; ?>" <?php echo $computer['status'] == 'In Use' ? 'disabled' : ''; ?>>
                                    </td>
                                    <td class="border p-2"><?php echo htmlspecialchars($computer['name']); ?></td>
                                    <td class="border p-2 <?php echo $computer['status'] == 'Available' ? 'text-green-700' : 'text-red-700'; ?>">
                                        <?php echo htmlspecialchars($computer['status']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="mt-4 bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800">Apply Changes</button>
                </form>
            <?php else: ?>
                <p class="text-center text-green-900">No computers found for the selected lab.</p>
            <?php endif; ?>
        </div>

        <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg">
            <h3 class="text-xl font-bold text-green-900 mb-4">Guide</h3>
            <p class="text-green-700"><strong>Available:</strong> Computers that are free to use.</p>
            <p class="text-red-700"><strong>In Use:</strong> Computers currently being used.</p>
        </div>
    </div>
</body>
</html>
