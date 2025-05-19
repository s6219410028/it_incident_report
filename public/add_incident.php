<?php
// add_incident.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Bangkok');
session_start();

include __DIR__ . '/db_config.php';

// ITD connection
$db = getDb('itd');

// HRM connection to pull staffcodes + names
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
    FROM users AS u
    ORDER BY u.staffcode
")->fetchAll(PDO::FETCH_ASSOC);

// Handle new‐incident POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $now = date('Y-m-d H:i:s');

    $stmt = $db->prepare("
  INSERT INTO incidents
    (problem_type, custom_problem, severity,
     description, informant_name, informant_department,
     status, assigned_at, created_at)
      VALUES (?,?,?,?,?,?,'Open',NULL,NOW())")
        ->execute([
            $_POST['problem_type'],       // 1
            $_POST['custom_problem'],     // 2
            $_POST['severity'],           // 3
            $_POST['description'],        // 4
            $_POST['informant_name'],     // 5
            $_POST['informant_department'],// 6 ← this matches the 6th ?
        ]);
    header('Location: add_incident.php');
    exit;
}

// Fetch all incidents for display (read-only)
$incidents = $db->query("
  SELECT 
    id,
    problem_type,
    custom_problem,
    severity,
    informant_name,
    informant_department,
    status,
    DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') AS created_at
  FROM incidents
  ORDER BY id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add & View Incidents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
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
        }

        .container.expanded {
            margin-left: 250px;
            width: calc(100% - 250px);
        }

        .card {
            margin: 1rem;
        }

        .card-body {
            overflow-x: auto;
        }

        .dataTables_wrapper .dataTables_filter {
            float: right;
        }

        .nowrap th,
        .nowrap td {
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
    </style>
</head>

<body>
    <div class="container" id="container">



        <div class="form-section">
            <h2>Submit New Incident</h2>
            <form method="post">
                <div class="row gy-2">
                    <div class="col-md-4">
                        <label>Problem Type</label>
                        <select name="problem_type" class="form-select">
                            <option>Hardware</option>
                            <option>Software</option>
                            <option>IT Support</option>
                            <option>ERP</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>If Other, specify</label>
                        <input type="text" name="custom_problem" class="form-control" placeholder="Custom Problem">
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

                    <div class="col-md-12">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>

                    <!-- ★ StaffCode Lookup ★ -->
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
                        <input type="text" id="informant_department" name="informant_department" class="form-control"
                            readonly required>
                    </div>
                </div>

                <button class="btn btn-primary mt-3">Submit Incident</button>
            </form>
        </div>
        <br>
        <br>
        <br>
        <h2>All Incidents (Read-Only)</h2>

        <table id="incidentTable" class="table table-striped table-bordered nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Custom</th>
                    <th>Severity</th>
                    <th>Informant</th>
                    <th>Dept.</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incidents as $i): ?>
                    <tr>
                        <td><?= $i['id'] ?></td>
                        <td><?= htmlspecialchars($i['problem_type']) ?></td>
                        <td><?= htmlspecialchars($i['custom_problem']) ?></td>
                        <td>
                            <span class="badge-severity badge-severity-<?= $i['severity'] ?>">
                                <?= htmlspecialchars($i['severity']) ?>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // build a JS map of staffcode → {name, branch_name}
        const hrmUsers = <?= json_encode($hrmUsers, JSON_HEX_TAG) ?>;

        $('#staffcode').on('input change', function () {
            const code = this.value;
            const user = hrmUsers.find(u => u.staffcode === code);
            if (user) {
                $('#informant_name').val(user.name);
                $('#informant_department').val(user.branch_name);
            } else {
                $('#informant_name, #informant_department').val('');
            }
        });

        // initialize DataTable
        $(function () {
            $('#incidentTable').DataTable({
                scrollX: true,
                pageLength: 10,
                order: [[0, 'desc']]
            });
        });
    </script>


</body>

</html>