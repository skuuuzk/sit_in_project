

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #92929288;
            display: flex;
            height: 100vh;
        }        
        .nav-container {
            width: 200px;
            background-color: whitesmoke;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 10px 20px;
            border-right: #4d5572  2px solid;
        }

        .nav-container a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #333;
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
            background-color: #929292;
            color: white;       
        }

        .nav-container a.active {
            font-weight: bold;
            background-color: #e0e0e0;
        }

        .logo {
            margin: 50px auto;
            text-align: center;
        }

        .logo img {
            width: 80px;
        }
        .container { 
            flex: 1; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            padding: 20px;
        }
        .form-container { 
            background: whitesmoke; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
            max-width: 450px; 
            width: 100%;
            border: 2px solid #4d5572; 
        }
        .form-container h2 { 
            text-align: center;
            background-color: #4d5572;
            border-radius: 5px 5px 0 0;
            padding: 5px;
            color: white;
            margin: -30px -30px 20px -30px;
        }
        label { 
            font-weight: bold; 
            display: block; 
            margin-top: 10px; 
        }
        input, select, button { 
            width: 100%; 
            padding: 10px; 
            margin-top: 5px; 
            border: 1px solid #ccc; 
            border-radius: 5px;         
        }
        button { 
            background-color: #4d5572; 
            color: white; 
            border: none; 
            cursor: pointer; 
            margin-top: 20px; 
            padding: 15px; 
            font-size: 16px; 
            border-radius: 5px;
        }
        button:hover {
            background-color: #3a4256;
        }
    </style>
</head>
<body>
    <div class="nav-container">
        <div class="logo">
            <img src="img/default.png" alt="Logo">
            <p style="text-align: center;"> <?php echo htmlspecialchars($student_name); ?> </p>
            <p><strong>Session:</strong> <?php echo htmlspecialchars($remaining_sessions); ?></p>
        </div>
        <a href="dashboard.php"><i class="fas fa-user"></i><span>Home</span></a>
        <a href="edit.php"><i class="fas fa-edit"></i><span>Profile</span></a>
        <a href="reservation.php" class="active"><i class="fas fa-calendar-check"></i><span> Reservation</span></a>
        <a href="history.php"><i class="fas fa-history"></i><span> History</span></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
    </div>
    <div class="container">
        <div class="form-container">
            <h2>Reservation</h2>
            <form action="" method="POST">
                <label>ID Number:</label>
                <input type="text" value="<?php echo htmlspecialchars($user_id); ?>" readonly>
                <label>Student Name:</label>
                <input type="text" value="<?php echo htmlspecialchars($student_name); ?>" readonly>
                <label>Purpose:</label>
                <select name="purpose">
                    <option>C Programming</option>
                    <option>Python</option>
                    <option selected>ASP .net</option>
                    <option>Java</option>
                </select>
                <label>Lab:</label>
                <input type="text" name="lab" required>
                <label>Time In:</label>
                <input type="time" name="time_in" required>
                <label>Date:</label>
                <input type="date" name="date" required>
                <label>Remaining Session:</label>
                <input type="text" value="<?php echo htmlspecialchars($remaining_sessions); ?>" readonly>
                <button type="submit">Reserve</button>
            </form>
        </div>
    </div>
</body>
</html>
