<?php
require_once('config/db.php');
require_once('vendor/autoload.php'); // For libraries like PhpSpreadsheet or Dompdf

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

// Initialize variables
$filter_lab = $_GET['lab'] ?? '';
$filter_purpose = $_GET['purpose'] ?? '';

// Fetch filtered sit-in records
$query = "SELECT r.idno, CONCAT(u.firstname, ' ', u.lastname) AS student_name, r.purpose, r.lab, r.time_in, r.time_out 
          FROM reservations r 
          JOIN users u ON r.idno = u.idno 
          WHERE r.status = 'completed'";

if (!empty($filter_lab)) {
    $query .= " AND r.lab = '" . mysqli_real_escape_string($conn, $filter_lab) . "'";
}
if (!empty($filter_purpose)) {
    $query .= " AND r.purpose = '" . mysqli_real_escape_string($conn, $filter_purpose) . "'";
}

$query .= " ORDER BY r.time_in DESC";
$result = mysqli_query($conn, $query);
$records = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Handle export to Excel
if (isset($_POST['export_excel'])) {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Sit-in Records');

    // Add headers
    $sheet->setCellValue('A1', 'ID Number')
          ->setCellValue('B1', 'Name')
          ->setCellValue('C1', 'Purpose')
          ->setCellValue('D1', 'Lab')
          ->setCellValue('E1', 'Time-in')
          ->setCellValue('F1', 'Time-out');

    // Add data
    $row = 2;
    foreach ($records as $record) {
        $sheet->setCellValue("A$row", $record['idno'])
              ->setCellValue("B$row", $record['student_name'])
              ->setCellValue("C$row", $record['purpose'])
              ->setCellValue("D$row", $record['lab'])
              ->setCellValue("E$row", $record['time_in'])
              ->setCellValue("F$row", $record['time_out']);
        $row++;
    }

    // Output to Excel file
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="sit_in_records.xlsx"');
    $writer->save('php://output');
    exit();
}

// Handle export to PDF
if (isset($_POST['export_pdf'])) {
    $html = '<h2>Sit-in Records</h2><table border="1" cellpadding="5" cellspacing="0">';
    $html .= '<thead><tr><th>ID Number</th><th>Name</th><th>Purpose</th><th>Lab</th><th>Time-in</th><th>Time-out</th></tr></thead><tbody>';
    foreach ($records as $record) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($record['idno']) . '</td>';
        $html .= '<td>' . htmlspecialchars($record['student_name']) . '</td>';
        $html .= '<td>' . htmlspecialchars($record['purpose']) . '</td>';
        $html .= '<td>' . htmlspecialchars($record['lab']) . '</td>';
        $html .= '<td>' . htmlspecialchars($record['time_in']) . '</td>';
        $html .= '<td>' . htmlspecialchars($record['time_out']) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';

    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream('sit_in_records.pdf', ['Attachment' => true]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-image: url(img/5.jpg); /* Background image */
            background-size: cover; /* Cover the entire viewport */
            display: flex;
        }

        .nav-container {
            width: 240px;
            background: rgba(255, 255, 255, 0.1); /* Transparent background */
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Soft shadow */
            backdrop-filter: blur(1px); /* Frosted glass effect */
            background-color:rgba(119, 152, 95, 0.54);
            color:rgb(11, 27, 3);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 10px 20px;
            border-radius: 0 20px 20px 0;
            justify-content: stretch;
        }

        .nav-container a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color:rgb(1, 23, 13);
            font-size: 16px;
            margin: 23.5px 0;
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
            margin: 25px auto;
            text-align: center;
        }

        .logo img {
            width: 70px;
            height: 70px; /* Set height to make it circular */
            object-fit: cover; /* Ensure the image covers the area */
            border-radius: 50%;
            border: 2px solid #475E53; /* Border around the image */
        }
        .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #4d5572; /* Background color */
            color: white; /* Text color */
            padding: 10px 0;
            text-align: center;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Add shadow for better visibility */
        }
        .container {
            flex-direction: column;
            gap: 20px;
            padding: 50px;
            justify-content: center;
            position: relative;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #333;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: whitesmoke;
            color: #333;
        }

        .filters {
            margin-bottom: 20px;
        }

        .filters label {
            margin-right: 10px;
        }

        .filters select {
            padding: 5px;
            margin-right: 10px;
        }

        .filters button {
            padding: 5px 10px;
            background-color: #4d5572;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .filters button:hover {
            background-color: #3a4256;
        }
    </style>
