<?php
session_start();
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.html");
//     exit;
// }
include(__DIR__ . '/db_config.php');

// Retrieve all incidents (optionally, exclude closed incidents by adding: WHERE status != 'Close')
$stmt = $pdo->query("SELECT * FROM incidents WHERE status != 'Close' ORDER BY created_at DESC");

$incidents = $stmt->fetchAll();

// Retrieve IT staff from local users table for the dropdown
$staffStmt = $pdo->query("SELECT id, user_name FROM users ORDER BY user_name ASC");
$staffList = $staffStmt->fetchAll(PDO::FETCH_ASSOC);

// New Query: Group incidents by assigned_staff (if NULL or empty, show as 'Unassigned')
$stmtAssigned = $pdo->query("SELECT IF(assigned_staff IS NULL OR assigned_staff = '', 'Unassigned', assigned_staff) AS staff, COUNT(*) AS count FROM incidents GROUP BY staff");
$assignedData = $stmtAssigned->fetchAll(PDO::FETCH_ASSOC);
$staffLabels = [];
$staffCounts = [];
foreach ($assignedData as $row) {
    $staffLabels[] = $row['staff'];
    $staffCounts[] = (int) $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sortable & Searchable Incident List</title>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            white-space: nowrap;
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

        /* Main container styles */
        .container {
            margin-left: 50px;
            width: calc(100% - 50px);
            transition: margin-left 0.3s ease, width 0.3s ease;
            padding: 20px;
        }

        .container.expanded {
            margin-left: 250px;
            width: calc(100% - 250px);
        }

        .main-content {
            width: 90%;
            margin: 0 auto;
        }

        /* Table Styles */
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Style for dropdowns in table cells */
        select {
            width: 100%;
            padding: 4px;
        }

        /* Update button below table */
        .update-btn-container {
            text-align: center;
            margin-top: 20px;
        }

        /* Pie chart container for assigned staff distribution */
        .chart-container {
            width: 40%;
            margin: 40px auto 0;
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
            <h1>Incident List</h1>
            <!-- Wrap the entire table in one form for bulk update -->
            <form action="update_incident.php" method="post" id="bulkUpdateForm">
                <table id="incidentTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Problem Type</th>
                            <th>Severity</th>
                            <th>Assigned Staff</th>
                            <th>Description</th>
                            <th>Employee Name</th>
                            <th>Employee Dept</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($incidents as $incident): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($incident['id']); ?>
                                    <input type="hidden" name="incident_ids[]"
                                        value="<?php echo htmlspecialchars($incident['id']); ?>">
                                </td>
                                <td><?php echo htmlspecialchars($incident['problem_type']); ?></td>
                                <td><?php echo htmlspecialchars($incident['severity']); ?></td>
                                <td>
                                    <select name="assigned_staff[<?php echo $incident['id']; ?>]">
                                        <option value="">-- Select Staff --</option>
                                        <?php foreach ($staffList as $staff): ?>
                                            <option value="<?php echo htmlspecialchars($staff['user_name']); ?>" <?php if ($incident['assigned_staff'] == $staff['user_name'])
                                                   echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($staff['user_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><?php echo htmlspecialchars($incident['description']); ?></td>
                                <td><?php echo htmlspecialchars($incident['employee_name']); ?></td>
                                <td><?php echo htmlspecialchars($incident['employee_department']); ?></td>
                                <td>
                                    <select name="status[<?php echo $incident['id']; ?>]">
                                        <option value="Open" <?php if ($incident['status'] == 'Open')
                                            echo 'selected'; ?>>Open
                                        </option>
                                        <option value="Assign" <?php if ($incident['status'] == 'Assign')
                                            echo 'selected'; ?>>
                                            Assign</option>
                                        <option value="Close" <?php if ($incident['status'] == 'Close')
                                            echo 'selected'; ?>>
                                            Close</option>
                                    </select>
                                </td>
                                <td><?php echo htmlspecialchars($incident['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- Bulk Update Button -->
                <div class="update-btn-container">
                    <button type="submit">Update All Changes</button>
                </div>
            </form>

            <!-- Pie Chart: Distribution of Incidents by Assigned Staff -->
            <div class="chart-container">
                <canvas id="assignedPieChart"></canvas>
            </div>
        </div>
    </div>

    <!-- jQuery and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#incidentTable').DataTable({
                dom: '<"dt-header"f>rt<"dt-footer"ip>'
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

        // Create Assigned Staff Pie Chart
        const assignedPieCtx = document.getElementById('assignedPieChart').getContext('2d');
        const assignedPieChart = new Chart(assignedPieCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($staffLabels); ?>,
                datasets: [{
                    label: 'Incidents by Assigned Staff',
                    data: <?php echo json_encode($staffCounts); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(201, 203, 207, 0.7)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(201, 203, 207, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: { display: true, text: 'Incidents by Assigned Staff' }
                }
            }
        });
    </script>
</body>

</html>