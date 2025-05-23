<?php
// add_incident.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include __DIR__ . '/db_config.php';
$db = getDb('itd');

// ─── pull HRM users for staffcode lookup ─────────────────────────────
$hrm = getDb('hrm');
$hrmUsers = $hrm->query("
    SELECT 
      u.staffcode,
      CONCAT(u.firstname,' ',u.lastname) AS name,
      CASE
        WHEN u.branch_id = 1 THEN 'Q.A'
        WHEN u.branch_id = 2 THEN 'BOX 1'
        WHEN u.branch_id = 3 THEN 'BOX 2'

        WHEN u.branch_id = 93 THEN 'ผู้จัดการควบคุมคุณภาพ'
        ELSE 'ไม่ระบุ'
      END AS branch_name
    FROM users u
    ORDER BY u.firstname, u.lastname
")->fetchAll(PDO::FETCH_ASSOC);

// ─── handle new‐incident POST ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $now = date('Y-m-d H:i:s');
    $stmt = $db->prepare("
        INSERT INTO incidents
          (problem_type, custom_problem, severity,
           description, informant_name, informant_department,
           status, assigned_at, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'Open', ?, ?)
    ");
    $stmt->execute([
        $_POST['problem_type'] ?? '',
        $_POST['custom_problem'] ?? '',
        (int) ($_POST['severity'] ?? 0),
        $_POST['description'] ?? '',
        $_POST['informant_name'] ?? '',
        $_POST['informant_department'] ?? '',
        $now,  // assigned_at
        $now   // created_at
    ]);
    header('Location: add_incident.php');
    exit;
}

// ─── fetch all incidents (read-only) ────────────────────────────────
$incidents = $db->query("
    SELECT 
      id,
      problem_type,
      custom_problem,
      severity,
      informant_name,
      informant_department,
      status,
      DATE_FORMAT(created_at,'%Y-%m-%d %H:%i') AS created_at
    FROM incidents
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submit New Incident</title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
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
            background: #007bff;
        }

        .badge-severity-2 {
            background: #28a745;
        }

        .badge-severity-3 {
            background: #ffc107;
            color: #212529;
        }

        .badge-severity-4 {
            background: #fd7e14;
        }

        .badge-severity-5 {
            background: #dc3545;
        }

        .badge-status {
            padding: .25em .6em;
            font-size: .85em;
            border-radius: 999px;
            color: #fff;
        }

        .badge-open {
            background: #28a745;
        }

        .badge-inprogress {
            background: #007bff;
        }

        .badge-closed {
            background: #6c757d;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .container {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- Submit New Incident Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Submit New Incident</h5>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Problem Type</label>
                            <select name="problem_type" class="form-select">
                                <option>Hardware</option>
                                <option>Software</option>
                                <option>IT Support</option>
                                <option>ERP</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>If Other, specify</label>
                            <input type="text" name="custom_problem" class="form-control"
                                placeholder="Custom Problem Type">
                        </div>
                        <div class="col-md-4">
                            <label>Severity</label>
                            <select name="severity" class="form-select">
                                <option value="1">1 – Lowest</option>
                                <option value="2">2 – Low</option>
                                <option value="3">3 – Normal</option>
                                <option value="4">4 – High</option>
                                <option value="5">5 – Highest</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label>Staff Code</label>
                            <input type="text" id="staffcode" class="form-control" placeholder="Type your staff code"
                                list="staffcodeList" required>
                            <datalist id="staffcodeList">
                                <?php foreach ($hrmUsers as $u): ?>
                                    <option value="<?= htmlspecialchars($u['staffcode']) ?>">
                                    <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label>Informant Name</label>
                            <input type="text" id="informant_name" name="informant_name" class="form-control" readonly
                                required>
                        </div>
                        <div class="col-md-4">
                            <label>Informant Dept.</label>
                            <input type="text" id="informant_department" name="informant_department"
                                class="form-control" readonly required>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary">Submit Incident</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Read-Only Incidents Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list-ul me-2"></i>All Incidents (Read-Only)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="incidentTable" class="table table-striped table-bordered nowrap" style="width:100%">
                        <thead class="nowrap">
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Custom</th>
                                <th>Sev.</th>
                                <th>Informant</th>
                                <th>Dept.</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($incidents as $i): ?>
                                <tr>
                                    <td><?= htmlspecialchars($i['id']) ?></td>
                                    <td><?= htmlspecialchars($i['problem_type']) ?></td>
                                    <td><?= htmlspecialchars($i['custom_problem']) ?></td>
                                    <td>
                                        <span class="badge-severity badge-severity-<?= (int) $i['severity'] ?>">
                                            <?= (int) $i['severity'] ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($i['informant_name']) ?></td>
                                    <td><?= htmlspecialchars($i['informant_department']) ?></td>
                                    <td>
                                        <?php
                                        switch ($i['status']) {
                                            case 'Open':
                                                $cls = 'badge-open';
                                                break;
                                            case 'In Progress':
                                                $cls = 'badge-inprogress';
                                                break;
                                            case 'Closed':
                                                $cls = 'badge-closed';
                                                break;
                                            default:
                                                $cls = 'badge-secondary';
                                                break;
                                        }
                                        ?>
                                        <span class="badge-status <?= $cls ?>">
                                            <?= htmlspecialchars($i['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($i['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        const hrmData = <?= json_encode($hrmUsers, JSON_HEX_TAG) ?>;
        $('#staffcode').on('input change', function () {
            const u = hrmData.find(x => x.staffcode === this.value);
            $('#informant_name').val(u?.name || '');
            $('#informant_department').val(u?.branch_name || '');
        });

        $(function () {
            $('#incidentTable').DataTable({
                dom:
                    '<"row mb-3"<"col-sm-6"l><"col-sm-6"f>>' +
                    '<"table-responsive"t>' +
                    '<"row mt-3"<"col-sm-5"i><"col-sm-7"p>>',
                scrollX: true,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                pageLength: 10,
                order: [[0, 'desc']]
            });
        });
    </script>
</body>

</html>