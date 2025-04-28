<?php
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idno = $_POST['idno'];
    $lastname = $_POST['lastname'];
    $firstname = $_POST['firstname'];
    $midname = $_POST['midname'];
    $year = $_POST['year'];
    $course = $_POST['course'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $repeat_password = $_POST['repeat_password'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    if ($password !== $repeat_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: register.php");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (idno, lastname, firstname, midname, course, year, username, password, email, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssssss", $idno, $lastname, $firstname, $midname, $course, $year, $username, $hashed_password, $email, $address);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful. Please log in.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
        header("Location: register.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="container bg-white p-4 rounded shadow" style="max-width: 600px;">
        <h3 class="text-center bg-primary text-white p-2 rounded">Sign Up</h3>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="idno" class="form-label">ID Number:</label>
                    <input type="text" class="form-control" id="idno" name="idno" required>
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="form-label">Last Name:</label>
                    <input type="text" class="form-control" id="lastname" name="lastname" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="first_name" class="form-label">First Name:</label>
                    <input type="text" class="form-control" id="firstname" name="firstname" required>
                </div>
                <div class="col-md-6">
                    <label for="middle_name" class="form-label">Middle Name:</label>
                    <input type="text" class="form-control" id="midname" name="midname">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="course_level" class="form-label">Course Level:</label>
                    <select class="form-select" id="year" name="year" required>
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
                        <option value="BSIT">Bachelor of Science in Information Technology</option>
                        <option value="BSCS">Bachelor of Science in Computer Science</option>
                        <option value="BSECE">Bachelor of Science in Electronics Engineering</option>
                        <option value="BSCE">Bachelor of Science in Civil Engineering</option>
                        <option value="BSME">Bachelor of Science in Mechanical Engineering</option>
                        <option value="BSEE">Bachelor of Science in Electrical Engineering</option>
                        <option value="BSBA">Bachelor of Science in Business Administration</option>
                        <option value="BSA">Bachelor of Science in Accountancy</option>
                        <option value="BSHM">Bachelor of Science in Hospitality Management</option>
                        <option value="BSTM">Bachelor of Science in Tourism Management</option>
                        <option value="BSN">Bachelor of Science in Nursing</option>
                        <option value="BSED">Bachelor of Secondary Education</option>
                        <option value="BEED">Bachelor of Elementary Education</option>
                        <option value="BSPSY">Bachelor of Science in Psychology</option>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>