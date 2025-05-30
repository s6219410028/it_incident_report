<?php
// hardware_list.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
date_default_timezone_set('Asia/Bangkok');

include __DIR__ . '/db_config.php';

// ─── 1) Open both ITD & HRM DBs ─────────────────────────────────────────
$dbItd = getDb('itd');
$dbHrm = getDb('hrm');

// ─── 2) Pull HRM users (staffcode, full name, branch) ───────────────────
$hrmUsers = $dbHrm->query("
    SELECT 
      u.staffcode,
      CONCAT(u.firstname,' ',u.lastname) AS name,
      CASE
        WHEN u.branch_id = 1 THEN 'Q.A'
        WHEN u.branch_id = 2 THEN 'BOX 1'
        WHEN u.branch_id = 3 THEN 'BOX 2'
        /* … add all your other WHENs … */
        WHEN u.branch_id = 93 THEN 'ผู้จัดการควบคุมคุณภาพ'
        ELSE 'ไม่ระบุ'
      END AS branch_name
    FROM users u
    ORDER BY u.firstname, u.lastname
")->fetchAll(PDO::FETCH_ASSOC);

// build map for lookups
$hrmMap = [];
foreach ($hrmUsers as $u) {
    $hrmMap[$u['staffcode']] = [
        'name' => $u['name'],
        'branch_name' => $u['branch_name'],
    ];
}

// ─── 3) Handle GET edit for hardware or software ─────────────────────────
$editType = null;
$editItem = null;
if (!empty($_GET['edit_type']) && in_array($_GET['edit_type'], ['hardware', 'software'], true)) {
    $editType = $_GET['edit_type'];
    $editId = (int) $_GET['edit_id'];
    if ($editType === 'hardware') {
        $stmt = $dbItd->prepare("SELECT * FROM hardware WHERE id = ?");
        $stmt->execute([$editId]);
        $editItem = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $stmt = $dbItd->prepare("SELECT * FROM software_licenses WHERE id = ?");
        $stmt->execute([$editId]);
        $editItem = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// ─── 4) Handle POST insert/update ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assetType = $_POST['asset_type'] ?? '';
    $staffcode = trim($_POST['staffcode'] ?? '');

    if ($assetType === 'hardware') {
        $name = trim($_POST['name']);
        $type = trim($_POST['type']);
        $description = trim($_POST['description']);
        if (!empty($_POST['edit_id'])) {
            $stmt = $dbItd->prepare("
                UPDATE hardware
                   SET name        = ?,
                       type        = ?,
                       description = ?,
                       user_name   = ?
                 WHERE id = ?
            ");
            $stmt->execute([$name, $type, $description, $staffcode, $_POST['edit_id']]);
        } else {
            $stmt = $dbItd->prepare("
                INSERT INTO hardware
                  (name,type,description,user_name)
                VALUES (?,?,?,?)
            ");
            $stmt->execute([$name, $type, $description, $staffcode]);
        }
    } elseif ($assetType === 'software') {
        $softwareName = trim($_POST['software_name']);
        $licenseKey = trim($_POST['license_key']);
        if (!empty($_POST['edit_id'])) {
            $stmt = $dbItd->prepare("
                UPDATE software_licenses
                   SET software_name = ?,
                       license_key   = ?,
                       user_name     = ?
                 WHERE id = ?
            ");
            $stmt->execute([$softwareName, $licenseKey, $staffcode, $_POST['edit_id']]);
        } else {
            $stmt = $dbItd->prepare("
                INSERT INTO software_licenses
                  (software_name,license_key,user_name)
                VALUES (?,?,?)
            ");
            $stmt->execute([$softwareName, $licenseKey, $staffcode]);
        }
    }

    header('Location: hardware_list.php');
    exit;
}

// ─── 5) Load all hardware & software entries ────────────────────────────
$hardwareItems = $dbItd
    ->query("SELECT id,name,type,description,user_name FROM hardware ORDER BY id")
    ->fetchAll(PDO::FETCH_ASSOC);

$softwareItems = $dbItd
    ->query("SELECT id,software_name,license_key,user_name FROM software_licenses ORDER BY id")
    ->fetchAll(PDO::FETCH_ASSOC);

// prepare once for software lookup under hardware
$softStmt = $dbItd->prepare("
    SELECT software_name, license_key
      FROM software_licenses
     WHERE user_name = ?
");

// enrich hardware rows
foreach ($hardwareItems as &$h) {
    $code = $h['user_name'];
    if (isset($hrmMap[$code])) {
        $h['hrm_name'] = $hrmMap[$code]['name'];
        $h['hrm_branch'] = $hrmMap[$code]['branch_name'];
    } else {
        $h['hrm_name'] = '(not found)';
        $h['hrm_branch'] = '';
    }
    $softStmt->execute([$code]);
    $h['licenses'] = $softStmt->fetchAll(PDO::FETCH_ASSOC);
}
unset($h);

// enrich software rows
foreach ($softwareItems as &$s) {
    $code = $s['user_name'];
    if (isset($hrmMap[$code])) {
        $s['hrm_name'] = $hrmMap[$code]['name'];
        $s['hrm_branch'] = $hrmMap[$code]['branch_name'];
    } else {
        $s['hrm_name'] = '(not found)';
        $s['hrm_branch'] = '';
    }
}
unset($s);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Assets: Hardware & Software</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        {
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
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
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
        <div class="main-content">
            <h1>Assets Management</h1>

            <form class="hardware-form" method="post" action="">
                <h3>
                    <?= $editType === 'software'
                        ? 'Edit Software #' . $editItem['id']
                        : ($editType === 'hardware'
                            ? 'Edit Hardware #' . $editItem['id']
                            : 'Add New Asset') ?>
                </h3>

                <?php if ($editType): ?>
                    <input type="hidden" name="edit_id" value="<?= $editItem['id'] ?>">
                    <input type="hidden" name="asset_type" id="asset_type" value="<?= $editType ?>">
                <?php else: ?>
                    <div class="field-group">
                        <label for="asset_type">Asset Type:</label>
                        <select name="asset_type" id="asset_type" required>
                            <option value="">-- select type --</option>
                            <option value="hardware">Hardware</option>
                            <option value="software">Software</option>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- hardware fields -->
                <div id="hardware_fields" style="display:none;">
                    <div class="field-group">
                        <label for="name">Hardware Name:</label>
                        <input name="name" id="name" placeholder="Name"
                            value="<?= htmlspecialchars($editItem['name'] ?? '') ?>">
                    </div>
                    <div class="field-group">
                        <label for="type">Hardware Type:</label>
                        <input name="type" id="type" placeholder="Type"
                            value="<?= htmlspecialchars($editItem['type'] ?? '') ?>">
                    </div>
                    <div class="field-group">
                        <label for="description">Description:</label>
                        <textarea name="description" id="description"
                            rows="2"><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- software fields -->
                <div id="software_fields" style="display:none;">
                    <div class="field-group">
                        <label for="software_name">Software Name:</label>
                        <input name="software_name" id="software_name" placeholder="Name"
                            value="<?= htmlspecialchars($editItem['software_name'] ?? '') ?>">
                    </div>
                    <div class="field-group">
                        <label for="license_key">License Key:</label>
                        <input name="license_key" id="license_key" placeholder="Key"
                            value="<?= htmlspecialchars($editItem['license_key'] ?? '') ?>">
                    </div>
                </div>

                <!-- common staffcode -->
                <div class="field-group">
                    <label for="staffcode">Assign to Staff:</label>
                    <select name="staffcode" id="staffcode" required>
                        <option value="">-- select staff --</option>
                        <?php foreach ($hrmUsers as $u): ?>
                            <option value="<?= htmlspecialchars($u['staffcode']) ?>" <?= isset($editItem['user_name']) && $editItem['user_name'] == $u['staffcode']
                                  ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['name']) ?>
                                (<?= htmlspecialchars($u['staffcode']) ?> /
                                <?= htmlspecialchars($u['branch_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit">
                    <?= $editType === 'software'
                        ? 'Save Software'
                        : ($editType === 'hardware'
                            ? 'Save Hardware'
                            : 'Add Asset') ?>
                </button>
                <?php if ($editType): ?>
                    <a href="hardware_list.php" style="margin-left:10px">Cancel</a>
                <?php endif; ?>
            </form>

            <!-- ▼ Hardware Table ▼ -->
            <h2>Hardware List</h2>
            <div class="table-wrapper">
                <table id="hardwareTable" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Staffcode</th>
                            <th>Name</th>
                            <th>Branch</th>
                            <th>Licenses</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hardwareItems as $h): ?>
                            <tr>
                                <td><?= $h['id'] ?></td>
                                <td><?= htmlspecialchars($h['name']) ?></td>
                                <td><?= htmlspecialchars($h['type']) ?></td>
                                <td><?= htmlspecialchars($h['description']) ?></td>
                                <td><?= htmlspecialchars($h['user_name']) ?></td>
                                <td><?= htmlspecialchars($h['hrm_name']) ?></td>
                                <td><?= htmlspecialchars($h['hrm_branch']) ?></td>
                                <td>
                                    <?php if ($h['licenses']): ?>
                                        <ul style="margin:0;padding-left:1em;">
                                            <?php foreach ($h['licenses'] as $s): ?>
                                                <li>
                                                    <?= htmlspecialchars($s['software_name']) ?>
                                                    (<?= htmlspecialchars($s['license_key']) ?>)
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?edit_type=hardware&edit_id=<?= $h['id'] ?>">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- ▼ Software Table ▼ -->
            <h2>Software Licenses</h2>
            <div class="table-wrapper">
                <table id="softwareTable" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Software</th>
                            <th>Key</th>
                            <th>Staffcode</th>
                            <th>Name</th>
                            <th>Branch</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($softwareItems as $s): ?>
                            <tr>
                                <td><?= $s['id'] ?></td>
                                <td><?= htmlspecialchars($s['software_name']) ?></td>
                                <td><?= htmlspecialchars($s['license_key']) ?></td>
                                <td><?= htmlspecialchars($s['user_name']) ?></td>
                                <td><?= htmlspecialchars($s['hrm_name']) ?></td>
                                <td><?= htmlspecialchars($s['hrm_branch']) ?></td>
                                <td>
                                    <a href="?edit_type=software&edit_id=<?= $s['id'] ?>">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <script>
        $(function () {
            // DataTables
            $('#hardwareTable').DataTable({ scrollX: true, pageLength: 10, order: [[0, 'asc']] });
            $('#softwareTable').DataTable({ scrollX: true, pageLength: 10, order: [[0, 'asc']] });

            // show/hide form sections
            function toggleFields() {
                const t = $('#asset_type').val();
                $('#hardware_fields').toggle(t === 'hardware');
                $('#software_fields').toggle(t === 'software');
            }
            $('#asset_type').on('change', toggleFields);
            toggleFields();  // on load, esp. for edit

        });
    </script>

    <!-- sidebar toggle script: -->
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