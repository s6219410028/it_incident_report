<?php
session_start();
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.html");
//     exit;
// }
include(__DIR__ . '/db_config.php');

// === Retrieve IT Staff from Local Database ===
// Adjust this query to match your local IT staff table structure.
$stmtLocal = $pdo->query("SELECT staffcode, user_name, firstname, lastname, email, position FROM users ORDER BY staffcode ASC");
$itStaff = $stmtLocal->fetchAll();

// === Retrieve Employees from Remote Database ===
// Configure remote connection parameters
$remoteHost = '4637.vm.gocloud.cloud';  // remote host address
$remoteDb = 'admin_hrm';               // remote database name
$remoteUser = 'remote_user';           // remote username (replace with actual)
$remotePass = 'remote_password';       // remote password (replace with actual)
$remoteCharset = 'utf8mb4';

$remoteDsn = "mysql:host=$remoteHost;dbname=$remoteDb;charset=$remoteCharset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
try {
    $remotePdo = new PDO($remoteDsn, $remoteUser, $remotePass, $options);
} catch (PDOException $e) {
    die("Remote DB connection failed: " . $e->getMessage());
}

$stmtRemote = $remotePdo->query("SELECT staffcode, username, firstname, lastname, email, position FROM users ORDER BY staffcode ASC");
$employees = $stmtRemote->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Select IT Staff & Employees</title>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Global resets */
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            overflow-x: hidden;
        }

        /* Sidebar styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 50px;
            /* Collapsed width */
            background-color: #333;
            color: #fff;
            overflow: hidden;
            transition: width 0.3s ease;
            z-index: 1000;
        }

        .sidebar.expanded {
            width: 250px;
        }

        .sidebar h2 {
            margin: 0;
            padding: 10px;
            text-align: center;
            font-size: 18px;
            cursor: pointer;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: #fff;
            text-decoration: none;
            padding: 10px;
            margin: 5px;
            background: #444;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .sidebar a:hover {
            background: #555;
        }

        .sidebar a i {
            min-width: 30px;
            text-align: center;
            font-size: 18px;
        }

        .sidebar a .link-text {
            margin-left: 10px;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .sidebar.expanded a .link-text {
            opacity: 1;
        }

        /* Container styles */
        .container {
            margin-left: 50px;
            transition: margin-left 0.3s ease, width 0.3s ease;
            padding: 20px;
        }

        .container.expanded {
            margin-left: 250px;
        }

        .main-content {
            width: 90%;
            margin: 0 auto;
        }

        /* Table styling */
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 8px;
            border: 1px solid #ccc;
        }

        /* Integrated search row styling */
        thead tr.search-row th {
            padding: 5px;
            background: #f2f2f2;
            border-bottom: 1px solid #ccc;
        }

        thead tr.search-row th input {
            width: 100%;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        /* Section headers */
        h2.section-title {
            margin-top: 40px;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <h2><i class="fas fa-bars"></i></h2>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span class="link-text">Dashboard</span></a>
        <a href="incident_list.php"><i class="fas fa-exclamation-triangle"></i><span class="link-text">Incident
                List</span></a>
        <a href="employee_list.php"><i class="fas fa-users"></i><span class="link-text">Employee List</span></a>
        <a href="hardware_list.php"><i class="fas fa-desktop"></i><span class="link-text">Hardware List</span></a>
        <a href="report_incident.php"><i class="fas fa-file"></i><span class="link-text">Report Incident</span></a>
        <a href="closed_incidents.php"><i class="fas fa-circle-xmark"></i><span class="link-text">Closed
                Incidents</span></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span class="link-text">Logout</span></a>
    </div>
    <!-- Main container -->
    <div class="container" id="container">
        <div class="main-content">
            <h1>Select IT Staff & Employees</h1>
            <!-- IT Staff Table (Local) -->
            <h2 class="section-title">IT Staff (Local)</h2>
            <table id="itStaffTable">
                <thead>
                    <tr class="search-row">
                        <th colspan="6">
                            <input type="text" id="itStaffSearch" placeholder="Search IT staff...">
                        </th>
                    </tr>
                    <tr>
                        <th>Staff Code</th>
                        <th>Username</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Position</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itStaff as $staff): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($staff['staffcode']); ?></td>
                            <td><?php echo htmlspecialchars($staff['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($staff['firstname']); ?></td>
                            <td><?php echo htmlspecialchars($staff['lastname']); ?></td>
                            <td><?php echo htmlspecialchars($staff['email']); ?></td>
                            <td><?php echo htmlspecialchars($staff['position']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Employee Table (Remote) -->
            <h2 class="section-title">Employees (Remote)</h2>
            <table id="employeeTable">
                <thead>
                    <tr class="search-row">
                        <th colspan="6">
                            <input type="text" id="employeeSearch" placeholder="Search employees...">
                        </th>
                    </tr>
                    <tr>
                        <th>Staff Code</th>
                        <th>Username</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Position</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($emp['staffcode']); ?></td>
                            <td><?php echo htmlspecialchars($emp['username']); ?></td>
                            <td><?php echo htmlspecialchars($emp['firstname']); ?></td>
                            <td><?php echo htmlspecialchars($emp['lastname']); ?></td>
                            <td><?php echo htmlspecialchars($emp['email']); ?></td>
                            <td><?php echo htmlspecialchars($emp['position']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Include jQuery and DataTables JS libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize DataTables for IT Staff table
            var itTable = $('#itStaffTable').DataTable({
                "order": [[0, "asc"]],
                "pageLength": 10,
                "bFilter": false
            });
            $('#itStaffSearch').on('keyup', function () {
                itTable.search(this.value).draw();
            });

            // Initialize DataTables for Employee table
            var empTable = $('#employeeTable').DataTable({
                "order": [[0, "asc"]],
                "pageLength": 10,
                "bFilter": false
            });
            $('#employeeSearch').on('keyup', function () {
                empTable.search(this.value).draw();
            });
        });

        // Sidebar toggle with persistent state
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const container = document.getElementById('container');
            const storedState = localStorage.getItem('sidebarExpanded') === 'true';
            if (storedState) {
                sidebar.classList.add('expanded');
                container.classList.add('expanded');
            }
            sidebar.querySelector('h2').addEventListener('click', function () {
                sidebar.classList.toggle('expanded');
                container.classList.toggle('expanded');
                localStorage.setItem('sidebarExpanded', sidebar.classList.contains('expanded'));
            });
        });
    </script>
</body>

</html>