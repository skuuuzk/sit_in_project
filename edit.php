<?php

include 'config/db.php'; // Assuming you have a file for database connection

// Ensure user is logged in
if (!isset($_SESSION['idno'])) {
    header("Location: login.php");
    exit();
}

// Fetch user information from the database
$idno = $_SESSION['idno'];
$query = "SELECT idno AS idno, firstname AS FIRSTNAME, lastname AS LASTNAME, year AS YEAR, course AS COURSE, email AS EMAIL, address AS ADDRESS, profile_pic FROM users WHERE idno = '$idno'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Fetch session count
$query = "SELECT session AS session_count FROM users WHERE idno = '$idno'";
$result = mysqli_query($conn, $query);
$session_data = mysqli_fetch_assoc($result);
$session_count = $session_data['session_count'];
$full_name = $user['FIRSTNAME'] . ' ' . $user['LASTNAME'];
$profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'img/default.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-cover bg-center h-screen flex items-center justify-center" style="background-image: url('img/5.jpg');">
    <div class="bg-white bg-opacity-20 p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-bold text-center text-green-900 mb-6">Edit Details</h2>
        <form action="edit.php" method="post" class="space-y-4">
            <div>
                <label for="idno" class="block font-bold text-green-900">ID Number:</label>
                <input type="text" id="idno" name="idno" value="<?php echo htmlspecialchars($user['idno']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" readonly>
            </div>
            <div>
                <label for="firstname" class="block font-bold text-green-900">First Name:</label>
                <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['FIRSTNAME']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label for="lastname" class="block font-bold text-green-900">Last Name:</label>
                <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['LASTNAME']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label for="year" class="block font-bold text-green-900">Year Level:</label>
                <input type="text" id="year" name="year" value="<?php echo htmlspecialchars($user['YEAR']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label for="course" class="block font-bold text-green-900">Course:</label>
                <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($user['COURSE']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label for="email" class="block font-bold text-green-900">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['EMAIL']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label for="address" class="block font-bold text-green-900">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['ADDRESS']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <button type="submit" class="w-full bg-green-700 text-white p-2 rounded hover:bg-green-800">Save Changes</button>
        </form>
    </div>
</body>
</html>
