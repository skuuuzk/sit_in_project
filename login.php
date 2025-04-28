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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body class="h-screen flex items-center justify-center" style="background-image: url('img/5.jpg'); background-repeat: no-repeat; background-size: cover;">
<div class="relative bg-white p-8 rounded-xl shadow-lg w-96 overflow-hidden group hover:scale-105 hover:shadow-[0px_10px_30px_rgba(0,0,0,0.2)] transition-all duration-500">
    
    <!-- Animated Gradient Border -->
    <span class="absolute inset-0 border-4 rounded-xl border-transparent animate-gradient-border"></span>
    
    <h2 class="relative z-10 text-2xl font-bold text-center text-green-900 mb-6">Login</h2>
    <form action="login.php" method="post" class="relative z-10 space-y-4">
    
        <!-- Username Field -->
        <div class="relative mb-4">
            <label for="username" class="block font-bold text-green-900">Username:</label>
            <div class="relative">
                <input type="text" id="username" name="username" class="w-full p-2 border-2 border-gray-300 rounded focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-300 pl-10" required>
                <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>

        <!-- Password Field -->
        <div class="relative mb-4">
            <label for="password" class="block font-bold text-green-900">Password:</label>
            <div class="relative">
                <input type="password" id="password" name="password" class="w-full p-2 border-2 border-gray-300 rounded focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-300 pl-10" required>
                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <button type="button" id="togglePassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                    <i class="fas fa-eye"></i> <!-- Default Eye Open Icon -->
                </button>
            </div>
        </div>

        <!-- Error Message (if any) -->
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <!-- Login Button -->
        <button type="submit" name="login" class="w-full bg-green-700 text-white p-2 rounded hover:bg-green-800">Login</button>

        <!-- Link to Registration Page -->
        <a href="register.php" class="block text-center text-green-900 mt-4">Don't have an account? Click here.</a>
    </form>

    <script>
        // Toggle the password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;
            
            // Toggle the eye icon between open and closed
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    </script>

        <style>
             @keyframes gradientMove {
                0% {
                    border-image: linear-gradient(45deg, rgb(3, 38, 16), #22c55e, #4caf50) 1;
                }
                50% {
                    border-image: linear-gradient(45deg, #81c784, #66bb6a, #2e7d32) 1;
                }
                100% {
                    border-image: linear-gradient(45deg, #4caf50, #388e3c, #2b7a2e) 1;
                }
                }
                
                .animate-gradient-border {
                animation: gradientMove 10s linear infinite;
                z-index: 1;
                border-radius: 1rem;
                border-width: 4px;
                border-style: solid;
                padding: 0;
                }
        </style>
    </div>
</body>
</html>