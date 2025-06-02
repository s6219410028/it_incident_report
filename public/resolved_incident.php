<?php
// resolved_incident.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

include __DIR__ . '/db_config.php';
$db = getDb('itd');

// only these two statuses
$statuses = ['Closed', 'Cancelled'];
$filter = $_GET['status'] ?? 'Closed';
if (!in_array($filter, $statuses, true)) {
    $filter = 'Closed';
}

// fetch incidents with the selected status
$stmt = $db->prepare("
    SELECT 
      i.id,
      i.problem_type,
      i.custom_problem,
      i.severity,
      i.informant_name,
      i.informant_department,
      i.status,
      i.assign_to,
      DATE_FORMAT(i.created_at,    '%Y-%m-%d %H:%i') AS created_at,
      DATE_FORMAT(i.assigned_at,   '%Y-%m-%d %H:%i') AS assigned_at,
      DATE_FORMAT(i.inprogress_at, '%Y-%m-%d %H:%i') AS inprogress_at,
      DATE_FORMAT(i.resolved_at,   '%Y-%m-%d %H:%i') AS resolved_at
    FROM incidents i
    WHERE i.status = ?
    ORDER BY i.id DESC
");
$stmt->execute([$filter]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resolved Incidents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Severity badges */
        .badge-severity {
            display: inline-block;
            padding: .25em .6em;
            font-size: .85em;
            line-height: 1;
            border-radius: 999px;
            color: #fff;
            white-space: nowrap;
        }

        .badge-severity-1 {
            background-color: #007bff;
        }

        .badge-severity-2 {
            background-color: #28a745;
        }

        .badge-severity-3 {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-severity-4 {
            background-color: #fd7e14;
        }

        .badge-severity-5 {
            background-color: #dc3545;
        }

        /* Status badges now black with white text */
        .badge-status {
            padding: .25em .6em;
            font-size: .85em;
            border-radius: 999px;
            color: #fff;
        }

        .badge-closed,
        .badge-cancelled {
            background-color: rgb(31, 33, 34);
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

        .sidebar a,
        .sidebar a i {
            line-height: 18px;
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

        .container-fluid {
            margin-left: 50px;
            padding: 20px;
            transition: margin-left .3s ease;
            width: calc(100% - 50px);

        }

        .container-fluid.expanded {
            margin-left: 250px;
            width: calc(100% - 250px);
        }

        .container-fluid>.card {
            width: 100%;

        }

        .card-body,
        .card-body .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .dataTables_wrapper .dataTables_filter {
            float: right;
        }

        .nowrap th,
        .nowrap td {
            white-space: nowrap;
        }
    </style>
</head>

<body>
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

    <div class="container-fluid" id="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-circle-check me-2"></i>Resolved Incidents</h5>
                <select id="statusFilter" class="form-select w-auto">
                    <?php foreach ($statuses as $s): ?>
                        <option value="<?= $s ?>" <?= $s === $filter ? ' selected' : '' ?>>
                            <?= $s ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="card-body">
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
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['id']) ?></td>
                                <td><?= htmlspecialchars($r['problem_type']) ?></td>
                                <td><?= htmlspecialchars($r['custom_problem']) ?></td>
                                <td>
                                    <span class="badge-severity badge-severity-<?= $r['severity'] ?>">
                                        <?= (int) $r['severity'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($r['informant_name']) ?></td>
                                <td><?= htmlspecialchars($r['informant_department']) ?></td>
                                <td><?= $r['created_at'] ?></td>
                                <td>
                                    <span class="badge-status badge-<?= strtolower($r['status']) ?>">
                                        <?= htmlspecialchars($r['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($r['assign_to'] ?: '-') ?></td>
                                <td><?= $r['assigned_at'] ?: '-' ?></td>
                                <td><?= $r['inprogress_at'] ?: '-' ?></td>
                                <td><?= $r['resolved_at'] ?: '-' ?></td>
                                <td>
                                    <?php if ($r['resolved_at'] && $r['assigned_at']):
                                        $sdt = new DateTime($r['assigned_at']);
                                        $edt = new DateTime($r['resolved_at']);
                                        echo $sdt->diff($edt)->format('%a day(s) %h hour(s) %i minute(s)');
                                    else: ?>
                                        –
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(function () {
            $('#closedTable').DataTable({
                dom:
                    '<"row mb-3"<"col-sm-6"l><"col-sm-6"f>>' +
                    '<"table-responsive"t>' +
                    '<"row mt-3"<"col-sm-5"i><"col-sm-7"p>>',
                scrollX: true,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                pageLength: 10,
                order: [[0, 'desc']]
            });

            $('#statusFilter').on('change', function () {
                window.location = '?status=' + encodeURIComponent(this.value);
            });

            // sidebar toggle
            const sb = document.getElementById('sidebar'),
                ct = document.getElementById('container'),
                exp = localStorage.getItem('sidebarExpanded') === 'true';
            if (exp) { sb.classList.add('expanded'); ct.classList.add('expanded'); }
            sb.querySelector('h2').addEventListener('click', () => {
                const e = sb.classList.toggle('expanded');
                ct.classList.toggle('expanded');
                localStorage.setItem('sidebarExpanded', e);
            });
        });
    </script>
</body>

</html>