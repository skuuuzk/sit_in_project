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
$filter_date = $_GET['date'] ?? '';
// Fetch admin username
$admin_id = $_SESSION['admin_id'];
$query = "SELECT username FROM admins WHERE id = ?";
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

$username = $admin['username'] ?? 'Admin';

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
if (!empty($filter_date)) {
    $conditions[] = "DATE(r.time_in) = '" . mysqli_real_escape_string($conn, $filter_date) . "'";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="path/to/your/script.js" defer></script>
    <link rel="stylesheet" href="style.css"> 
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
       <!-- Dropdown CSS -->
       <style>
        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #f9f9f9;
            min-width: 200px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            z-index: 10;
        }

        .dropdown-content li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .dropdown-content li a {
            text-decoration: none;
            color: black;
        }

        .dropdown-content li a:hover {
            background-color: #ddd;
        }

        .dropdown-content.show {
            display: block;
        }
    </style>
    <script>
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const isVisible = dropdown.classList.contains('show');
            closeAllDropdowns(); // Close other dropdowns
            if (!isVisible) {
                dropdown.classList.add('show');
            }
        }

        function closeAllDropdowns() {
            const dropdowns = document.querySelectorAll('.dropdown-content');
            dropdowns.forEach(dropdown => dropdown.classList.remove('show'));
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.relative')) {
                closeAllDropdowns();
            }
        });
    </script>
