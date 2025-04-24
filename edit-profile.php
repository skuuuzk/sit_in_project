<?php
require_once('config/db.php'); // Updated path to the database file

// Ensure user is logged in
if (!isset($_SESSION['idno'])) {
    header("Location: login.php");
    exit();
}

$idno = $_SESSION['idno'];

// Fetch user data
$stmt = $conn->prepare("SELECT idno, firstname, lastname, midname, email, course, year, address, profile_pic, session FROM users WHERE idno = ?");
$stmt->bind_param("i", $idno);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'img/default.png';
$error_messages = array(); // Array to store different error messages

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $middlename = $_POST['midname'];
    $email = $_POST['email'];
    $course = $_POST['course'];
    $year_level = $_POST['year'];
    $address = $_POST['address'];
    $profile_pic_path = $user['profile_pic']; // Default to current profile picture

    // Profile Picture Upload - Update to use uploads directory in user folder
    if (!empty($_FILES['profile_pic']['name'])) {
        $upload_dir = "uploads/";  // Physical directory path for saving (in user folder)
        $db_path = "uploads/";  // Database path (relative to user directory)
        
        // Ensure the directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate a unique filename
        $file_extension = strtolower(pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION));
        $new_filename = "profile_" . $idno . "_" . time() . "." . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        // Store relative path in database
        $profile_pic_path = $db_path . $new_filename;

        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_extension, $allowed_types)) {
            $error_messages['profile'] = "Invalid file type! Only JPG, JPEG, PNG, and GIF are allowed.";
        } else {
            if (!move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $upload_path)) {
                $error_messages['profile'] = "Error uploading profile picture.";
            }
        }
    }

    // If no errors, update the user information
    if (empty($error_messages)) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update user details
            $update_stmt = $conn->prepare("UPDATE users SET firstname = ?, lastname = ?, midname = ?, email = ?, course = ?, year = ?, address = ?, profile_pic = ? WHERE idno = ?");
            $update_stmt->bind_param("ssssssssi", $firstname, $lastname, $middlename, $email, $course, $year_level, $address, $profile_pic_path, $idno);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Commit transaction
            $conn->commit();
            
            // Update session variables with new info
            $_SESSION['user'] = $firstname . ' ' . $lastname;
            $_SESSION['profile_pic'] = $profile_pic_path;
            $_SESSION['session_count'] = $user['session'];
            
            // Redirect to edit.php after successful update
            header("Location: edit.php");
            exit();
            
        } catch (Exception $e) {
            // Rollback the transaction if any part fails
            $conn->rollback();
            $error_messages['general'] = "Error updating profile: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-cover bg-center h-screen flex items-center justify-center" style="background-image: url('img/5.jpg');">
    <div class="bg-white bg-opacity-20 p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-bold text-center text-green-900 mb-6">Edit Profile</h2>
        <form action="edit-profile.php" method="post" class="space-y-4">
            <!-- Profile Picture -->
            <div class="form-group">
                <!-- Display current profile picture -->
                <div class="pic">
                    <img id="profileImage" src="<?php echo htmlspecialchars($_SESSION['profile_pic'] ?? $profile_pic); ?>" alt="Profile Picture" class="w-32 h-32 rounded-full mx-auto cursor-pointer">
                    <input type="file" name="profile_pic" id="profileInput" class="hidden" onchange="previewImage(event)">
                </div>
            </div>
                
            <!-- ID Number -->
            <div>
                <label for="idno" class="block font-bold text-green-900">ID Number:</label>
                <input type="text" id="idno" name="idno" value="<?php echo htmlspecialchars($user['idno']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" readonly>
            </div>

            <!-- First Name, Last Name, and Middle Name (side by side) -->
            <div class="flex space-x-4">
                <div class="flex-1">
                    <label for="firstname" class="block font-bold text-green-900">First Name:</label>
                    <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
                <div class="flex-1">
                    <label for="midname" class="block font-bold text-green-900">Middle Name:</label>
                    <input type="text" id="midname" name="midname" value="<?php echo htmlspecialchars($user['midname']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div class="flex-1">
                    <label for="lastname" class="block font-bold text-green-900">Last Name:</label>
                    <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
            </div>

            <!-- Year Level and Course (side by side) -->
            <div class="flex space-x-4">
                <div class="flex-1">
                    <label for="year" class="block font-bold text-green-900">Year Level:</label>
                    <select id="year" name="year" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option <?php if ($user['year'] == '1st Year') echo 'selected'; ?>>1st</option>
                        <option <?php if ($user['year'] == '2nd Year') echo 'selected'; ?>>2nd</option>
                        <option <?php if ($user['year'] == '3rd Year') echo 'selected'; ?>>3rd</option>
                        <option <?php if ($user['year'] == '4th Year') echo 'selected'; ?>>4th</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label for="course" class="block font-bold text-green-900">Course:</label>
                    <select id="course" name="course" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
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

            <!-- Address -->
            <div>
                <label for="email" class="block font-bold text-green-900">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
            </div>
            <div>
                <label for="address" class="block font-bold text-green-900">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
            </div>

            <button type="submit" class="w-full bg-green-700 text-white p-2 rounded hover:bg-green-800">Save Changes</button>
        </form>
    </div>
</body>
</html>
