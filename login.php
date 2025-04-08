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
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <title>Login</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url(img/5.jpg); /* Background image */
            background-size: cover; /* Cover the entire viewport */
        }

        .login-container {
            background: rgba(255, 255, 255, 0.1); /* Transparent background */
            color:#DEE9DC;
            padding: 30px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            border: #475E53 2px solid;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Soft shadow */
            backdrop-filter: blur(10px); /* Frosted glass effect */
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 30px;
            background-color: #475E53;
            border-radius: 5px 5px 0 0;
            padding: 5px;
            color: #DEE9DC;
        }

        .login-container label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
            text-align: left;
            color:rgb(39, 51, 46);
        }

        .login-container input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .login-container button {
            width: 100%;
            padding: 15px;
            background-color: #475E53;
            color: whitesmoke;
            border: none;
            cursor: pointer;
            margin-top: 20px;
            font-size: 16px;
            border-radius: 5px;
            
        }

        .login-container button:hover {
            background-color: #DEE9DC;
            color: black;
        }

        .login-container .forgot-password {
            margin-top: 10px;
            display: block;
            color: black;
            text-decoration: none;
        }

        .login-container .forgot-password:hover {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form method="POST" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Password" required>

            <?php if (isset($error)): ?>
                <p style="color: red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <button type="submit" name="login">Login</button>
            <a href="register.php" class="forgot-password">Don't have an account? Click here.</a>
        </form>
    </div>
</body>
</html>