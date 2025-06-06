<?php
// dashboard.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Bangkok');

include __DIR__ . '/db_config.php';
$hrm = getDb('hrm');
$itd = getDb('itd');

// Counts
$totalUsers = $hrm->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalHardware = $itd->query("SELECT COUNT(*) FROM hardware")->fetchColumn();
$totalSoftware = $itd->query("SELECT COUNT(*) FROM software_licenses")->fetchColumn();
$totalIncidents = $itd->query("SELECT COUNT(*) FROM incidents")->fetchColumn();

// Incident status counts
$statusCounts = $itd->query("SELECT status, COUNT(*) as count FROM incidents GROUP BY status")
  ->fetchAll(PDO::FETCH_KEY_PAIR);

$openCount = $statusCounts['Open'] ?? 0;
$inProgressCount = $statusCounts['In Progress'] ?? 0;
$closedCount = $statusCounts['Closed'] ?? 0;

$openPercent = $totalIncidents > 0 ? ($openCount / $totalIncidents) * 100 : 0;
$inProgressPercent = $totalIncidents > 0 ? ($inProgressCount / $totalIncidents) * 100 : 0;
$closedPercent = $totalIncidents > 0 ? ($closedCount / $totalIncidents) * 100 : 0;

$assetPercent = ($totalHardware + $totalSoftware) > 0 ? ($totalHardware / ($totalHardware + $totalSoftware)) * 100 : 0;
$licensePercent = ($totalHardware + $totalSoftware) > 0 ? ($totalSoftware / ($totalHardware + $totalSoftware)) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" />

  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: Arial, sans-serif;
      overflow-x: hidden;
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

    .summary-boxes {
      display: flex;
      gap: 20px;
      margin-bottom: 30px;
      flex-wrap: wrap;
      justify-content: center;
    }

    .summary-box {
      flex: 1;
      min-width: 200px;
      max-width: 250px;
      padding: 20px;
      border-radius: 10px;
      color: #fff;
      position: relative;
    }

    .summary-box .icon {
      position: absolute;
      top: 10px;
      right: 10px;
      background: #fff;
      border-radius: 50%;
      padding: 8px;
      color: #333;
    }

    .summary-box h3 {
      margin: 0;
      font-size: 18px;
    }

    .summary-box p {
      font-size: 26px;
      margin: 5px 0 0;
    }

    .incident-charts-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 60px;
      padding-bottom: 60px;
    }

    .chart-container {
      width: 100%;
      max-width: 600px;
      margin: 10px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      border: 1px solid #ccc;
      background: #f9f9f9;
      padding: 20px;
      box-sizing: border-box;
    }

    .chart-title {
      text-align: center;
      font-weight: bold;
      margin-bottom: 20px;
    }

    .chart-container canvas {
      width: 100% !important;
      max-width: 100%;
      height: auto !important;
      margin-bottom: 30px;
    }

    @media (max-width: 768px) {
      .summary-boxes {
        flex-direction: column;
        align-items: center;
      }
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
  <div class="container" id="main">
    <h2 class="center">Dashboard Overview</h2>
    <div class="summary-boxes">
      <div class="summary-box" style="background:#42a5f5">
        <div class="icon"><i class="fas fa-users"></i></div>
        <h3>Users</h3>
        <p><?= $totalUsers ?></p>
      </div>
      <div class="summary-box" style="background:#26c6da">
        <div class="icon"><i class="fas fa-desktop"></i></div>
        <h3>Assets</h3>
        <p><?= $totalHardware ?></p>
      </div>
      <div class="summary-box" style="background:#ffb300">
        <div class="icon"><i class="fas fa-key"></i></div>
        <h3>Licenses</h3>
        <p><?= $totalSoftware ?></p>
      </div>
      <div class="summary-box" style="background:#66bb6a">
        <div class="icon"><i class="fas fa-bug"></i></div>
        <h3>Incidents</h3>
        <p><?= $totalIncidents ?></p>
      </div>
    </div>
    <div class="incident-charts-container">
      <div class="chart-container">
        <div class="chart-title">Asset vs License</div>
        <canvas id="assetLicenseChart"></canvas>
        <canvas id="assetLicenseBar"></canvas>
      </div>
      <div class="chart-container">
        <div class="chart-title">Incident Status Distribution</div>
        <canvas id="incidentStatusChart"></canvas>
        <canvas id="incidentBarChart"></canvas>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <script>
    new Chart(document.getElementById('assetLicenseChart'), {
      type: 'doughnut',
      data: {
        labels: ['Hardware', 'Software Licenses'],
        datasets: [{
          data: [<?= $totalHardware ?>, <?= $totalSoftware ?>],
          backgroundColor: ['#36a2eb', '#4bc0c0'],
          borderWidth: 1
        }]
      },
      options: {
        plugins: {
          legend: { position: 'bottom' },
          tooltip: { enabled: true }
        }
      }
    });

    new Chart(document.getElementById('assetLicenseBar'), {
      type: 'bar',
      data: {
        labels: ['Hardware', 'Software Licenses'],
        datasets: [{
          label: 'Count',
          data: [<?= $totalHardware ?>, <?= $totalSoftware ?>],
          backgroundColor: ['#36a2eb', '#4bc0c0']
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } }
      }
    });

    new Chart(document.getElementById('incidentStatusChart'), {
      type: 'doughnut',
      data: {
        labels: ['Open', 'In Progress', 'Closed'],
        datasets: [{
          data: [<?= $openCount ?>, <?= $inProgressCount ?>, <?= $closedCount ?>],
          backgroundColor: ['#ff6384', '#ffcd56', '#36a2eb']
        }]
      },
      options: {
        plugins: {
          legend: { position: 'bottom' },
          tooltip: { enabled: true }
        }
      }
    });

    new Chart(document.getElementById('incidentBarChart'), {
      type: 'bar',
      data: {
        labels: ['Open', 'In Progress', 'Closed'],
        datasets: [{
          label: 'Incidents',
          data: [<?= $openCount ?>, <?= $inProgressCount ?>, <?= $closedCount ?>],
          backgroundColor: ['#ff6384', '#ffcd56', '#36a2eb']
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } }
      }
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