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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #D1B8E1;
        }

        .navbar {
            background: #7F60A8;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .navbar ul {
            list-style: none;
            display: flex;
            gap: 15px;
        }

        .navbar ul li a {
            text-decoration: none;
            color: white;
            font-weight: bold;
        }

        .container {
            margin: 20px;
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
    <nav class="navbar">
        <h2>Admin Dashboard</h2>
        <ul>
            <li><a href="a-dashboard.php">Home</a></li>
            <li><a href="a-students.php">Students</a></li>
            <li><a href="a-currents.php">Current Sit-in</a></li>
            <li><a href="a-vrecords.php">Visit Records</a></li>
            <li><a href="a-reports.php">Reports</a></li>
            <li><a href="a-logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <h2 style="text-align: center;">Generate Reports</h2>
        <form method="get" class="filters">
            <label for="lab">Filter by Lab:</label>
            <select name="lab" id="lab">
                <option value="">All</option>
                <option value="Lab 1" <?php if ($filter_lab == 'Lab 1') echo 'selected'; ?>>Lab 1</option>
                <option value="Lab 2" <?php if ($filter_lab == 'Lab 2') echo 'selected'; ?>>Lab 2</option>
                <option value="Lab 3" <?php if ($filter_lab == 'Lab 3') echo 'selected'; ?>>Lab 3</option>
            </select>
            <label for="purpose">Filter by Purpose:</label>
            <select name="purpose" id="purpose">
                <option value="">All</option>
                <option value="Research" <?php if ($filter_purpose == 'Research') echo 'selected'; ?>>Research</option>
                <option value="Project" <?php if ($filter_purpose == 'Project') echo 'selected'; ?>>Project</option>
                <option value="Study" <?php if ($filter_purpose == 'Study') echo 'selected'; ?>>Study</option>
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
