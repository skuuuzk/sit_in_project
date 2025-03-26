
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .container {
            background: whitesmoke;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            text-align: center;
            border: #4d5572 2px solid;
        }

        .container h3 {
            text-align: center;
            margin-bottom: 30px;
            background-color: #4d5572;
            border-radius: 5px 5px 0 0;
            padding: 5px;
            color: white;
        }

        .container .form-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .container .form-group .form-field {
            width: 48%;
        }

        .container label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
            text-align: left;
        }

        .container input,
        .container select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .container button {
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

        .container button:hover {
            background-color: #4d5572b0;
            color: black;
        }

        .container .back-to-login {
            margin-top: 10px;
            display: block;
            color: black;
            text-decoration: none;
        }

        .container .back-to-login:hover {
            text-decoration: none;
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="container bg-white p-4 rounded shadow" style="max-width: 600px;">
        <h3 class="text-center bg-primary text-white p-2 rounded">Sign Up</h3>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="id_number" class="form-label">ID Number:</label>
                    <input type="text" class="form-control" id="id_number" name="id_number" required>
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="form-label">Last Name:</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="first_name" class="form-label">First Name:</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="col-md-6">
                    <label for="middle_name" class="form-label">Middle Name:</label>
                    <input type="text" class="form-control" id="middle_name" name="middle_name">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="course_level" class="form-label">Course Level:</label>
                    <select class="form-select" id="course_level" name="course_level" required>
                        <option value="" disabled selected>Select Course Level</option>
                        <option value="1st">1st</option>
                        <option value="2nd">2nd</option>
                        <option value="3rd">3rd</option>
                        <option value="4th">4th</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="course" class="form-label">Course:</label>
                    <select class="form-select" id="course" name="course" required>
                        <option value="" disabled selected>Select Course</option>
                        <option value="BSIT">BS Information Technology</option>
                        <option value="BSCS">BS Computer Science</option>
                        <option value="BSECE">BS Electronics & Communications Engineering</option>
                        <option value="BSBA">BS Business Administration</option>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="username" class="form-label">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="repeat_password" class="form-label">Repeat Password:</label>
                    <input type="password" class="form-control" id="repeat_password" name="repeat_password" required>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address:</label>
                <input type="text" class="form-control" id="address" name="address" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>