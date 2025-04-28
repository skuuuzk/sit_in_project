<?php

include 'config/db.php'; // Assuming you have a file for database connection

// Ensure user is logged in
if (!isset($_SESSION['idno'])) {
    header("Location: login.php");
    exit();
}

// Fetch user information from the database
$idno = $_SESSION['idno'];
$query = "SELECT idno AS idno, firstname AS FIRSTNAME, midname AS MIDNAME, lastname AS LASTNAME, year AS YEAR, course AS COURSE, email AS EMAIL, address AS ADDRESS, profile_pic FROM users WHERE idno = '$idno'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Fetch session count
$query = "SELECT session AS session_count FROM users WHERE idno = '$idno'";
$result = mysqli_query($conn, $query);
$session_data = mysqli_fetch_assoc($result);
$session_count = $session_data['session_count'];
$full_name = $user['FIRSTNAME'] . ' ' . (!empty($user['MIDNAME']) ? $user['MIDNAME'] . ' ' : '') . $user['LASTNAME'];
$profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'img/default.png';

// Fetch unread notifications count
$query = "SELECT COUNT(*) AS unread_count FROM notifications WHERE idno = '$idno' AND is_read = 0";
$result = mysqli_query($conn, $query);
$notification_data = mysqli_fetch_assoc($result);
$unread_count = $notification_data['unread_count'] ?? 0;

// Handle form submission to update user details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $midname = trim($_POST['midname']);
    $year = trim($_POST['year']);
    $course = trim($_POST['course']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);

    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        // Ensure the uploads directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileExtension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $uploadFile = $uploadDir . 'profile_' . $idno . '.' . $fileExtension;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
            $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE idno = ?");
            $stmt->bind_param("si", $uploadFile, $idno);
            $stmt->execute();
            $stmt->close();
            $profile_pic = $uploadFile; // Update the profile picture variable
        }
    }

    // Update user details
    $stmt = $conn->prepare("UPDATE users SET firstname = ?, lastname = ?, year = ?, course = ?, email = ?, address = ? WHERE idno = ?");
    $stmt->bind_param("ssssssi", $firstname, $lastname, $year, $course, $email, $address, $idno);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: edit.php?success=1");
        exit();
    } else {
        $error_message = "Error updating details: " . $stmt->error;
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">  
</head>
<body class="bg-cover bg-center h-screen flex items-center justify-center" style="background-image: url('img/5.jpg');">
    <nav class="w-60 bg-green-700 bg-opacity-60 text-green-900 p-5 rounded-r-2xl shadow-lg fixed top-0 left-0 h-full">
        <div class="logo text-center mb-6">
            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" class="w-24 h-24 object-cover rounded-full border-2 border-green-800 mx-auto">
            <p class="mt-2 text-white font-bold"><?php echo htmlspecialchars($full_name); ?></p>
            <p class="text-sm text-gray-200"><strong>Session:</strong> <?php echo htmlspecialchars($session_count); ?></p>  
        </div>
        <a href="dashboard.php" class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800">
            <i class="fas fa-user mr-3"></i> Home
        </a>
        <a href="edit.php" class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800">
            <i class="fas fa-edit mr-3"></i> Profile
        </a>
        <a href="reservation.php" class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800">
            <i class="fas fa-calendar-check mr-3"></i> Reservation
        </a>
        <a href="history.php" class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800">
            <i class="fas fa-history mr-3"></i> History
        </a>
        <a href="notification.php" class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800">
            <i class="fas fa-bell mr-3"></i> Notifications <?php if ($unread_count > 0) echo "($unread_count)"; ?>
        </a>
        <a href="logout.php" class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800">
            <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
    </nav>
    <div class="bg-white bg-opacity-20 p-8 rounded-lg shadow-lg w-106">
    <h2 class="text-2xl font-bold text-center text-green-900 mb-6">Student's Profile</h2>

    <?php if (isset($error_message)): ?>
        <p class="text-red-700 text-center mb-4"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <form action="edit.php" method="post" class="space-y-8" enctype="multipart/form-data">
        <!-- Profile Image -->
        <div class="flex justify-center mb-6">
            <label for="profile_image" class="cursor-pointer">
                <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Image" class="w-24 h-24 rounded-full object-cover border-2 border-green-500">
                <input type="file" id="profile_image" name="profile_image" accept="image/*" class="hidden" onchange="previewImage(event)">
            </label>
        </div>

        <!-- ID Number -->
        <div class="mb-4">
            <label for="idno" class="block font-bold text-green-900">ID Number:</label>
            <input type="text" id="idno" name="idno" value="<?php echo htmlspecialchars($user['idno']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" readonly>
        </div>

        <!-- First Name, Middle Name, Last Name on the same row -->
        <div class="flex space-x-6 mb-6">
            <div class="flex-1">
                <label for="firstname" class="block font-bold text-green-900">First Name:</label>
                <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['FIRSTNAME']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="flex-1">
                <label for="middlename" class="block font-bold text-green-900">Middle Name:</label>
                <input type="text" id="middlename" name="midname" value="<?php echo htmlspecialchars($user['MIDNAME']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="flex-1">
                <label for="lastname" class="block font-bold text-green-900">Last Name:</label>
                <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['LASTNAME']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
        </div>

        <!-- Year Level and Course on the same row -->
        <div class="flex space-x-6 mb-6">
            <div class="flex-1">
                <label for="year" class="block font-bold text-green-900">Year Level:</label>
                <select id="year" name="year" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="1st" <?php if ($user['YEAR'] == '1st') echo 'selected'; ?>>1st</option>
                    <option value="2nd" <?php if ($user['YEAR'] == '2nd') echo 'selected'; ?>>2nd</option>
                    <option value="3rd" <?php if ($user['YEAR'] == '3rd') echo 'selected'; ?>>3rd</option>
                    <option value="4th" <?php if ($user['YEAR'] == '4th') echo 'selected'; ?>>4th</option>
                </select>
            </div>
            <div class="flex-1">
                <label for="course" class="block font-bold text-green-900">Course:</label>
                <select id="course" name="course" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="BSIT" <?php if ($user['COURSE'] == 'BSIT') echo 'selected'; ?>>Bachelor of Science in Information Technology</option>
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

        <!-- Email and Address on the same row -->
        <div class="flex space-x-6 mb-6">
            <div class="flex-1">
                <label for="email" class="block font-bold text-green-900">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['EMAIL']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="flex-1">
                <label for="address" class="block font-bold text-green-900">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['ADDRESS']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
        </div>

        <!-- Save Changes Button -->
        <button type="submit" class="w-full bg-green-700 text-white p-3 rounded hover:bg-green-800 focus:ring-4 focus:ring-green-300 transition-all duration-300">
            Save Changes
        </button>
            <!-- Toast Message -->
            <div id="toast" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-green-500 text-white px-6 py-3 rounded shadow-lg hidden">
                Changes Saved Successfully!
            </div>
        </form>
    </div>

    <script>
        // Image preview function
        function previewImage(event) {
            const output = document.querySelector('img[alt="Profile Image"]');
            output.src = URL.createObjectURL(event.target.files[0]);
            document.querySelector('.logo img').src = output.src; // Update the navbar image dynamically
        }
    </script>
</body>
</html>
