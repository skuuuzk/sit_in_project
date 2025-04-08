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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { 
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-image: url(img/5.jpg); /* Background image */
            background-size: cover; /* Cover the entire viewport */
            display: flex;
            height: 100vh;
        }        
        .nav-container { 
            width: 237px; 
            background: rgba(255, 255, 255, 0.1); /* Transparent background */
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Soft shadow */
            backdrop-filter: blur(1px); /* Frosted glass effect */
            background-color:rgba(119, 152, 95, 0.54);
            color:rgb(11, 27, 3);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 10px 20px;
            border-radius: 0 20px 20px 0;
        }
        .nav-container a { 
            display: flex;
            align-items: center;
            text-decoration: none;
            color:rgb(1, 23, 13);
            font-size: 16px;
            margin: 30px 0;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .nav-container a i { 
            margin-right: 10px; 
            font-size: 18px; 
        }
        .nav-container a:hover { 
            background-color:#DEE9DC;
            color: seagreen;  
        }
        .nav-container a.active { 
            font-weight: bold; 
            background-color: #BACEAB; 
        }
        .logo { 
            margin: 50px auto; 
            text-align: center; 
        }
        .logo img { 
            width: 90px;
            height: 90px; /* Set height to make it circular */
            object-fit: cover; /* Ensure the image covers the area */
            border-radius: 50%;
            border: 2px solid #475E53; /* Border around the image */
        }
        .container { 
            margin:auto; 
            display: flex; 
            justify-content: center; 
            align-items: stretch; 
            height: 100vh; 
            width: 100%; 
        }
        .edit-profile-card { 
            width: 500px;
            height: auto; 
            padding: 35px; 
            display: flex;
            flex-direction: column;
            text-align: justify; 
            border-radius: 10px; 
            background: rgba(255, 255, 255, 0.1); /* Transparent background */
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Soft shadow */
            backdrop-filter: blur(10px); /* Frosted glass effect */
            border: 3px solid #929292;
            overflow: clip; 
        }
        .title { 
            text-align: center;
            background-color: #475E53;
            border-radius: 5px 5px 0 0;
            padding: 10px;
            color: white;
        }
        /* General styling for form groups */
        .form-group {
            margin-bottom: auto;
        }

        /* For row layout: First Name, Last Name, Middle Name side by side */
        .row {
            display: flex;
            gap: 30px; /* Adjust the gap between columns */
        }

        .col {
            flex: 1; /* Each column takes equal space */
        }

        /* To make sure the select elements are aligned correctly in the same row */
        select, input[type="text"], input[type="email"] {
            width: 100%;
            padding: 5px;
        }

        label { 
            font-weight: 600; 
            display: block; 
            margin: 5px 0 3px; 
        }
        .buttons { 
            display: flex; 
            justify-content: space-between; 
            margin-top: 15px; 
        }
        .save-btn, .cancel-btn { 
            padding: 10px 15px; 
            border: none; 
            cursor: pointer; 
            flex: 1;
            border-radius: 5px; 
            font-size: 14px; 
        }
        .save-btn { 
            background-color: #4d5572; 
            color: white; 
            margin-right: 5px; 
        }
        .cancel-btn { 
            background-color: #4d5572a4; 
            color: white; 
            margin-left: 5px; 
        }
        .save-btn:hover { 
            background-color: #1565C0; 
        }
        .cancel-btn:hover { 
            background-color: #B71C1C; 
        }
    </style>
</head>
<body>
    <div class="nav-container">
        <div class="logo">
            <img src="<?php echo htmlspecialchars($_SESSION['profile_pic'] ?? $profile_pic); ?>" alt="Profile Picture">
            <p style="text-align: center;"> <?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></p>
            <p><strong>Session:</strong> <?= htmlspecialchars($_SESSION['session_count'] ?? $user['session']); ?></p>
        </div>
        <a href="dashboard.php"><i class="fas fa-user"></i><span>Home</span></a>
        <a href="edit.php" class="active"><i class="fas fa-edit"></i><span>Profile</span></a>
        <a href="reservation.php"><i class="fas fa-calendar-check"></i><span>Reservation</span></a>
        <a href="history.php"><i class="fas fa-history"></i><span>History</span></a>
        <a href="notification.php"><i class="fas fa-bell"></i><span>Notifications</span></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </div>

    <div class="container">
        <div class="edit-profile-card">
            <h2 class="title">Edit Profile</h2>
            <form action="edit-profile.php" method="post" enctype="multipart/form-data">

            <!-- Profile Picture -->
            <div class="form-group">

                <!-- Display current profile picture -->
                <div class="profile-pic">
                    <img id="profileImage" src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture">
                    <input type="file" name="profile_pic" id="profileInput" style="display: none;" onchange="previewImage(event)">
                </div>
            </div>
                
                <!-- ID Number -->
                <div class="form-group">
                    <label>ID Number:</label>
                    <input type="text" name="idno" value="<?php echo htmlspecialchars($user['idno']); ?>" readonly>
                </div>

                <!-- First Name, Last Name, and Middle Name (side by side) -->
                <div class="form-group row">
                    <div class="col">
                        <label>First Name:</label>
                        <input type="text" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
                    </div>
                    <div class="col">
                        <label>Middle Name:</label>
                        <input type="text" name="midname" value="<?php echo htmlspecialchars($user['midname']); ?>">
                    </div>
                    <div class="col">
                        <label>Last Name:</label>
                        <input type="text" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" required>
                    </div>
                </div>

                <!-- Year Level and Course (side by side) -->
                <div class="form-group row">
                    <div class="col">
                        <label>Year Level:</label>
                        <select name="year">
                            <option <?php if ($user['year'] == '1st Year') echo 'selected'; ?>>1st</option>
                            <option <?php if ($user['year'] == '2nd Year') echo 'selected'; ?>>2nd</option>
                            <option <?php if ($user['year'] == '3rd Year') echo 'selected'; ?>>3rd</option>
                            <option <?php if ($user['year'] == '4th Year') echo 'selected'; ?>>4th</option>
                        </select>
                    </div>
                    <div class="col">
                        <label>Course:</label>
                        <select name="course">
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
                <div class="form-group">
                    <div class="col">
                        <label>Email:</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="col">
                        <label>Address:</label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
                    </div>
                </div>

                <div class="buttons">
                    <button type="submit" class="save-btn">Save Changes</button>
                    <button type="button" class="cancel-btn" onclick="window.location.href='profile.php'">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    // Change the image when the user clicks the profile picture
        document.getElementById('profileImage').onclick = function() {
            document.getElementById('profileInput').click();
        };

        // Preview the selected image before submitting the form
        function previewImage(event) {
            var output = document.getElementById('profileImage');
            output.src = URL.createObjectURL(event.target.files[0]);
        }
    </script>
</body>
</html>
