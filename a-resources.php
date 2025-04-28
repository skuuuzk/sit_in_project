<?php
require_once('config/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

// Fetch admin username
$admin_id = $_SESSION['admin_id'];
$query = "SELECT username FROM admins WHERE id = ?";
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

$username = $admin['username'] ?? 'Admin';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_resource'])) {
    $file_name = $_FILES['resource_file']['name'];
    $file_tmp = $_FILES['resource_file']['tmp_name'];
    $upload_dir = 'uploads/resources/';
    $file_path = $upload_dir . basename($file_name);

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
    }

    if (move_uploaded_file($file_tmp, $file_path)) {
        $stmt = $conn->prepare("INSERT INTO resources (file_name, file_path, uploaded_by) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $file_name, $file_path, $username);
        $stmt->execute();
        $stmt->close();
        header("Location: a-resources.php?upload_success=1");
        exit();
    } else {
        header("Location: a-resources.php?upload_error=1");
        exit();
    }
}

// Handle file deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_resource'])) {
    $resource_id = $_POST['resource_id'];
    $file_path = $_POST['file_path'];

    if (file_exists($file_path)) {
        unlink($file_path); // Delete the file from the server
    }

    $stmt = $conn->prepare("DELETE FROM resources WHERE id = ?");
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();
    $stmt->close();

    header("Location: a-resources.php?delete_success=1");
    exit();
}

// Fetch resources
$query = "SELECT * FROM resources ORDER BY uploaded_at DESC";
$resources = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .main-content {
            margin-left: 15rem; /* Match the width of the navbar */
            padding: 1.5rem;
        }

        .resource-card {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .resource-card h3 {
            font-size: 1.25rem;
            font-weight: bold;
            color: #065f46;
        }

        .resource-card p {
            font-size: 1rem;
            color: #1a202c;
        }
    </style>
</head>
<body class="bg-cover bg-center h-screen" style="background-image: url('img/5.jpg');">
    <nav class="w-60 bg-green-700 bg-opacity-60 text-green-900 p-5 rounded-r-2xl shadow-lg fixed top-0 left-0 h-full">
        <div class="logo text-center mb-6">
            <img src="img/ccs.png" alt="Logo" class="w-20 h-20 object-cover rounded-full border-2 border-green-800 mx-auto">
            <p class="mt-2 text-white font-bold"><?php echo htmlspecialchars($username); ?></p>
        </div>
        <a href="a-dashboard.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700 active:bg-green-300">
            <i class="fas fa-user mr-3"></i> Home
        </a>
        <a href="a-students.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-users mr-3"></i> Students
        </a>
        <a href="a-vrecords.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-history mr-3"></i> Visit Records
        </a>
        <a href="a-feedback.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-comments mr-3"></i> Feedback
        </a>
        <a href="a-resources.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-folder mr-3"></i> Resources
        </a>
        <a href="a-logout.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
    </nav>

    <div class="main-content">
        <div class="text-center text-2xl font-bold text-green-900 mb-6">Resources</div>
        <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg mb-6">
            <h3 class="text-xl font-bold text-green-900 mb-4">Upload New Resource</h3>
            <form method="POST" action="a-resources.php" enctype="multipart/form-data" class="space-y-4">
                <input type="file" name="resource_file" class="block w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
                <button type="submit" name="upload_resource" class="w-full bg-green-700 text-white p-2 rounded hover:bg-green-800">Upload</button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($resources as $resource): ?>
                <div class="resource-card">
                    <h3><?php echo htmlspecialchars($resource['file_name']); ?></h3>
                    <p>Uploaded by: <?php echo htmlspecialchars($resource['uploaded_by']); ?></p>
                    <p>Uploaded at: <?php echo htmlspecialchars($resource['uploaded_at']); ?></p>
                    <div class="mt-4 flex justify-between">
                        <a href="<?php echo htmlspecialchars($resource['file_path']); ?>" target="_blank" class="text-blue-500 hover:underline">View</a>
                        <form method="POST" action="a-resources.php">
                            <input type="hidden" name="resource_id" value="<?php echo htmlspecialchars($resource['id']); ?>">
                            <input type="hidden" name="file_path" value="<?php echo htmlspecialchars($resource['file_path']); ?>">
                            <button type="submit" name="delete_resource" class="text-red-500 hover:underline">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
