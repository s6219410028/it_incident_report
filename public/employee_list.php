<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.html");
//     exit;
// }
include __DIR__ . '/db_config.php';
$db = getDb('hrm');
try {

    $sqlUsers = "
    SELECT 
        u.id,
        u.staffcode,
        CASE
          WHEN u.prefix_id = 1 THEN 'ไม่ระบุ'
          WHEN u.prefix_id = 2 THEN 'นาย'
          WHEN u.prefix_id = 3 THEN 'นาง'
          WHEN u.prefix_id = 4 THEN 'นางสาว'
          ELSE 'ไม่ระบุ'
        END AS prefix,
        CONCAT(u.firstname, ' ', u.lastname) AS name,
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
        END AS branch_name,
		CASE
			WHEN b.department_id = 1 THEN 'บริษัท ที.แมน ฟาร์มา จำกัด'
			WHEN b.department_id = 2 THEN 'บริษัท ที.แมน ฟาร์มาซูติคอล จำกัด'					
            WHEN b.department_id = 3 THEN 'บริษัท เฮเว่น เฮิร์บ จำกัด'
			WHEN b.department_id = 4 THEN 'บริษัท ทีเอ็มทีโปรสปอร์ต จำกัด'
			WHEN b.department_id = 5 THEN 'บริษัท ทีเอ็มแซต จำกัด'
		ELSE 'Other'
		END AS department_name,
        u.position AS designation,
        u.date_serve,
        CASE
          WHEN u.status = 1 THEN 'Active'
          ELSE 'Inactive'
        END AS status
      FROM users u
      LEFT JOIN branchs b
        ON u.branch_id = b.id
";

    $stmtUsers = $db->prepare($sqlUsers);
    $stmtUsers->execute();
    $users = $stmtUsers->fetchAll();

    // 2) (optional) if you still need the raw branches table:
    $stmtBranches = $db->query("SELECT * FROM branchs");
    $branchs = $stmtBranches->fetchAll();

    // header('Content-Type: application/json; charset=utf-8');
    // echo json_encode([
    //     'users' => $users,
    //     'branchs' => $branchs
    // ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    // something went wrong with the connection or queries
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Employee List</title>
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

        /* Button to toggle form */
        .toggle-btn {
            padding: 10px 20px;
            margin-bottom: 20px;
            margin-left: 40%;
            background-color: #007BFF;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        .toggle-btn:hover {
            background-color: #0056b3;
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

        .table-card {
            background: #fff;
            padding: 0px;
            border-radius: 0px;
            font-size: 0.8em;
        }

        #tableWrapper {
            overflow-x: auto;
            position: relative;
        }

        /* never break lines in table cells */
        #employeeTable th,
        #employeeTable td {
            white-space: nowrap;
        }

        #tableWrapper .dataTables_filter {
            float: right;
            margin: 0 0 10px;
            /* push it a little above the table */
        }
    </style>
    <!-- jQuery and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
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
    <!-- Main container -->
    <div class="container-fluid" id="container">
        <div class="main-content">
            <h1>Employee List</h1>
            <div class="table-card">
                <!-- Employee Table -->
                <div class="table-responsive" id="tableWrapper">
                    <table id="employeeTable" class="display nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Staff Code</th>
                                <th>คำนำหน้า</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Company</th>
                                <th>Position</th>
                                <th>Date serve</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $employee): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($employee['id']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['staffcode']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['prefix']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['branch_name']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['department_name']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['designation']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['date_serve']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            var table = $('#employeeTable').DataTable({
                scrollX: true,
                pageLength: 10,
                order: [[0, 'asc']]
            });

        });


        // Sidebar toggle with persistent state
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const container = document.getElementById('container');
            const isExpanded = localStorage.getItem('sidebarExpanded') === 'true';
            if (isExpanded) {
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