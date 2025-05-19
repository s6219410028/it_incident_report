<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

include __DIR__ . '/db_config.php';
$db = getDb('itd');


$editItem = null;
if (isset($_GET['edit_id'])) {
    $stmt = $db->prepare("SELECT * FROM hardware WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $editItem = $stmt->fetch();
}


// Process insertion if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $description = trim($_POST['description']);
    $userName = trim($_POST['user_name']);

    if (!empty($_POST['edit_id'])) {
        // → update existing
        $stmt = $db->prepare("
          UPDATE hardware
             SET name        = ?,
                 type        = ?,
                 description = ?,
                 user_name   = ?
           WHERE id = ?
        ");
        $stmt->execute([$name, $type, $description, $userName, $_POST['edit_id']]);
    } else {
        // → new insert
        $stmt = $db->prepare("
          INSERT INTO hardware (name, type, description, user_name)
          VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$name, $type, $description, $userName]);
    }
    header('Location: hardware_list.php');
    exit;
}

$stmt = $db->query("
  SELECT id, name, type, description, user_name
  FROM hardware
  ORDER BY id ASC
");
$hardwareItems = $db
    ->query("SELECT id, name, type, description, user_name FROM hardware ORDER BY id")
    ->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Computer & Hardware List</title>
    <!-- <link rel="stylesheet" href="style.css"> -->
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

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
    </style>
    <!-- jQuery and DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
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
        <div class="main-content">
            <h1>Hardware List</h1>

            <!-- Add-Hardware Form -->
            <form class="hardware-form" method="post" action="">
                <h3><?= $editItem ? 'Edit Hardware #' . $editItem['id'] : 'Add New Hardware' ?></h3>

                <!-- if editing, carry the ID -->
                <?php if ($editItem): ?>
                    <input type="hidden" name="edit_id" value="<?= $editItem['id'] ?>">
                <?php endif; ?>

                <input name="name" placeholder="Hardware Name" required
                    value="<?= htmlspecialchars($editItem['name'] ?? '') ?>">
                <input name="type" placeholder="Hardware Type" required
                    value="<?= htmlspecialchars($editItem['type'] ?? '') ?>">
                <textarea name="description" placeholder="Description" rows="2"
                    required><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea>

                <input name="user_name" placeholder="Assigned User Name" required
                    value="<?= htmlspecialchars($editItem['user_name'] ?? '') ?>">

                <button type="submit">
                    <?= $editItem ? 'Update Hardware' : 'Add Hardware' ?>
                </button>

                <?php if ($editItem): ?>
                    <a href="hardware_list.php" style="margin-left:10px">Cancel</a>
                <?php endif; ?>
            </form>


            <!-- Hardware Table -->
            <div class="table-wrapper">
                <table id="hardwareTable" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Assigned User</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hardwareItems as $h): ?>
                            <tr>
                                <td><?= htmlspecialchars($h['id']) ?></td>
                                <td><?= htmlspecialchars($h['name']) ?></td>
                                <td><?= htmlspecialchars($h['type']) ?></td>
                                <td><?= htmlspecialchars($h['description']) ?></td>
                                <td><?= htmlspecialchars($h['user_name']) ?></td>
                                <td><a href="?edit_id=<?= $h['id'] ?>">Edit</a></td>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(function () {
            $('#hardwareTable').DataTable({
                scrollX: true,
                pageLength: 10,
                order: [[0, 'asc']]
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