<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include __DIR__ . '/db_config.php';
$db = getDb('itd');


$hrm = getDb('hrm');
$hrmUsers = $hrm->query("
    SELECT 
      u.staffcode,
      CONCAT(u.firstname,' ',u.lastname) AS name,
      CASE
        WHEN u.branch_id = 1 THEN 'Q.A'
        WHEN u.branch_id = 2 THEN 'BOX 1'
        WHEN u.branch_id = 3 THEN 'BOX 2'
        WHEN u.branch_id = 4 THEN 'BOX 3'
        WHEN u.branch_id = 5 THEN 'BOX 4'
        WHEN u.branch_id = 6 THEN 'BOX กะ'
        WHEN u.branch_id = 7 THEN 'Collector ติดตามเก็บเงิน'
        WHEN u.branch_id = 8 THEN 'CRM'
        WHEN u.branch_id = 9 THEN 'DBU'
        WHEN u.branch_id = 10 THEN 'DETAIL ต่างจังหวัด(Sale โรงพยาบาล)'
        /* …etc… */
        WHEN u.branch_id = 93 THEN 'ผู้จัดการควบคุมคุณภาพ'
        ELSE 'ไม่ระบุ'
      END AS branch_name
    FROM users u
    ORDER BY u.firstname, u.lastname
")->fetchAll(PDO::FETCH_ASSOC);


$assignees = $db
    ->query("SELECT DISTINCT assign_to
              FROM incidents
             WHERE assign_to <> ''
             ORDER BY assign_to")
    ->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    error_log("===== \$_POST:\n" . print_r($_POST, true));
    error_log("===== \$_FILES:\n" . print_r($_FILES, true));

    if (!empty($_POST['delete_id'])) {
        $db->prepare("DELETE FROM incidents WHERE id = ?")
            ->execute([(int) $_POST['delete_id']]);
        header('Location: report_incident.php');
        exit;
    }

    if (!empty($_POST['update_id'])) {
        $now = date('Y-m-d H:i:s');
        $status = $_POST['status'];
        $assign = trim($_POST['assign_to']);

        $sql = "UPDATE incidents
                     SET status     = ?,
                         assign_to  = ?,
                         updated_at = NOW()";
        $params = [$status, $assign];

        if ($status === 'In Progress') {
            $sql .= ", inprogress_at = ?";
            $params[] = $now;
        } elseif ($status === 'Closed') {
            $sql .= ", resolved_at = ?";
            $params[] = $now;
        }

        $sql .= " WHERE id = ?";
        $params[] = (int) $_POST['update_id'];

        $db->prepare($sql)->execute($params);
        header('Location: report_incident.php');
        exit;
    }

    $now = date('Y-m-d H:i:s');

    $stmt = $db->prepare("
        INSERT INTO incidents
          (problem_type, custom_problem, severity,
           description, informant_name, informant_department,
           status, assigned_at, created_at)
        VALUES (?,?,?,?,?,?,'Open',?,NOW())
    ");
    $stmt->execute([
        $_POST['problem_type'] ?? '',
        $_POST['custom_problem'] ?? '',
        (int) ($_POST['severity'] ?? 0),
        $_POST['description'] ?? '',
        $_POST['informant_name'] ?? '',
        $_POST['informant_department'] ?? '',
        $now
    ]);

    $incidentId = $db->lastInsertId();
    error_log("→ Inserted incident #{$incidentId}");

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    if (!is_writable($uploadDir)) {
        error_log("⚠️ UPLOAD DIR NOT WRITABLE: {$uploadDir}");
        die("Upload folder not writable. Check server permissions.");
    }

    if (!empty($_FILES['photos']['tmp_name']) && is_array($_FILES['photos']['tmp_name'])) {
        foreach ($_FILES['photos']['error'] as $i => $err) {
            error_log("Photo #{$i} upload error code: {$err}");
            if ($err !== UPLOAD_ERR_OK)
                continue;

            $tmp = $_FILES['photos']['tmp_name'][$i];
            $orig = basename($_FILES['photos']['name'][$i]);
            $ext = pathinfo($orig, PATHINFO_EXTENSION);
            $new = uniqid("inc{$incidentId}_") . ".$ext";
            $dest = $uploadDir . $new;

            if (move_uploaded_file($tmp, $dest)) {
                error_log("✓ Moved $orig → uploads/$new");
                // insert into incident_photos
                $db->prepare("
                  INSERT INTO incident_photos (incident_id, file_path)
                  VALUES (?,?)
                ")->execute([$incidentId, "uploads/$new"]);
                error_log("✓ Recorded photo for incident #{$incidentId}");
            } else {
                error_log("✗ FAILED to move $orig to $dest");
            }
        }
    }

    header('Location: report_incident.php');
    exit;
}

$incidents = $db->query("
    SELECT 
      i.id,
      i.problem_type,
      i.custom_problem,
      i.severity,
      i.description,
      i.informant_name,
      i.informant_department,
      i.status,
      i.assign_to,
      DATE_FORMAT(i.created_at,    '%Y-%m-%d %H:%i') AS created_at,
      DATE_FORMAT(i.assigned_at,   '%Y-%m-%d %H:%i') AS assigned_at,
      DATE_FORMAT(i.inprogress_at, '%Y-%m-%d %H:%i') AS inprogress_at,
      DATE_FORMAT(i.resolved_at,   '%Y-%m-%d %H:%i') AS resolved_at
    FROM incidents i
    WHERE i.status IN ('Open','In Progress')
    ORDER BY i.id DESC
")->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Assigned Incidents</title>
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

        /* 1 = green, 2 = blue, 3 = yellow, 4 = orange, 5 = red */
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

        .container {
            margin-left: 50px;
            padding: 20px;
            transition: margin-left .3s, width .3s;
            overflow-x: auto;
            width: calc(100% - 50px);

        }

        .container.expanded {
            margin-left: 250px;
            width: calc(100% - 250px);
        }

        .container>.card {
            margin: 1rem 0;
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

    <div class="container" id="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Assigned Incidents</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#incidentModal">
                    Add Incident
                </button>
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
                                <th>Created</th>
                                <th>Status</th>
                                <th>Assigned To</th>
                                <th>Assigned At</th>
                                <th>In Prog.</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($incidents as $i):
                                $ps = $db->prepare("SELECT file_path FROM incident_photos WHERE incident_id = ?");
                                $ps->execute([$i['id']]);
                                $photos = $ps->fetchAll(PDO::FETCH_COLUMN);
                                // JSON-encode for HTML attribute; use unescaped slashes so URLs stay clean
                                $photosJson = json_encode($photos, JSON_UNESCAPED_SLASHES);
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($i['id']) ?></td>
                                    <td><?= htmlspecialchars($i['problem_type']) ?></td>
                                    <td><?= htmlspecialchars($i['custom_problem']) ?></td>
                                    <td>
                                        <span class="badge-severity badge-severity-<?= $i['severity'] ?>">
                                            <?= htmlspecialchars($i['severity']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($i['informant_name']) ?></td>
                                    <td><?= htmlspecialchars($i['informant_department']) ?></td>
                                    <td><?= $i['created_at'] ?></td>
                                    <td>
                                        <span class="badge-status badge-<?=
                                            strtolower(str_replace(' ', '', $i['status'])) ?>">
                                            <?= htmlspecialchars($i['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($i['assign_to'] ?: '-') ?></td>
                                    <td><?= $i['assigned_at'] ?: '-' ?></td>
                                    <td><?= $i['inprogress_at'] ?: '-' ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary view-btn" data-bs-toggle="modal"
                                            data-bs-target="#viewModal" data-id="<?= $i['id'] ?>"
                                            data-problem="<?= htmlspecialchars($i['problem_type']) ?>"
                                            data-custom="<?= htmlspecialchars($i['custom_problem']) ?>"
                                            data-severity="<?= htmlspecialchars($i['severity']) ?>"
                                            data-informant="<?= htmlspecialchars($i['informant_name']) ?>"
                                            data-department="<?= htmlspecialchars($i['informant_department']) ?>"
                                            data-description="<?= htmlspecialchars($i['description']) ?>"
                                            data-created="<?= $i['created_at'] ?>"
                                            data-status="<?= htmlspecialchars($i['status']) ?>"
                                            data-assign="<?= htmlspecialchars($i['assign_to']) ?>"
                                            data-assigned_at="<?= $i['assigned_at'] ?>"
                                            data-inprogress_at="<?= $i['inprogress_at'] ?>"
                                            data-resolved_at="<?= $i['resolved_at'] ?>"
                                            data-photos='<?= htmlspecialchars($photosJson, ENT_QUOTES) ?>'>
                                            View
                                        </button>
                                        <button class="btn btn-sm btn-info edit-btn" …>Edit</button>
                                        <form method="post" style="display:inline" …>
                                            <input type="hidden" name="delete_id" value="<?= $i['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>



        <!-- Add Incident Modal -->
        <div class="modal fade" id="incidentModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="post" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Incident</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Problem Type, Severity, Staff Code, Name, Dept., Description -->
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
                            </div>
                            <div class="row g-3 mt-3">
                                <div class="col-md-4">
                                    <label>Severity</label>
                                    <select name="severity" class="form-select">
                                        <option value="1">1 - Lowest</option>
                                        <option value="2">2 - Low</option>
                                        <option value="3">3 - Normal</option>
                                        <option value="4">4 - High</option>
                                        <option value="5">5 - Highest</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label>Staff Code</label>
                                    <input type="text" id="staffcode" name="informant_staffcode" class="form-control"
                                        placeholder="Type your staff code" list="staffcodeList" required>
                                    <datalist id="staffcodeList">
                                        <?php foreach ($hrmUsers as $u): ?>
                                            <option value="<?= htmlspecialchars($u['staffcode']) ?>">
                                            <?php endforeach; ?>
                                    </datalist>
                                </div>
                                <div class="col-md-4">
                                    <label>Name</label>
                                    <input type="text" id="informant_name" name="informant_name" class="form-control"
                                        readonly required>
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label>Department</label>
                                    <input type="text" id="informant_department" name="informant_department"
                                        class="form-control" readonly required>
                                </div>
                            </div>
                            <div class="row g-3 mt-3">
                                <div class="col-md-12">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control" rows="4"
                                        placeholder="Describe the incident" required></textarea>
                                </div>
                            </div>
                            <div class="mb-3 mt-3">
                                <label for="photos" class="form-label">Upload Photos</label>
                                <input class="form-control" type="file" name="photos[]" id="photos" accept="image/*"
                                    multiple>
                                <div class="form-text">
                                    Select one or more images (JPEG/PNG).
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Submit Incident</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Incident Modal -->
        <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Incident Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <dl class="row">
                            <dt class="col-sm-3">ID</dt>
                            <dd class="col-sm-9" id="view-id"></dd>
                            <dt class="col-sm-3">Type</dt>
                            <dd class="col-sm-9" id="view-problem"></dd>
                            <dt class="col-sm-3">Custom</dt>
                            <dd class="col-sm-9" id="view-custom"></dd>
                            <dt class="col-sm-3">Severity</dt>
                            <dd class="col-sm-9" id="view-severity"></dd>
                            <dt class="col-sm-3">Informant</dt>
                            <dd class="col-sm-9" id="view-informant"></dd>
                            <dt class="col-sm-3">Dept.</dt>
                            <dd class="col-sm-9" id="view-dept"></dd>
                            <dt class="col-sm-3">Description</dt>
                            <dd class="col-sm-9" id="view-description"></dd>
                            <dt class="col-sm-3">Created At</dt>
                            <dd class="col-sm-9" id="view-created"></dd>
                            <dt class="col-sm-3">Status</dt>
                            <dd class="col-sm-9" id="view-status"></dd>
                            <dt class="col-sm-3">Assigned To</dt>
                            <dd class="col-sm-9" id="view-assign"></dd>
                            <dt class="col-sm-3">Assigned At</dt>
                            <dd class="col-sm-9" id="view-assigned_at"></dd>
                            <dt class="col-sm-3">In Progress</dt>
                            <dd class="col-sm-9" id="view-inprogress_at"></dd>
                            <dt class="col-sm-3">Resolved At</dt>
                            <dd class="col-sm-9" id="view-resolved_at"></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>




        <!-- Edit Incident Modal -->
        <div class="modal fade" id="editIncidentModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" id="editIncidentForm">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Incident</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="update_id" id="edit_update_id">
                            <div class="mb-3">
                                <label>Status</label>
                                <select name="status" id="edit_status" class="form-select">
                                    <option>Open</option>
                                    <option>In Progress</option>
                                    <option>Closed</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Assigned To</label>
                                <select name="assign_to" id="edit_assign_to" class="form-select">
                                    <option value="">-- Unassigned --</option>
                                    <?php foreach ($assignees as $a): ?>
                                        <option value="<?= htmlspecialchars($a) ?>"><?= htmlspecialchars($a) ?></option>
                                    <?php endforeach; ?>
                                    <option value="__other__">Other…</option>
                                </select>
                                <input type="text" name="assign_custom" id="edit_assign_custom"
                                    class="form-control mt-2" placeholder="Enter new assignee" style="display:none;">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- JS -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
        <script>
            $(document).ready(function () {
                // ─── DataTables init ───────────────────────────────────────────
                $('#incidentTable').DataTable({
                    dom:
                        '<"row mb-3"<"col-sm-6"l><"col-sm-6"f>>' +
                        '<"table-responsive"t>' +
                        '<"row mt-3"<"col-sm-5"i><"col-sm-7"p>>',
                    scrollX: true,
                    lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                    pageLength: 10,
                    ordering: true,
                    order: [[0, 'desc']],
                    columnDefs: [{ orderable: false, targets: -1 }]
                });

                // ─── staffcode → auto-fill name & dept ──────────────────────────
                const hrmData = <?php echo json_encode($hrmUsers ?? [], JSON_HEX_TAG); ?>;
                $('#staffcode').on('input change', function () {
                    const code = this.value,
                        user = hrmData.find(u => u.staffcode === code);
                    $('#informant_name').val(user ? user.name : '');
                    $('#informant_department').val(user ? user.branch_name : '');
                });

                // ─── View-modal ─────────────────────────────────────────────────
                $('.view-btn').on('click', function () {
                    const b = $(this);
                    $('#view-id').text(b.data('id'));
                    $('#view-problem').text(b.data('problem'));
                    $('#view-custom').text(b.data('custom'));
                    $('#view-severity').text(b.data('severity'));
                    $('#view-informant').text(b.data('informant'));
                    $('#view-dept').text(b.data('department'));
                    $('#view-description').text(b.data('description'));
                    $('#view-created').text(b.data('created'));
                    $('#view-status').text(b.data('status'));
                    $('#view-assign').text(b.data('assign'));
                    $('#view-assigned_at').text(b.data('assigned_at'));
                    $('#view-inprogress_at').text(b.data('inprogress_at'));
                    $('#view-resolved_at').text(b.data('resolved_at'));
                });

                // ─── Edit-modal ────────────────────────────────────────────────
                $('.edit-btn').on('click', function () {
                    const btn = $(this),
                        id = btn.data('id'),
                        st = btn.data('status'),
                        as = btn.data('assign');
                    $('#edit_update_id').val(id);
                    $('#edit_status').val(st);
                    if (as && !['', 'Open', 'In Progress', 'Closed'].includes(as)) {
                        $('#edit_assign_to').val('__other__');
                        $('#edit_assign_custom').show().val(as);
                    } else {
                        $('#edit_assign_to').val(as || '');
                        $('#edit_assign_custom').hide().val('');
                    }
                    new bootstrap.Modal($('#editIncidentModal')).show();
                });
                $('#edit_assign_to').on('change', function () {
                    if (this.value === '__other__') {
                        $('#edit_assign_custom').show().focus();
                    } else {
                        $('#edit_assign_custom').hide();
                    }
                });
            });
        </script>
    </div>
</body>

</html>