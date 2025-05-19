<?php
// closed_incidents.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

include __DIR__ . '/db_config.php';
$db = getDb('itd');

// Fetch only Closed incidents:
$closed = $db->query("
  SELECT 
    id,
    problem_type,
    custom_problem,
    severity,
    informant_name,
    informant_department,
    status,
    assign_to,
    DATE_FORMAT(created_at,'%Y-%m-%d %H:%i')    AS created_at,
    DATE_FORMAT(assigned_at,'%Y-%m-%d %H:%i')   AS assigned_at,
    DATE_FORMAT(inprogress_at,'%Y-%m-%d %H:%i') AS inprogress_at,
    DATE_FORMAT(resolved_at,'%Y-%m-%d %H:%i')   AS resolved_at
  FROM incidents
  WHERE status = 'Closed'
  ORDER BY resolved_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Resolved Incidents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Global resets */
        * {
            box-sizing: border-box;
        }

        .sidebar a,
        .sidebar a i {
            line-height: 18px;
        }


        body {
            margin: 0;
            font-family: Arial, sans-serif;
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

        /* Form Styles */
        form.hardware-form {
            margin-bottom: 20px;
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        form.hardware-form h3 {
            margin-top: 0;
        }

        form.hardware-form input,
        form.hardware-form textarea {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
        }

        form.hardware-form button {
            padding: 10px 20px;
            background-color: #333;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 4px;
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

        .table-wrapper {
            overflow-x: auto;
        }

        /* prevent wrapping */
        #hardwareTable th,
        #hardwareTable td {
            white-space: nowrap;
        }

        /* status badge overrides if needed */
        .badge-status {
            padding: .25em .6em;
            font-size: .85em;
            border-radius: 999px;
            color: #fff;
        }

        .badge-open {
            background-color: #28a745;
        }

        .badge-inprogress {
            background-color: #007bff;
        }

        .badge-closed {
            background-color: #6c757d;
        }
    </style>
</head>

<body>
    <!-- sidebar -->
    <div class="sidebar" id="sidebar">
        <h2><i class="fas fa-bars"></i></h2>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span class="link-text">Dashboard</span></a>
        <a href="employee_list.php"><i class="fas fa-users"></i><span class="link-text">Employee List</span></a>
        <a href="hardware_list.php"><i class="fas fa-desktop"></i><span class="link-text">Hardware List</span></a>
        <a href="report_incident.php"><i class="fas fa-file"></i><span class="link-text">Report Incident</span></a>
        <a href="resolved_incident.php"><i class="fas fa-circle-xmark"></i><span class="link-text">Resolved
                Incidents</span></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span class="link-text">Logout</span></a>
    </div>


    <div class="container" id="container">
        <h2>Closed Incidents</h2>
        <div class="table-responsive">
            <table id="closedTable" class="table table-striped table-bordered nowrap" style="width:100%">
                <thead class="nowrap">
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Custom</th>
                        <th>Sev.</th>
                        <th>Informant</th>
                        <th>Dept.</th>
                        <th>Created</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Assigned At</th>
                        <th>In Prog.</th>
                        <th>Resolved</th>
                        <th>Resolution Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($closed as $r): ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td><?= htmlspecialchars($r['problem_type']) ?></td>
                            <td><?= htmlspecialchars($r['custom_problem']) ?></td>
                            <td><?= htmlspecialchars($r['severity']) ?></td>
                            <td><?= htmlspecialchars($r['informant_name']) ?></td>
                            <td><?= htmlspecialchars($r['informant_department']) ?></td>
                            <td><?= $r['created_at'] ?></td>
                            <td><?= htmlspecialchars($r['assign_to'] ?: '-') ?></td>
                            <td><?= $r['assigned_at'] ?: '-' ?></td>
                            <td><?= $r['inprogress_at'] ?: '-' ?></td>
                            <td><?= $r['resolved_at'] ?: '-' ?></td>
                            <td>
                                <?php
                                if ($r['resolved_at']) {
                                    $s = new DateTime($r['assigned_at']);
                                    $e = new DateTime($r['resolved_at']);
                                    $diff = $s->diff($e);
                                    echo $diff->format('%a day(s) %h hour(s) %i minute(s)');
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(function () {
            $('#closedTable').DataTable({
                scrollX: true,
                pageLength: 10,
                order: [[3, 'desc']] // sort by severity or change index as needed
            });
        });
        <script>
            document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const container = document.getElementById('container');
            const wasExpanded = localStorage.getItem('sidebarExpanded') === 'true';
            if (wasExpanded) {
                sidebar.classList.add('expanded');
            container.classList.add('expanded');
            }
            sidebar.querySelector('h2').addEventListener('click', function () {
                const expanded = sidebar.classList.toggle('expanded');
            container.classList.toggle('expanded');
            localStorage.setItem('sidebarExpanded', expanded);
            });
        });
    </script>
</body>

</html>