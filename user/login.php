
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #92929288;
        }
        .login-container {
            background: whitesmoke;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 25px 35px;
            border: #4d5572 2px solid;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 30px;
            background-color: #4d5572;
            border-radius: 5px 5px 0 0;
            padding: 5px;
            color: white;
        }
        .login-container label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
            text-align: left;
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
            background-color: #4d5572;
            color: whitesmoke;
            border: none;
            cursor: pointer;
            margin-top: 20px;
            font-size: 16px;
            border-radius: 5px;
        }
        .login-container button:hover {
            background-color: #4d5572b0;
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
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <?php if (isset($error)): ?>
                <p style="color: red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <button type="submit" name="login">Login</button>
            <a href="register.php" class="forgot-password">Don't have an account? Click here.</a>
        </form>
    </div>
</body>
</html>
