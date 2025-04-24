<?php
require_once('config/db.php'); // Assuming you have a file for database connection

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT admin_id, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($admin_id, $hashed_password);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        $_SESSION['admin_id'] = $admin_id;
        header("Location: a-dashboard.php");
        exit();
    } else {
        $error_message = 'Invalid username or password';
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-cover bg-center h-screen flex items-center justify-center" style="background-image: url('img/5.jpg');">
    <div class="bg-white bg-opacity-20 p-8 rounded-xl shadow-lg w-96">
        <h2 class="text-center text-2xl font-bold text-green-900 mb-6">Admin Login</h2>
        <form action="a-login.php" method="post" class="space-y-4">
            <div>
                <label for="username" class="block font-bold text-green-900">Username:</label>
                <input type="text" id="username" name="username" required class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label for="password" class="block font-bold text-green-900">Password:</label>
                <input type="password" id="password" name="password" required class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <button type="submit" class="w-full px-6 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800">Login</button>
        </form>
        <?php if ($error_message): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
