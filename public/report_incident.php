<?php
// report_incident.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Bangkok');

session_start();
include __DIR__ . '/db_config.php';
$db = getDb('itd');

// ─── pull HRM users for staffcode lookup ─────────────────────────────────
$hrm = getDb('hrm');
$hrmUsers = $hrm->query("
    SELECT 
      u.staffcode,
      CONCAT(u.firstname,' ',u.lastname) AS name,
      CASE
        WHEN u.branch_id = 1 THEN 'Q.A'
		WHEN u.branch_id = 2 THEN 'BOX 1'
		WHEN u.branch_id = 3 THEN 'BOX 2'
		WHEN u.branch_id = 4  THEN 'BOX 3'
		WHEN u.branch_id = 5  THEN 'BOX 4'
		WHEN u.branch_id = 6  THEN 'BOX กะ'
		WHEN u.branch_id = 7  THEN 'Collector ติดตามเก็บเงิน'
		WHEN u.branch_id = 8  THEN 'CRM'
		WHEN u.branch_id = 9  THEN 'DBU'
		WHEN u.branch_id = 10 THEN 'DETAIL ต่างจังหวัด(Sale โรงพยาบาล)'
		WHEN u.branch_id = 11 THEN 'DETAIL กทม(Sale โรงพยาบาล)'
		WHEN u.branch_id = 12 THEN 'DETAIL2 ต่างจังหวัด (Sale โรงพยาบาล)'
		WHEN u.branch_id = 13 THEN 'FOOD'
		WHEN u.branch_id = 14 THEN 'Import-Export'
		WHEN u.branch_id = 15 THEN 'INKJET'
		WHEN u.branch_id = 16 THEN 'IT'
		WHEN u.branch_id = 17 THEN 'M 1'
		WHEN u.branch_id = 18 THEN 'M 2'
		WHEN u.branch_id = 19 THEN 'M 3'
		WHEN u.branch_id = 20 THEN 'M 4'
		WHEN u.branch_id = 21 THEN 'M 5'
		WHEN u.branch_id = 22 THEN 'Modern Trade'
		WHEN u.branch_id = 23 THEN 'Online'
		WHEN u.branch_id = 24 THEN 'OTC ต่างจังหวัด(Sale ร้านค้า)'
		WHEN u.branch_id = 25 THEN 'OTC 2 กทม.(Sale ร้านค้า)'
		WHEN u.branch_id = 26 THEN 'OTC 2 ต่างจังหวัด(Sale ร้านค้า)'
		WHEN u.branch_id = 27 THEN 'OTC 3 ต่างจังหวัด(Sale ร้านค้า)'
		WHEN u.branch_id = 28 THEN 'OTC กทม.(Sale ร้านค้า)'
		WHEN u.branch_id = 29 THEN 'OTC 3 กทม.(Sale ร้านค้า)'
		WHEN u.branch_id = 30 THEN 'P/M'
		WHEN u.branch_id = 31 THEN 'PROJECT MANAGER'
		WHEN u.branch_id = 32 THEN 'TMT'
		WHEN u.branch_id = 33 THEN 'ขนส่ง'
		WHEN u.branch_id = 34 THEN 'คลังบรรจุภัณฑ์'
		WHEN u.branch_id = 35 THEN 'คลังวัตถุดิบ'
		WHEN u.branch_id = 36 THEN 'คลังสินค้าสำเร็จรูป'
		WHEN u.branch_id = 37 THEN 'ควบคุมคุณภาพ'
		WHEN u.branch_id = 38 THEN 'ควบคุมคุณภาพด้านจุลชีววิทยา'
		WHEN u.branch_id = 39 THEN 'ควบคุมคุณภาพด้านบรรจุภัณฑ์'
		WHEN u.branch_id = 40 THEN 'เคลือบ'
		WHEN u.branch_id = 41 THEN 'แคปซูล'
		WHEN u.branch_id = 42 THEN 'งานเอกสารผลิต'
		WHEN u.branch_id = 43 THEN 'จัดซื้อ'
		WHEN u.branch_id = 44 THEN 'เจ้าหน้าที่ความปลอดภัยในการทำงาน'
		WHEN u.branch_id = 45 THEN 'ชิ้งยา'
		WHEN u.branch_id = 46 THEN 'ซ่อมบำรุง'
		WHEN u.branch_id = 47 THEN 'ตรวจบิล'
		WHEN u.branch_id = 48 THEN 'ตอกยา'
		WHEN u.branch_id = 49 THEN 'ทรัพยากรบุคคล'
		WHEN u.branch_id = 50 THEN 'ทะเบียนยา'
		WHEN u.branch_id = 51 THEN 'ทั่วไป'
		WHEN u.branch_id = 52 THEN 'ทั่วไป'
		WHEN u.branch_id = 53 THEN 'ธุรการขาย'
		WHEN u.branch_id = 54 THEN 'ธุรการควบคุมเอกสาร'
		WHEN u.branch_id = 55 THEN 'นักวิทยาศาสตร์'
		WHEN u.branch_id = 56 THEN 'บรรจุ'
		WHEN u.branch_id = 57 THEN 'บรรจุยาครีม'
		WHEN u.branch_id = 58 THEN 'บรรจุยาผง'
		WHEN u.branch_id = 59 THEN 'บริหาร'
		WHEN u.branch_id = 60 THEN 'บริสเตอร์แพค'
		WHEN u.branch_id = 61 THEN 'บัญชีภาษี'
		WHEN u.branch_id = 62 THEN 'บัญชีลูกหนี้'
		WHEN u.branch_id = 63 THEN 'บัญชีและการเงิน'
		WHEN u.branch_id = 64 THEN 'บัญชีและการเงิน'
		WHEN u.branch_id = 65 THEN 'ประกันคุณภาพวิเคราะห์'
		WHEN u.branch_id = 66 THEN 'ประสานงานการผลิต'
		WHEN u.branch_id = 67 THEN 'ประสานขนส่ง'
		WHEN u.branch_id = 68 THEN 'ผลิตภัณฑ์&การตลาด'
		WHEN u.branch_id = 69 THEN 'ผลิตภัณฑ์&การตลาด'
		WHEN u.branch_id = 70 THEN 'ผสมยาน้ำ,ยาครีม'
		WHEN u.branch_id = 71 THEN 'ผสมยาเม็ด'
		WHEN u.branch_id = 72 THEN 'ผู้จัดการทั่วไป'
		WHEN u.branch_id = 73 THEN 'ผู้ช่วยเภสัชกร'
		WHEN u.branch_id = 74 THEN 'พัฒนาธุรกิจ'
		WHEN u.branch_id = 75 THEN 'พิมพ์ฉลาก'
		WHEN u.branch_id = 76 THEN 'ฟอล์ย Manual'
		WHEN u.branch_id = 77 THEN 'ฟิล์มยา'
		WHEN u.branch_id = 78 THEN 'เภสัชกรฝ่าย R&D'
		WHEN u.branch_id = 79 THEN 'เภสัชกรฝ่ายควบคุมคุณภาพ'
		WHEN u.branch_id = 80 THEN 'เภสัชกรฝ่ายประกันคุณภาพ'
		WHEN u.branch_id = 81 THEN 'เภสัชกรฝ่ายผลิต'
		WHEN u.branch_id = 82 THEN 'แม่บ้าน'
		WHEN u.branch_id = 83 THEN 'รับ/จ่าย'
		WHEN u.branch_id = 84 THEN 'โรตารี่'
		WHEN u.branch_id = 85 THEN 'ล้างถาด'
		WHEN u.branch_id = 86 THEN 'เลขาผู้บริหาร'
		WHEN u.branch_id = 87 THEN 'วิจัย&พัฒนาผลิตภัณฑ์'
		WHEN u.branch_id = 88 THEN 'บรรจุยาน้ำ'
		WHEN u.branch_id = 89 THEN 'OTC3 กทม (Office)'
		WHEN u.branch_id = 90 THEN 'Sale Director'
		WHEN u.branch_id = 91 THEN 'QA Senior'
		WHEN u.branch_id = 92 THEN 'ขายในประเทศ'
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


// ─── Unified POST handler ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['update_id'])) {
        $id = (int) $_POST['update_id'];
        $status = $_POST['status'] ?? 'Open';
        $raw = $_POST['assign_to'] ?? '';
        $assign = $raw === '__other__'
            ? trim($_POST['assign_custom'] ?? '')
            : trim($raw);
        $remark = trim($_POST['remark'] ?? '');
        $now = date('Y-m-d H:i:s');

        // base UPDATE (always include remark)
        $sql = "UPDATE incidents
                       SET updated_at = ?,
                           status     = ?,
                           assign_to  = ?,
                           remark     = ?";
        $params = [$now, $status, $assign, $remark];

        // conditional timestamps
        if ($status === 'Open') {
            // someone set it back to Open → stamp assigned_at
            $sql .= ", assigned_at = ?";
            $params[] = $now;
        }
        if ($status === 'In Progress') {
            // **NEW**: stamp both assigned_at & inprogress_at
            $sql .= ", assigned_at = ?, inprogress_at = ?";
            $params[] = $now;
            $params[] = $now;
        }
        if ($status === 'Closed') {
            $sql .= ", resolved_at = ?";
            $params[] = $now;
        }
        if ($status === 'Cancelled') {
            $sql .= ", cancelled_at = ?";
            $params[] = $now;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;
        $db->prepare($sql)->execute($params);

    } else {
        $now = date('Y-m-d H:i:s');
        // a) insert incident (assigned_at left NULL)
        $stmt = $db->prepare("
            INSERT INTO incidents
              (problem_type, custom_problem, severity,
               description, informant_name, informant_department,
               status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'Open', NOW())
        ");
        $stmt->execute([
            $_POST['problem_type'] ?? '',
            $_POST['custom_problem'] ?? '',
            (int) ($_POST['severity'] ?? 0),
            $_POST['description'] ?? '',
            $_POST['informant_name'] ?? '',
            $_POST['informant_department'] ?? '',
        ]);

        // b) grab new incident ID
        $incidentId = $db->lastInsertId();

        // c) handle photo uploads
        if (!empty($_FILES['photos']['tmp_name']) && is_array($_FILES['photos']['tmp_name'])) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir))
                mkdir($uploadDir, 0755, true);

            foreach ($_FILES['photos']['error'] as $i => $err) {
                if ($err !== UPLOAD_ERR_OK)
                    continue;
                $tmp = $_FILES['photos']['tmp_name'][$i];
                $orig = basename($_FILES['photos']['name'][$i]);
                $ext = pathinfo($orig, PATHINFO_EXTENSION);
                $new = uniqid("inc{$incidentId}_") . ".$ext";
                if (move_uploaded_file($tmp, "$uploadDir$new")) {
                    $db->prepare("
                        INSERT INTO incident_photos (incident_id, file_path)
                        VALUES (?, ?)
                    ")->execute([$incidentId, "uploads/$new"]);
                }
            }
        }
    }

    // redirect to clear POST
    header('Location: report_incident.php');
    exit;
}



