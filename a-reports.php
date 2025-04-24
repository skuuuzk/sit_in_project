<?php
require_once('config/db.php');
require_once(__DIR__ . '/vendor/autoload.php'); // Ensure the correct path to autoload.php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: a-login.php");
    exit();
}

// Increase memory limit to handle large data exports
ini_set('memory_limit', '1G'); // Set memory limit to 1GB

// Initialize variables
$filter_lab = $_GET['lab'] ?? '';
$filter_purpose = $_GET['purpose'] ?? '';

// Fetch filtered sit-in records
$query = "SELECT r.idno, CONCAT(u.firstname, ' ', u.lastname) AS student_name, r.purpose, r.lab, r.time_in, r.time_out 
          FROM reservations r 
          JOIN users u ON r.idno = u.idno 
          WHERE r.status = 'completed'";

$conditions = [];
if (!empty($filter_lab)) {
    $conditions[] = "r.lab = '" . mysqli_real_escape_string($conn, $filter_lab) . "'";
}
if (!empty($filter_purpose)) {
    $conditions[] = "r.purpose = '" . mysqli_real_escape_string($conn, $filter_purpose) . "'";
}

if (!empty($conditions)) {
    $query .= " AND " . implode(" AND ", $conditions);
}

$query .= " ORDER BY r.time_in DESC";

// Pagination logic
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Modify query to include LIMIT and OFFSET for pagination
$query .= " LIMIT $records_per_page OFFSET $offset";
$result = mysqli_query($conn, $query);
$records = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get total number of records for pagination
$total_records_query = "SELECT COUNT(*) AS total FROM reservations r 
                        JOIN users u ON r.idno = u.idno 
                        WHERE r.status = 'completed'";
