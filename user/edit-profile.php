<?php 
session_start();
require_once('../config/db.php'); // Updated path to the database file

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT IDNO, LASTNAME, FIRSTNAME, USERNAME, COURSE, YEAR, SESSION, PROFILE_PIC FROM USERS WHERE USER_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fix profile picture path handling
$default_pic = "images/snoopy.jpg";  // Default relative to user directory
$profile_pic = !empty($user['PROFILE_PIC']) ? $user['PROFILE_PIC'] : $default_pic;

$edit_mode = isset($_GET['edit']);
$error_messages = array(); // Array to store different error messages

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idno = $_POST['idno'];
    $lastname = $_POST['lastname'];
    $firstname = $_POST['firstname'];
    $username = $_POST['username'];
    $course = $_POST['course'];
    $year = $_POST['year'];
    $profile_pic_path = $user['PROFILE_PIC']; // Default to current profile picture
    
    // Password change fields (optional)
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $password_changed = false;

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
        $new_filename = "profile_" . $user_id . "_" . time() . "." . $file_extension;
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

    // Check for unique ID Number
    $check_id_stmt = $conn->prepare("SELECT USER_ID FROM USERS WHERE IDNO = ? AND USER_ID != ?");
    $check_id_stmt->bind_param("si", $idno, $user_id);
    $check_id_stmt->execute();
    $check_id_result = $check_id_stmt->get_result();
    
    if ($check_id_result->num_rows > 0) {
        $error_messages['idno'] = "ID Number is already taken!";
    }
    $check_id_stmt->close();
    
    // Check for unique Username
    $check_username_stmt = $conn->prepare("SELECT USER_ID FROM USERS WHERE USERNAME = ? AND USER_ID != ?");
    $check_username_stmt->bind_param("si", $username, $user_id);
    $check_username_stmt->execute();
    $check_username_result = $check_username_stmt->get_result();
    
    if ($check_username_result->num_rows > 0) {
        $error_messages['username'] = "Username is already taken!";
    }
    $check_username_stmt->close();
    
    // Handle password change if requested
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        // Verify all password fields are filled
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_messages['password'] = "All password fields are required to change your password.";
        } else {
            // Verify current password
            $verify_stmt = $conn->prepare("SELECT PASSWORD FROM USERS WHERE USER_ID = ?");
            $verify_stmt->bind_param("i", $user_id);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            $user_data = $verify_result->fetch_assoc();
            $verify_stmt->close();
            
            if (!password_verify($current_password, $user_data['PASSWORD'])) {
                $error_messages['password'] = "Current password is incorrect.";
            } elseif ($new_password !== $confirm_password) {
                $error_messages['password'] = "New passwords don't match.";
            } elseif (strlen($new_password) < 8) {
                $error_messages['password'] = "New password must be at least 8 characters long.";
            } else {
                // Password change is valid
                $password_changed = true;
            }
        }
    }

    // If no errors, update the user information
    if (empty($error_messages)) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update user details
            $update_stmt = $conn->prepare("UPDATE USERS SET IDNO = ?, LASTNAME = ?, FIRSTNAME = ?, USERNAME = ?, COURSE = ?, YEAR = ?, PROFILE_PIC = ? WHERE USER_ID = ?");
            $update_stmt->bind_param("sssssssi", $idno, $lastname, $firstname, $username, $course, $year, $profile_pic_path, $user_id);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Update password if changed
            if ($password_changed) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $password_stmt = $conn->prepare("UPDATE USERS SET PASSWORD = ? WHERE USER_ID = ?");
                $password_stmt->bind_param("si", $hashed_password, $user_id);
                $password_stmt->execute();
                $password_stmt->close();
            }
            
            // Commit transaction
            $conn->commit();
            
            // Update session variables with new info
            $_SESSION['username'] = $username;
            
            // Redirect to profile page after successful update
            header("Location: profile.php");
            exit();
            
        } catch (Exception $e) {
            // Rollback the transaction if any part fails
            $conn->rollback();
            $error_messages['general'] = "Error updating profile: " . $e->getMessage();
        }
    }
}