</head>
<body class="bg-cover bg-center h-screen flex" style="background-image: url('img/5.jpg');">
<nav class="w-60 bg-green-700 bg-opacity-60 text-green-900 p-5 rounded-r-2xl shadow-lg fixed top-0 left-0 h-full">
        <div class="logo text-center mb-6">
            <img src="img/ccs.png" alt="Logo" class="w-20 h-20 object-cover rounded-full border-2 border-green-800 mx-auto">
            <p class="mt-2 text-white font-bold"><?php echo htmlspecialchars($username); ?></p>
        </div>
        <a href="a-dashboard.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700 active:bg-green-300">
            <i class="fas fa-user mr-3"></i> Home
        </a>
        <a href="#" onclick="openModal('searchModal')" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-search mr-3"></i> Search
        </a>
        <a href="a-students.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-users mr-3"></i> Students
        </a>

        <!-- Dropdown for View (clickable) -->
        <div class="relative">
                <a href="#" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700" onclick="toggleDropdown('viewDropdown'); return false;">
                    <i class="fas fa-eye mr-3"></i> View <i class="fas fa-caret-down ml-2"></i>
                </a>
                <ul id="viewDropdown" class="dropdown-content bg-green-200 text-green-900 w-full p-2 rounded-lg shadow-md">
                    <li><a href="a-currents.php" class="block p-3">Current Sit-in</a></li>
                    <li><a href="a-vrecords.php" class="block p-3">Visit Records</a></li>
                    <li><a href="a-feedback.php" class="block p-3">Feedback</a></li>
                    <li><a href="a-daily-analytics.php" class="block p-3">Daily Analytics</a></li>
                </ul>
            </div>

            <!-- Dropdown for Lab (clickable) -->
            <div class="relative">
                <a href="#" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700" onclick="toggleDropdown('labDropdown'); return false;">
                    <i class="fas fa-laptop mr-3"></i> Lab <i class="fas fa-caret-down ml-2"></i>
                </a>
                <ul id="labDropdown" class="dropdown-content bg-green-200 text-green-900 w-full p-2 rounded-lg shadow-md">
                    <li><a href="a-computer-control.php" class="block p-3">Computer Control</a></li>
                    <li><a href="a-leaderboard.php" class="block p-3">Leaderboard</a></li>
                    <li><a href="a-resources.php" class="block p-3">Resources</a></li>
                </ul>
            </div>
                
        <a href="a-reports.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-chart-line mr-3"></i> Reports
        </a>
        <a href="a-logout.php" class="flex items-center text-green-900 font-medium mb-5 p-3 rounded hover:bg-green-200 hover:text-green-700">
            <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
    </nav>

    <div class="flex-1 p-6 ml-60 space-y-6">
        <div class="text-center text-2xl font-bold text-green-900">Generate Reports</div>
        <div class="bg-white bg-opacity-20 p-6 rounded-xl shadow-lg">
            <!-- Filter Section -->
            <form method="get" class="mb-6 grid grid-cols-4 gap-4">
                <div>
                    <label for="date" class="block text-green-900 mb-2">Select Date:</label>
                    <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($filter_date); ?>" class="p-2 rounded border border-green-900 w-full">
                </div>
                <div>
                    <label for="lab" class="block text-green-900 mb-2">Select Laboratory:</label>
                    <select name="lab" id="lab" class="p-2 rounded border border-green-900 w-full">
                        <option value="">All</option>
                        <option value="Lab 530" <?php if ($filter_lab == 'Lab 530') echo 'selected'; ?>>Lab 530</option>
                        <option value="Lab 524" <?php if ($filter_lab == 'Lab 524') echo 'selected'; ?>>Lab 524</option>
                        <option value="Lab 526" <?php if ($filter_lab == 'Lab 526') echo 'selected'; ?>>Lab 526</option>
                        <option value="Lab 542" <?php if ($filter_lab == 'Lab 542') echo 'selected'; ?>>Lab 542</option>
                        <option value="Lab 540" <?php if ($filter_lab == 'Lab 540') echo 'selected'; ?>>Lab 540</option>
                    </select>
                </div>
                <div>
                    <label for="purpose" class="block text-green-900 mb-2">Select Purpose:</label>
                    <select name="purpose" id="purpose" class="p-2 rounded border border-green-900 w-full">
                        <option value="">All</option>
                        <option value="Python" <?php if ($filter_purpose == 'Python') echo 'selected'; ?>>Python</option>
                        <option value="Java" <?php if ($filter_purpose == 'Java') echo 'selected'; ?>>Java</option>
                        <option value="ASP .net" <?php if ($filter_purpose == 'ASP .net') echo 'selected'; ?>>ASP .net</option>
                        <option value="C Programming" <?php if ($filter_purpose == 'C Programming') echo 'selected'; ?>>C Programming</option>
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800">Search</button>
                    <a href="a-reports.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Reset</a>
                </div>
            </form>

            <!-- Export Buttons -->
            <form method="post" class="mb-6 flex space-x-4">
                <button type="submit" name="export_csv" class="bg-blue-500 text-white p-2 rounded flex items-center">
                    <i class="fas fa-file-csv mr-2"></i> Export CSV
                </button>
                <button type="submit" name="export_excel" class="bg-green-700 text-white p-2 rounded flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Export Excel
                </button>
                <button type="submit" name="export_pdf" class="bg-red-700 text-white p-2 rounded flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Export PDF
                </button>
                <button type="button" onclick="printTable()" class="bg-yellow-500 text-white p-2 rounded flex items-center">
                    <i class="fas fa-print mr-2"></i> Print Report
                </button>
            </form>

            <!-- Table -->
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-green-700 text-white">
                        <th class="border p-2">ID Number</th>
                        <th class="border p-2">Name</th>
                        <th class="border p-2">Purpose</th>
                        <th class="border p-2">Laboratory</th>
                        <th class="border p-2">Time In</th>
                        <th class="border p-2">Time Out</th>
                        <th class="border p-2">Date</th>
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
                                <td class="border p-2"><?php echo htmlspecialchars(date('d/m/Y', strtotime($record['time_in']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center p-2">No records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-4">
                <a href="?page=<?php echo max(1, $current_page - 1); ?>&lab=<?php echo urlencode($filter_lab); ?>&purpose=<?php echo urlencode($filter_purpose); ?>&date=<?php echo urlencode($filter_date); ?>" 
                   class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800 <?php echo $current_page <= 1 ? 'opacity-50 pointer-events-none' : ''; ?>">
                    Previous
                </a>
                <span class="text-green-900">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>
                <a href="?page=<?php echo min($total_pages, $current_page + 1); ?>&lab=<?php echo urlencode($filter_lab); ?>&purpose=<?php echo urlencode($filter_purpose); ?>&date=<?php echo urlencode($filter_date); ?>" 
                   class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800 <?php echo $current_page >= $total_pages ? 'opacity-50 pointer-events-none' : ''; ?>">
                    Next
                </a>
            </div>
        </div>
    </div>
    <?php include 'common-modals.php'; ?>
</body>
</html>
