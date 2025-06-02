<?php
// hardware_list.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
date_default_timezone_set('Asia/Bangkok');

include __DIR__ . '/db_config.php';

$dbItd = getDb('itd');
$dbHrm = getDb('hrm');

$hrmUsers = $dbHrm->query("
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

// build map for lookups
$hrmMap = [];
foreach ($hrmUsers as $u) {
    $hrmMap[$u['staffcode']] = [
        'name' => $u['name'],
        'branch_name' => $u['branch_name'],
    ];
}

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

$hardwareByUser = [];
foreach ($hardwareItems as $h) {
    $user = $h['user_name'];
    if (!isset($hardwareByUser[$user])) {
        $hardwareByUser[$user] = [];
    }

    $hardwareByUser[$user][] = [
        'id' => $h['id'],
        'name' => $h['name']
    ];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assets: Hardware & Software</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" />

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

        }

        .container-fluid.expanded {
            margin-left: 250px;
        }

        .container-fluid>.card {
            width: 100%;

        }

        .main-content {
            width: 90%;
            margin: 0 auto;
        }

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


        .main-content h1 {
            font-family: Arial, sans-serif;
            font-size: 1.5rem;
            /* same generic size as DataTables headers */
            margin-bottom: 1rem;
            /* space below */
            color: #333;
            /* same dark color as table text */
        }


        .hardware-form {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            font-family: Arial, sans-serif;
            max-width: 800px;
        }

        .hardware-form .field-group {
            margin-bottom: 0.75rem;
        }

        .hardware-form .field-group label {
            display: block;
            font-size: 1rem;
            /* same as table text */
            margin-bottom: 0.25rem;
            color: #333;
        }

        .hardware-form .field-group input,
        .hardware-form .field-group textarea,
        .hardware-form .field-group select {
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 1rem;

            line-height: 1.5;

            padding: 0.4rem 0.6rem;

            border: 1px solid #ccc;

            border-radius: 3px;

            box-sizing: border-box;
        }

        .hardware-form .field-group input:focus,
        .hardware-form .field-group textarea:focus,
        .hardware-form .field-group select:focus {
            outline: none;
            border-color: #66afe9;
            box-shadow: 0 0 0 0.2rem rgba(102, 175, 233, 0.3);
        }

        .hardware-form button {
            font-family: Arial, sans-serif;
            font-size: 1rem;
            line-height: 1.5;
            padding: 0.45rem 1rem;
            /* similar to DataTables paging button */
            background-color: #007bff;
            /* match DataTables blue highlight */
            color: #fff;
            border: 1px solid #007bff;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }

        .hardware-form button:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .hardware-form a {
            display: inline-block;
            font-family: Arial, sans-serif;
            font-size: 1rem;
            line-height: 1.5;
            padding: 0.45rem 1rem;
            background-color: #6c757d;
            /* gray secondary style */
            color: #fff;
            text-decoration: none;
            border: 1px solid #6c757d;
            border-radius: 3px;
            margin-left: 0.5rem;
            transition: background-color 0.15s ease;
        }

        .hardware-form a:hover {
            background-color: #5a6268;
            border-color: #5a6268;
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
                    <input type="hidden" name="edit_id" value="<?= $editItem['id'] ?>" />
                    <input type="hidden" name="asset_type" id="asset_type" value="<?= $editType ?>" />
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

                <div id="hardware_fields" style="display: none;">
                    <div class="field-group">
                        <label for="name">Hardware Name:</label>
                        <input name="name" id="name" placeholder="Name"
                            value="<?= htmlspecialchars($editItem['name'] ?? '') ?>" />
                    </div>
                    <div class="field-group">
                        <label for="type">Hardware Type:</label>
                        <input name="type" id="type" placeholder="Type"
                            value="<?= htmlspecialchars($editItem['type'] ?? '') ?>" />
                    </div>
                    <div class="field-group">
                        <label for="description">Description:</label>
                        <textarea name="description" id="description"
                            rows="2"><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- ▼ “Add/​Edit Software” fields ▼ -->
                <div id="software_fields" style="display: none;">
                    <div class="field-group">
                        <label for="software_name">Software Name:</label>
                        <input name="software_name" id="software_name" placeholder="Name"
                            value="<?= htmlspecialchars($editItem['software_name'] ?? '') ?>" />
                    </div>
                    <div class="field-group">
                        <label for="license_key">License Key:</label>
                        <input name="license_key" id="license_key" placeholder="Key"
                            value="<?= htmlspecialchars($editItem['license_key'] ?? '') ?>" />
                    </div>

                    <div class="field-group" id="hardware_select_group" style="display: none;">
                        <label for="hardware_id">Select Hardware:</label>
                        <select name="hardware_id" id="hardware_id">
                            <option value="">-- Select hardware --</option>
                        </select>
                    </div>
                </div>


                <div class="field-group">
                    <label for="staffcode">Assign to Staff:</label>
                    <select name="staffcode" id="staffcode" required>
                        <option value="">-- select staff --</option>
                        <?php foreach ($hrmUsers as $u): ?>
                            <option value="<?= htmlspecialchars($u['staffcode']) ?>" <?= (isset($editItem['user_name']) && $editItem['user_name'] === $u['staffcode']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['staffcode']) ?> /
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
                    <a href="hardware_list.php">Cancel</a>
                <?php endif; ?>
            </form>

            <h2>Hardware List</h2>
            <div class="table-wrapper">
                <table id="hardwareTable" class="display responsive nowrap" style="width: 100%">
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
                                        <ul style="margin: 0; padding-left: 1em;">
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

            <h2>Software Licenses</h2>
            <div class="table-wrapper">
                <table id="softwareTable" class="display responsive nowrap" style="width: 100%">
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


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        $(function () {
            $('#hardwareTable').DataTable({
                responsive: {
                    details: {
                        type: 'inline'
                    }
                },
                autoWidth: false,
                pageLength: 10,
                order: [[0, 'asc']],
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 1 },
                    { responsivePriority: 3, targets: 2 },
                    { responsivePriority: 4, targets: 3 },
                    { responsivePriority: 5, targets: 4 },
                    { responsivePriority: 6, targets: 5 },
                    { responsivePriority: 7, targets: 6 },
                    { responsivePriority: 8, targets: 7 },
                    { responsivePriority: 9, targets: 8 }
                ]
            });

            $('#softwareTable').DataTable({
                responsive: {
                    details: {
                        type: 'inline'
                    }
                },
                autoWidth: false,
                pageLength: 10,
                order: [[0, 'asc']],
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 1 },
                    { responsivePriority: 3, targets: 2 },
                    { responsivePriority: 4, targets: 3 },
                    { responsivePriority: 5, targets: 4 },
                    { responsivePriority: 6, targets: 5 },
                    { responsivePriority: 7, targets: 6 }
                ]
            });

            function toggleFields() {
                const t = $('#asset_type').val();
                $('#hardware_fields').toggle(t === 'hardware');
                $('#software_fields').toggle(t === 'software');


                if (t !== 'software') {
                    $('#hardware_select_group').hide();
                } else {
                    const sc = $('#staffcode').val();
                    populateHardwareDropdown(sc);
                }
            }
            $('#asset_type').on('change', toggleFields);

            $('#staffcode').on('change', function () {
                const t = $('#asset_type').val();
                if (t === 'software') {
                    const sc = $(this).val();
                    populateHardwareDropdown(sc);
                }
            });

            function populateHardwareDropdown(staffcode) {
                const $group = $('#hardware_select_group');
                const $select = $('#hardware_id');
                $select.empty();

                if (!staffcode || !hardwareByUser.hasOwnProperty(staffcode)) {
                    $group.hide();
                    return;
                }


                const hwList = hardwareByUser[staffcode];
                if (hwList.length === 0) {
                    $group.hide();
                    return;
                }


                $select.append('<option value="">-- Select hardware --</option>');
                hwList.forEach(item => {

                    $select.append(
                        $('<option>')
                            .val(item.id)
                            .text(item.name)
                    );
                });

                $group.show();
            }

            toggleFields();



        });
        const hardwareByUser = <?= json_encode($hardwareByUser, JSON_UNESCAPED_UNICODE) ?>;
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