// ─── Fetch only Open & In Progress incidents for display ───────────
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
      i.remark,
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assigned Incidents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" />
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
        }

        .dataTables_wrapper .dataTables_filter {
            float: right;
        }

        .nowrap th,
        .nowrap td {
            white-space: nowrap;
        }

        .dataTables_scrollBody {
            width: 100% !important;
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
        <div class="card mb-4">
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
                                        <button type="button" class="btn btn-sm btn-secondary view-btn"
                                            data-bs-toggle="modal" data-bs-target="#viewModal" data-id="<?= $i['id'] ?>"
                                            data-problem="<?= htmlspecialchars($i['problem_type']) ?>"
                                            data-custom="<?= htmlspecialchars($i['custom_problem']) ?>"
                                            data-severity="<?= $i['severity'] ?>"
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

                                        <button class="btn btn-sm btn-info edit-btn" data-id="<?= $i['id'] ?>"
                                            data-status="<?= htmlspecialchars($i['status']) ?>"
                                            data-assign="<?= htmlspecialchars($i['assign_to']) ?>"
                                            data-remark="<?= htmlspecialchars($i['remark'] ?? '') ?>">
                                            Edit
                                        </button>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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
                        <dt class="col-sm-3">Photos</dt>
                        <dd class="col-sm-9" id="view-photos"><em>No photos</em></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Full-Screen Photo Preview Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen">
            <div class="modal-content bg-dark">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex justify-content-center align-items-center p-0">
                    <img src="" id="photoModalImg" class="img-fluid" style="max-height:100vh; width:auto;"
                        alt="Incident Photo">
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
                                <option>Cancelled</option>
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
                            <input type="text" name="assign_custom" id="edit_assign_custom" class="form-control mt-2"
                                placeholder="Enter new assignee" style="display:none;">
                        </div>
                        <div class="mb-3" id="edit_remark_group" style="display: none;">
                            <label for="edit_remark" class="form-label">Remark</label>
                            <textarea name="remark" id="edit_remark" class="form-control" rows="3"
                                placeholder="Enter a remark when closing or cancelling"></textarea>
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
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            // ─── Sidebar toggle (unchanged) ────────────────────────────────────
            const sb = document.getElementById('sidebar'),
                ct = document.getElementById('container'),
                exp = localStorage.getItem('sidebarExpanded') === 'true';
            if (exp) sb.classList.add('expanded'), ct.classList.add('expanded');
            sb.querySelector('h2').addEventListener('click', () => {
                const e = sb.classList.toggle('expanded');
                ct.classList.toggle('expanded');
                localStorage.setItem('sidebarExpanded', e);
            });

            // ─── DataTable init (full-width like Add Incident page) ────────────
            $('#incidentTable').DataTable({
                dom:
                    '<"row mb-3"<"col-sm-6"l><"col-sm-6"f>>' +
                    '<"table-responsive"t>' +
                    '<"row mt-3"<"col-sm-5"i><"col-sm-7"p>>',

                responsive: false,
                scrollX: true,
                // responsive: {
                //     details: {
                //         type: 'inline'
                //     }
                // },
                autoWidth: false,
                paging: true,
                lengthChange: true,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                pageLength: 10,
                ordering: true,
                order: [[0, 'desc']],
                columnDefs: [{ orderable: false, targets: -1 }]
            });

            // ─── staffcode → auto-fill name & department ────────────────────────
            const hrmData = <?= json_encode($hrmUsers, JSON_HEX_TAG) ?>;
            $('#staffcode').on('input change', function () {
                const code = this.value,
                    u = hrmData.find(x => x.staffcode === code);
                $('#informant_name').val(u ? u.name : '');
                $('#informant_department').val(u ? u.branch_name : '');
            });

            // ─── Delegate View-modal ───────────────────────────────────────────
            $('#incidentTable').on('click', '.view-btn', function () {
                const btn = $(this),
                    photos = JSON.parse(btn.attr('data-photos') || '[]');

                $('#view-id').text(btn.data('id'));
                $('#view-problem').text(btn.data('problem'));
                $('#view-custom').text(btn.data('custom'));
                $('#view-severity').text(btn.data('severity'));
                $('#view-informant').text(btn.data('informant'));
                $('#view-dept').text(btn.data('department'));
                $('#view-description').text(btn.data('description'));
                $('#view-created').text(btn.data('created'));
                $('#view-status').text(btn.data('status'));
                $('#view-assign').text(btn.data('assign'));
                $('#view-assigned_at').text(btn.data('assigned_at'));
                $('#view-inprogress_at').text(btn.data('inprogress_at'));

                let html = '';
                photos.forEach(p => {
                    html += `
                <img
                  src="${p}"
                  class="img-thumbnail photo-thumb me-1 mb-1"
                  style="max-height:300px; cursor:pointer;"
                  alt="Incident Photo"
                >
            `;
                });
                $('#view-photos').html(html || '<em>No photos</em>');
            });

            // ─── Click on a thumbnail to open full-screen modal ────────────────
            $(document).on('click', '.photo-thumb', function () {
                const src = $(this).attr('src');
                $('#photoModalImg').attr('src', src);
                new bootstrap.Modal($('#photoModal')).show();
            });

            // ─── Delegate Edit-modal populator ─────────────────────────────
            $('#incidentTable tbody').on('click', '.edit-btn', function () {
                const btn = $(this),
                    id = btn.data('id') || '',
                    status = btn.data('status') || '',
                    assign = btn.data('assign') || '',
                    remark = btn.data('remark') || '';

                // 1) Populate the hidden ID, status & remark
                $('#edit_update_id').val(id);
                $('#edit_status').val(status).trigger('change');
                $('#edit_remark').val(remark);

                // 2) See if we have an <option> for this assign value already
                const $sel = $('#edit_assign_to'),
                    $other = $('#edit_assign_custom'),
                    hasOpt = $sel.find(`option[value="${assign}"]`).length > 0;

                if (hasOpt) {
                    // it’s one of your existing names
                    $sel.val(assign);
                    $other.hide().val('');
                }
                else if (assign) {
                    // a custom name
                    $sel.val('__other__');
                    $other.show().val(assign);
                }
                else {
                    // unassigned
                    $sel.val('');
                    $other.hide().val('');
                }

                // 3) Fire the modal
                new bootstrap.Modal($('#editIncidentModal')).show();
            });


            // when user picks “Other…”
            $('#edit_assign_to').on('change', function () {
                if (this.value === '__other__') {
                    $('#edit_assign_custom').show().focus();
                } else {
                    $('#edit_assign_custom').hide().val('');
                }
            });


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