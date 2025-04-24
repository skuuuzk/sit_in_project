<?php
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['idno'] = $user['idno'];
            $_SESSION['user'] = $username;  // Store username in session

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-bold text-center text-green-900 mb-6">Login</h2>
        <form action="login.php" method="post" class="space-y-4">
            <div>
                <label for="username" class="block font-bold text-green-900">Username:</label>
                <input type="text" id="username" name="username" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
            </div>
            <div>
                <label for="password" class="block font-bold text-green-900">Password:</label>
                <input type="password" id="password" name="password" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
            </div>
            <?php if (isset($error)): ?>
                <p style="color: red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <button type="submit" name="login" class="w-full bg-green-700 text-white p-2 rounded hover:bg-green-800">Login</button>
            <a href="register.php" class="block text-center text-green-900 mt-4">Don't have an account? Click here.</a>
        </form>
    </div>
</body>
</html>