</head>
<body>
<nav class="nav-container"> 
        <div class="logo">
            <img src="img/ccs.png" alt="Logo" style="width: 100px; height: auto; margin-bottom: 20px;">
        </div>      
            <a href="a-dashboard.php"><i class="fas fa-user"></i><span>Home</span></a>
            <a href="#" onclick="openModal('searchModal')"><i class="fas fa-search"></i> <span>Search</span></a>
            <a href="a-students.php"><i class="fas fa-users"></i> <span>Students</span></a>
            <a href="a-currents.php"><i class="fas fa-user-clock"></i> <span>Current Sit-in</span></a>
            <a href="a-vrecords.php"><i class="fas fa-book"></i> <span>Visit Records</span></a>
            <a href="a-feedback.php"><i class="fas fa-comments"></i> <span>Feedback</span></a>
            <a href="a-reports.php" class="active"><i class="fas fa-chart-line"></i> <span>Reports</span></a>
            <a href="a-logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
    </nav>

    <div class="container">
        <div class="header-container">
            <header>
                <h1 >Generate Reports </h1>
            </header>
        </div>

    <div class="top-section">
        <form method="get" class="filters">
            <label for="lab">Filter by Lab:</label>
            <select name="lab" id="lab">
                <option value="">All</option>
                <option value="Lab 530" <?php if ($filter_lab == 'Lab 530') echo 'selected'; ?>>Lab 530</option>
                <option value="Lab 524" <?php if ($filter_lab == 'Lab 524') echo 'selected'; ?>>Lab 524</option>
                <option value="Lab 526" <?php if ($filter_lab == 'Lab 526') echo 'selected'; ?>>Lab 526</option>
                <option value="Lab 542" <?php if ($filter_lab == 'Lab 542') echo 'selected'; ?>>Lab 542</option>
                <option value="Lab 540" <?php if ($filter_lab == 'Lab 540') echo 'selected'; ?>>Lab 540</option>
            </select>
            <label for="purpose">Filter by Purpose:</label>
            <select name="purpose" id="purpose">
                <option value="">All</option>
                <option value="Python" <?php if ($filter_purpose == 'Python') echo 'selected'; ?>>Python</option>
                <option value="Java" <?php if ($filter_purpose == 'Java') echo 'selected'; ?>>Java</option>
                <option value="ASP .net" <?php if ($filter_purpose == 'ASP .net') echo 'selected'; ?>>ASP .net</option>
                <option value="C Programming" <?php if ($filter_purpose == 'C Programming') echo 'selected'; ?>>C Programming</option>
            </select>
            <button type="submit">Filter</button>
        </form>
        <form method="post">
            <button type="submit" name="export_excel" style="background-color: #28a745; color: white; padding: 8px 12px; border: none; cursor: pointer; margin-right: 5px;">
                📄 Export to Excel
            </button>
            <button type="submit" name="export_pdf" style="background-color: #007bff; color: white; padding: 8px 12px; border: none; cursor: pointer; margin-right: 5px;">
                🧾 Export to PDF
            </button>
            <button type="button" onclick="window.print()" style="background-color: #ffc107; color: black; padding: 8px 12px; border: none; cursor: pointer;">
                🖨️ Print Records
            </button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID Number</th>
                    <th>Name</th>
                    <th>Purpose</th>
                    <th>Lab</th>
                    <th>Time-in</th>
                    <th>Time-out</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($records)): ?>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['idno']); ?></td>
                            <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['purpose']); ?></td>
                            <td><?php echo htmlspecialchars($record['lab']); ?></td>
                            <td><?php echo htmlspecialchars($record['time_in']); ?></td>
                            <td><?php echo htmlspecialchars($record['time_out']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