$pageTitle = "Profile";
$bodyClass = "bg-light font-poppins"; // Changed from Montserrat to Poppins
include('includes/header.php');
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { display: flex; margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #92929288; }        
        .nav-container { width: 237px; background-color: whitesmoke; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); padding: 10px 20px; border-right: #4d5572  2px solid; }
        .nav-container a { display: flex; align-items: center; text-decoration: none; color: #333; font-size: 16px; margin: 30px 0; padding: 10px; border-radius: 5px; transition: background-color 0.3s ease; }
        .nav-container a i { margin-right: 10px; font-size: 18px; }
        .nav-container a:hover { background-color: #929292; color: white; }
        .nav-container a.active { font-weight: bold; background-color: #e0e0e0; }
        .logo { margin: 50px auto; text-align: center; }
        .logo img { width: 80px; }
        .container { margin:auto; display: flex; justify-content: center; align-items: center; height: 100vh; width: 100%; }
        .edit-profile-card { background-color: whitesmoke; width: 600px; padding: 20px; text-align: center; border-radius: 10px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); border: 3px solid #929292; }
        .title { text-align: center; margin-bottom: 20px; background-color: #4d5572; border-radius: 5px 5px 0 0; padding: 5px; color: white; }
        .profile-img img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; background-color: #929292; }
        form { text-align: left; padding: 10px; }
        .form-group { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 25px; }
        .form-group .form-field { width: 47%; margin-bottom: 10px; }
        label { font-weight: 600; display: block; margin: 8px 0 3px; }
        input, select { width: 100%; padding: 8px; border: 1px solid #929292; border-radius: 5px; }
        .buttons { display: flex; justify-content: space-between; margin-top: 15px; }
        .save-btn, .cancel-btn { padding: 10px 15px; border: none; cursor: pointer; flex: 1; border-radius: 5px; font-size: 14px; }
        .save-btn { background-color: #4d5572; color: white; margin-right: 5px; }
        .cancel-btn { background-color: #4d5572a4; color: white; margin-left: 5px; }
        .save-btn:hover { background-color: #1565C0; }
        .cancel-btn:hover { background-color: #B71C1C; }
    </style>
</head>
<body>
    <div class="nav-container">
        <div class="logo">
            <img src="img/default.png" alt="Profile Picture">
            <p style="text-align: center;"> <?= $_SESSION['user'] ?? 'Guest' ?></p>
            <p><strong>Session:</strong> <?= $_SESSION['session_count'] ?? '0' ?></p>
        </div>
        <a href="dashboard.php"><i class="fas fa-user"></i><span>Home</span></a>
        <a href="edit.php" class="active"><i class="fas fa-edit"></i><span>Profile</span></a>
        <a href="reservation.php"><i class="fas fa-calendar-check"></i><span>Reservation</span></a>
        <a href="history.php"><i class="fas fa-history"></i><span>History</span></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </div>
    <div class="container">
        <div class="edit-profile-card">
            <h2 class="title">Edit Profile</h2>
            <form  action="edit.php" method="post">
                <label>First Name:</label>
                    <input type="text" name="firstname" value="<?php echo htmlspecialchars($user['FIRSTNAME']); ?>" required>

                    <label>Last Name:</label>
                    <input type="text" name="lastname" value="<?php echo htmlspecialchars($user['LASTNAME']); ?>" required>

                    <label>Middle Name:</label>
                    <input type="text" name="middlename" value="<?php echo htmlspecialchars($user['MIDDLENAME']); ?>">

                    <label>Email:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['EMAIL']); ?>" required>

                    <label>Course:</label>
                    <select name="course">
                        <option <?php if ($user['COURSE'] == 'BSIT') echo 'selected'; ?>>BSIT</option>
                        <option <?php if ($user['COURSE'] == 'BSCS') echo 'selected'; ?>>BSCS</option>
                        <option <?php if ($user['COURSE'] == 'BSECE') echo 'selected'; ?>>BSECE</option>
                    </select>

                    <label>Year Level:</label>
                    <select name="year_level">
                        <option <?php if ($user['YEAR_LEVEL'] == '1st Year') echo 'selected'; ?>>1st Year</option>
                        <option <?php if ($user['YEAR_LEVEL'] == '2nd Year') echo 'selected'; ?>>2nd Year</option>
                        <option <?php if ($user['YEAR_LEVEL'] == '3rd Year') echo 'selected'; ?>>3rd Year</option>
                        <option <?php if ($user['YEAR_LEVEL'] == '4th Year') echo 'selected'; ?>>4th Year</option>
                    </select>

                    <label>Address:</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($user['ADDRESS']); ?>" required>

                    <label>Profile Picture:</label>
                    <input type="file" name="profile_pic">

                    <button type="submit">Save Changes</button>
                    <button type="button" onclick="window.location.href='profile.php'">Cancel</button>
            </form>
        </div>
    </div>
</body>
</html>
