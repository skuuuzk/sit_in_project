<?php
require_once('config/db.php'); // Assuming you have a file for database connection

// Ensure user is logged in
if (!isset($_SESSION['idno'])) {
    header("Location: login.php");
    exit();
}

$idno = $_SESSION['idno'];

// Fetch user details
$query = "SELECT firstname, lastname, session AS remaining_sessions, profile_pic, year, course FROM users WHERE idno = '$idno'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$student_name = $user['firstname'] . " " . $user['lastname'];
$remaining_sessions = $user['remaining_sessions'];
$profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'img/default.png';
$course = $user['course'] ?? '';
$year = $user['year'] ?? '';

// Define $full_name and $session_count
$full_name = $student_name;
$session_count = $remaining_sessions;

// Fetch unread notifications count
$query = "SELECT COUNT(*) AS unread_count FROM notifications WHERE idno = '$idno' AND is_read = 0";
$result = mysqli_query($conn, $query);
$notification_data = mysqli_fetch_assoc($result);
$unread_count = $notification_data['unread_count'] ?? 0;

// Handle reservation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purpose'], $_POST['lab'], $_POST['time_in'], $_POST['date'])) {
    $purpose = trim($_POST['purpose']);
    $lab = trim($_POST['lab']);
    $time_in = trim($_POST['time_in']);
    $date = trim($_POST['date']);

    if (!empty($purpose) && !empty($lab) && !empty($time_in) && !empty($date)) {
        $stmt = $conn->prepare("INSERT INTO reservations (idno, purpose, lab, time_in, date, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("issss", $idno, $purpose, $lab, $time_in, $date);
        $stmt->close();

        // Redirect with success message
        header("Location: reservation.php?status=success");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">  
    <style>
        /* Main container styling */
        .main-container {
            display: flex;
            gap: 2rem;
            height: calc(100vh - 6rem); /* Full height minus navbar height */
            padding: 1rem;
        }

        /* Left and right sections */
        .form-section, .pc-section {
            flex: 1;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
        }

        /* Scrollable content inside pc-section */
        .pc-section-content {
            flex: 1;
            overflow-y: auto;
            margin-top: 1rem;
        }

        /* Fixed header inside pc-section */
        .pc-section-header {
            font-weight: bold;
            color: #1a202c;
            text-align: center;
            padding: 0.5rem;
            background-color: rgba(255, 255, 255, 0.3);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        /* PC item styling */
        .pc-item {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 1rem;
            margin: 0.5rem;
            background-color: #edf2f7;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .pc-item:hover {
            background-color: #c6f6d5;
            transform: scale(1.05);
        }

        .pc-item.selected {
            background-color: #38a169;
            color: white;
        }

        .pc-item i {
            font-size: 2rem;
            color: #38a169;
            margin-bottom: 0.5rem;
        }

        .pc-item span {
            font-size: 1rem;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-cover bg-center h-screen flex" style="background-image: url('img/5.jpg');">
    <nav class="w-60 bg-green-700 bg-opacity-60 text-green-900 p-5 rounded-r-2xl shadow-lg fixed top-0 left-0 h-full">
        <div class="logo text-center mb-6">
            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" class="w-24 h-24 object-cover rounded-full border-2 border-green-800 mx-auto">
            <p class="mt-2 text-white font-bold"><?php echo htmlspecialchars($full_name); ?></p>
            <p class="text-sm text-gray-200"><strong>Session:</strong> <?php echo htmlspecialchars($session_count); ?></p>  
        </div>
        <a href="dashboard.php"    class="flex items-center text-white font-medium mb-5 p-3 rounded hover:bg-green-800 <?php echo ($currentPage == 'dashboard.php') ? 'bg-green-800' : ''; ?>">
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

    <div class="main-container mx-auto max-w-7xl">
    <!-- Left Side: Reservation Form -->
        <div class="form-section pr-6">
        <div class="text-left text-2xl font-bold text-green-900 mb-4">Make a Reservation</div>

            <form method="POST" action="reservation.php" class="space-y-3">
                <div class="flex items-center">
                    <i class="fas fa-id-card text-green-900 mr-2"></i>
                    <label for="idno" class="block font-bold text-green-900"></label>
                    <input type="text" id="idno" name="idno" value="<?php echo htmlspecialchars($idno); ?>" class="w-full p-2 border rounded bg-gray-100" readonly>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-user text-green-900 mr-2"></i>
                    <label for="student_name" class="block font-bold text-green-900"></label>
                    <input type="text" id="student_name" name="student_name" value="<?php echo htmlspecialchars($student_name); ?>" class="w-full p-2 border rounded bg-gray-100" readonly>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-book text-green-900 mr-2"></i>
                    <label for="course" class="block font-bold text-green-900"></label>
                    <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($course); ?>" class="w-full p-2 border rounded bg-gray-100" readonly>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-graduation-cap text-green-900 mr-2"></i>
                    <label for="year" class="block font-bold text-green-900"></label>
                    <input type="text" id="year" name="year" value="<?php echo htmlspecialchars($year); ?>" class="w-full p-2 border rounded bg-gray-100" readonly>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-clock text-green-900 mr-2"></i>
                    <label for="remaining_sessions" class="block font-bold text-green-900"></label>
                    <input type="text" id="remaining_sessions" name="remaining_sessions" value="<?php echo htmlspecialchars($remaining_sessions); ?>" class="w-full p-2 border rounded bg-gray-100" readonly>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-bullseye text-green-900 mr-2"></i>
                    <label for="purpose" class="block font-bold text-green-900"></label>
                    <select name="purpose" required class="w-full p-2 border rounded bg-gray-100">
                        <option>C Programming</option>
                        <option>Python</option>
                        <option>ASP .net</option>
                        <option>Java</option>
                    </select>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-flask text-green-900 mr-2"></i>
                    <label for="lab" class="block font-bold text-green-900"></label>
                    <select id="lab" name="lab" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
                        <option value="">Select Laboratory</option>
                        <option value="Lab 1">Lab 524</option>
                        <option value="Lab 2">Lab 526</option>
                        <option value="Lab 3">Lab 530</option>
                        <option value="Lab 4">Lab 542</option>
                        <option value="Lab 5">Lab 544</option>
                        <option value="Lab 6">Lab 517</option>
                    </select>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-calendar-check text-green-900 mr-2"></i>
                    <label for="time_in" class="block font-bold text-green-900"></label>
                    <input type="time" id="time_in" name="time_in" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-calendar-alt text-green-900 mr-2"></i>
                    <label for="date" class="block font-bold text-green-900"></label>
                    <input type="date" id="date" name="date" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
                <!-- Confirm Reservation Button -->
                <button onclick="showToast()" type="submit" class="w-full bg-green-700 text-white p-2 rounded hover:bg-green-800">
                    Confirm Reservation
                </button>
                <!-- Toast Notification -->
                <div id="toast" class="fixed top-10 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-6 py-3 rounded shadow-lg hidden">
                    Reservation Submitted Successfully!
                </div>
            </form>
        </div>

        <!-- Right Side: Select a PC -->
        <div class="pc-section">
            <div class="pc-section-header">
                Please select a laboratory from the reservation form to view available PCs.
            </div>
            <div class="pc-section-content" id="pc-section-content">
                <!-- PCs will be dynamically loaded here -->
            </div>
        </div>
    </div>
    <script>
    document.getElementById('lab').addEventListener('change', function () {
        const lab = this.value;
        const pcSectionHeader = document.querySelector('.pc-section-header');
        const pcSectionContent = document.getElementById('pc-section-content');

        if (lab) {
            // Update the header text
            pcSectionHeader.textContent = `Available PCs in ${lab}:`;

            // Make an AJAX request to fetch available PCs
            fetch(`fetch_pcs.php?lab=${encodeURIComponent(lab)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to fetch available PCs');
                    }
                    return response.json();
                })
                .then(data => {
                    pcSectionContent.innerHTML = ''; // Clear previous content
                    if (data && data.length > 0) {
                        const pcContainer = document.createElement('div');
                        pcContainer.classList.add('flex', 'flex-wrap', 'gap-4');

                        data.forEach(pc => {
                            const pcItem = document.createElement('div');
                            pcItem.classList.add('pc-item');
                            pcItem.dataset.pc = pc;

                            const icon = document.createElement('i');
                            icon.classList.add('fa', 'fa-desktop');

                            const label = document.createElement('span');
                            label.textContent = pc;

                            pcItem.addEventListener('click', function () {
                                this.classList.toggle('selected');
                                const allPCs = pcContainer.querySelectorAll('.pc-item');
                                allPCs.forEach(item => {
                                    if (item !== this) {
                                        item.classList.remove('selected');
                                    }
                                });
                            });

                            pcItem.appendChild(icon);
                            pcItem.appendChild(label);
                            pcContainer.appendChild(pcItem);
                        });

                        pcSectionContent.appendChild(pcContainer);
                    } else {
                        pcSectionContent.innerHTML = `<p class="text-center text-gray-500">No available PCs in this laboratory at the moment.</p>`;
                    }
                })
                .catch(error => {
                    console.error('Error fetching PCs:', error);
                    pcSectionContent.innerHTML = `<p class="text-center text-red-500">Failed to fetch available PCs. Please try again later.</p>`;
                });
        } else {
            pcSectionHeader.textContent = 'Please select a laboratory from the reservation form to view available PCs.';
            pcSectionContent.innerHTML = '';
        }
    });

    function showToast() {
        var toast = document.getElementById('toast');
        toast.classList.remove('hidden'); // Show toast

        setTimeout(function () {
            toast.classList.add('hidden'); // Hide toast after 3 seconds
        }, 3000); // 3000 milliseconds = 3 seconds
    }
</script>

</body>
</html>