if (!empty($conditions)) {
    $total_records_query .= " AND " . implode(" AND ", $conditions);
}
$total_records_result = mysqli_query($conn, $total_records_query);
$total_records = mysqli_fetch_assoc($total_records_result)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Handle export to Excel
if (isset($_POST['export_excel'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Sit-in Records');

    // Add headers with formal styling
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

    // Apply styling
    $sheet->getStyle('A1:F1')->getFont()->setBold(true);
    $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal('center');
    $sheet->getStyle('A:F')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    // Output to Excel file
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="sit_in_records.xlsx"');
    $writer->save('php://output');
    exit();
}

// Handle export to PDF
if (isset($_POST['export_pdf'])) {
    $html = '
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="img/ucmn.png" alt="School Logo" style="width: 100px; height: auto;">
            <h2 style="font-family: Poppins, sans-serif; color: #4d5572;">Sit-in Records</h2>
        </div>
        <table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; font-family: Poppins, sans-serif; font-size: 12px;">
            <thead>
                <tr style="background-color: #4d5572; color: white; text-align: left;">
                    <th style="padding: 10px;">ID Number</th>
                    <th style="padding: 10px;">Name</th>
                    <th style="padding: 10px;">Purpose</th>
                    <th style="padding: 10px;">Lab</th>
                    <th style="padding: 10px;">Time-in</th>
                    <th style="padding: 10px;">Time-out</th>
                </tr>
            </thead>
            <tbody>';
    foreach ($records as $record) {
        $html .= '<tr>';
        $html .= '<td style="padding: 10px; border: 1px solid #333;">' . htmlspecialchars($record['idno']) . '</td>';
        $html .= '<td style="padding: 10px; border: 1px solid #333;">' . htmlspecialchars($record['student_name']) . '</td>';
        $html .= '<td style="padding: 10px; border: 1px solid #333;">' . htmlspecialchars($record['purpose']) . '</td>';
        $html .= '<td style="padding: 10px; border: 1px solid #333;">' . htmlspecialchars($record['lab']) . '</td>';
        $html .= '<td style="padding: 10px; border: 1px solid #333;">' . htmlspecialchars($record['time_in']) . '</td>';
        $html .= '<td style="padding: 10px; border: 1px solid #333;">' . htmlspecialchars($record['time_out']) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
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
    <title>Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-cover bg-center h-screen flex" style="background-image: url('img/5.jpg');">
    <nav class="w-60 bg-green-700 bg-opacity-60 text-green-900 p-5 rounded-r-2xl shadow-lg fixed top-0 left-0 h-full">
        <div class="logo text-center mb-5">
            <img src="img/ccs.png" alt="Logo" class="w-24 h-auto mx-auto mb-5 rounded-full border-2 border-green-900">
        </div>
        <a href="a-dashboard.php" class="flex items-center text-green-900 text-lg mb-6 p-2 rounded hover:bg-green-200">
            <i class="fas fa-user mr-2"></i> Home
        </a>
        <a href="#" onclick="openModal('searchModal')" class="flex items-center text-green-900 text-lg mb-6 p-2 rounded hover:bg-green-200">
            <i class="fas fa-search mr-2"></i> Search
        </a>
        <a href="a-students.php" class="flex items-center text-green-900 text-lg mb-6 p-2 rounded hover:bg-green-200">
            <i class="fas fa-users mr-2"></i> Students
        </a>
        <a href="a-currents.php" class="flex items-center text-green-900 text-lg mb-6 p-2 rounded hover:bg-green-200">
            <i class="fas fa-user-clock mr-2"></i> Current Sit-in
        </a>
        <a href="a-vrecords.php" class="flex items-center text-green-900 text-lg mb-6 p-2 rounded hover:bg-green-200">
            <i class="fas fa-book mr-2"></i> Visit Records
        </a>
        <a href="a-feedback.php" class="flex items-center text-green-900 text-lg mb-6 p-2 rounded hover:bg-green-200">
            <i class="fas fa-comments mr-2"></i> Feedback
        </a>
        <a href="a-reports.php" class="flex items-center text-green-900 text-lg mb-6 p-2 rounded bg-green-200 font-bold">
            <i class="fas fa-chart-line mr-2"></i> Reports
        </a>
        <a href="a-logout.php" class="flex items-center text-green-900 text-lg mb-6 p-2 rounded hover:bg-green-200">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
    </nav>

    <div class="flex-1 p-6 ml-60 space-y-6">
        <div class="text-center text-2xl font-bold text-green-900">Generate Reports</div>
        <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg">
            <form method="get" class="mb-6 flex space-x-4">
                <div>
                    <label for="lab" class="block text-green-900 mb-2">Filter by Lab:</label>
                    <select name="lab" id="lab" class="p-2 rounded border border-green-900">
                        <option value="">All</option>
                        <option value="Lab 530" <?php if ($filter_lab == 'Lab 530') echo 'selected'; ?>>Lab 530</option>
                        <option value="Lab 524" <?php if ($filter_lab == 'Lab 524') echo 'selected'; ?>>Lab 524</option>
                        <option value="Lab 526" <?php if ($filter_lab == 'Lab 526') echo 'selected'; ?>>Lab 526</option>
                        <option value="Lab 542" <?php if ($filter_lab == 'Lab 542') echo 'selected'; ?>>Lab 542</option>
                        <option value="Lab 540" <?php if ($filter_lab == 'Lab 540') echo 'selected'; ?>>Lab 540</option>
                    </select>
                </div>
                <div>
                    <label for="purpose" class="block text-green-900 mb-2">Filter by Purpose:</label>
                    <select name="purpose" id="purpose" class="p-2 rounded border border-green-900">
                        <option value="">All</option>
                        <option value="Python" <?php if ($filter_purpose == 'Python') echo 'selected'; ?>>Python</option>
                        <option value="Java" <?php if ($filter_purpose == 'Java') echo 'selected'; ?>>Java</option>
                        <option value="ASP .net" <?php if ($filter_purpose == 'ASP .net') echo 'selected'; ?>>ASP .net</option>
                        <option value="C Programming" <?php if ($filter_purpose == 'C Programming') echo 'selected'; ?>>C Programming</option>
                    </select>
                </div>
                <button type="submit" class="self-end bg-green-700 text-white p-2 rounded">Filter</button>
            </form>

            <form method="post" class="mb-6 flex space-x-4">
                <button type="submit" name="export_excel" class="bg-green-700 text-white p-2 rounded flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Export to Excel
                </button>
                <button type="submit" name="export_pdf" class="bg-red-700 text-white p-2 rounded flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Export to PDF
                </button>
                <button type="button" onclick="printTable()" class="bg-yellow-500 text-white p-2 rounded flex items-center">
                    <i class="fas fa-print mr-2"></i> Print Records
                </button>
            </form>

            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-green-700 text-white">
                        <th class="border p-2">ID Number</th>
                        <th class="border p-2">Name</th>
                        <th class="border p-2">Purpose</th>
                        <th class="border p-2">Lab</th>
                        <th class="border p-2">Time-in</th>
                        <th class="border p-2">Time-out</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($records)): ?>
                        <?php foreach ($records as $record): ?>
                            <tr class="bg-white bg-opacity-50">
                                <td class="border p-2"><?php echo htmlspecialchars($record['idno']); ?></td>
                                <td class="border p-2"><?php echo htmlspecialchars($record['student_name']); ?></td>
                                <td class="border p-2"><?php echo htmlspecialchars($record['purpose']); ?></td>
                                <td class="border p-2"><?php echo htmlspecialchars($record['lab']); ?></td>
                                <td class="border p-2"><?php echo htmlspecialchars($record['time_in']); ?></td>
                                <td class="border p-2"><?php echo htmlspecialchars($record['time_out']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center p-2">No records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-4">
                <a href="?page=<?php echo max(1, $current_page - 1); ?>&lab=<?php echo urlencode($filter_lab); ?>&purpose=<?php echo urlencode($filter_purpose); ?>" 
                   class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800 <?php echo $current_page <= 1 ? 'opacity-50 pointer-events-none' : ''; ?>">
                    Previous
                </a>
                <span class="text-green-900">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>
                <a href="?page=<?php echo min($total_pages, $current_page + 1); ?>&lab=<?php echo urlencode($filter_lab); ?>&purpose=<?php echo urlencode($filter_purpose); ?>" 
                   class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800 <?php echo $current_page >= $total_pages ? 'opacity-50 pointer-events-none' : ''; ?>">
                    Next
                </a>
            </div>
        </div>
    </div>
    <?php include 'common-modals.php'; ?>
</body>
</html>
