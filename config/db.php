<?php
session_start();
$host = 'localhost';
$user = 'root';
$password = ''; 
$dbname = 'sit_in';

try {
    // Establish connection
    $conn = new mysqli($host, $user, $password);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check if database exists, create if not
    $stmt = $conn->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
    $stmt->bind_param("s", $dbname);
    $stmt->execute();
    $db_check = $stmt->get_result();
    
    if ($db_check->num_rows == 0) {
        $stmt->close();
        if (!$conn->query("CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
            throw new Exception("Error creating database: " . $conn->error);
        }
    }
    
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    // Select the database
    $conn->select_db($dbname);

    // Table creation function
    function createTable($conn, $sql) {
        if (!$conn->query($sql)) {
            throw new Exception("Error creating table: " . $conn->error);
        }
    }

    // Users Table
    $usersTableSQL = "CREATE TABLE IF NOT EXISTS users (
        user_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        idno INT(11) UNIQUE NOT NULL,
        lastname VARCHAR(20) NOT NULL,
        firstname VARCHAR(20) NOT NULL,
        midname VARCHAR(20),
        course VARCHAR(50) NOT NULL,
        year VARCHAR(15) NOT NULL,
        username VARCHAR(30) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        session INT(11) DEFAULT 30,
        profile_pic VARCHAR(255) DEFAULT 'img/default.png',
        email VARCHAR(255) NULL,
        address VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    createTable($conn, $usersTableSQL);

    // Admin Table
    $adminTableSQL = "CREATE TABLE IF NOT EXISTS admin (
        admin_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    createTable($conn, $adminTableSQL);

    // Announcement Table
    $announcementTableSQL = "CREATE TABLE IF NOT EXISTS announcement (
        announce_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        admin_id INT(11),
        FOREIGN KEY (admin_id) REFERENCES admin(admin_id) ON DELETE CASCADE
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    createTable($conn, $announcementTableSQL);

    // Insert default admin user if not exists
    $admin_check = $conn->query("SELECT * FROM admin LIMIT 1");
    if ($admin_check->num_rows == 0) {
        $admin_username = 'admin';
        $admin_password = password_hash('password', PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $admin_username, $admin_password);
        if (!$stmt->execute()) {
            throw new Exception("Error inserting default admin: " . $stmt->error);
        }
    }

    // Create uploads directory if it doesn't exist
    $upload_dir = __DIR__ . '/user/uploads/';
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, true)) {
        throw new Exception("Failed to create uploads directory");
    }

    echo "";

} catch (Exception $e) {
    error_log($e->getMessage());
    die("Internal Server Error");
}

?>
