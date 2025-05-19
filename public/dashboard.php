<?php
session_start();
// if (!isset($_SESSION['user_id'])) {
//   header("Location: login.html");
//   exit;
// }
include(__DIR__ . '/db_config.php');

// ----- INCIDENT DATA -----
// Query to get the count of all incidents grouped by severity
$stmt = $pdo->query("SELECT severity, COUNT(*) AS count FROM incidents GROUP BY severity ORDER BY severity ASC");
$incidentData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize counts for severities 1 to 5
$severityCounts = [0, 0, 0, 0, 0];
foreach ($incidentData as $row) {
  $severity = (int) $row['severity'];
  if ($severity >= 1 && $severity <= 5) {
    $severityCounts[$severity - 1] = (int) $row['count'];
  }
}
$severityLabels = ['Severity 1', 'Severity 2', 'Severity 3', 'Severity 4', 'Severity 5'];
$totalIncidents = array_sum($severityCounts);

// ----- HARDWARE DATA -----
// Query to get the count of hardware items grouped by type
$stmtHardware = $pdo->query("SELECT type, COUNT(*) AS count FROM hardware GROUP BY type");
$hardwareData = $stmtHardware->fetchAll(PDO::FETCH_ASSOC);
$hardwareLabels = [];
$hardwareCounts = [];
foreach ($hardwareData as $row) {
  $hardwareLabels[] = $row['type'];
  $hardwareCounts[] = (int) $row['count'];
}
$totalHardware = array_sum($hardwareCounts);

// ----- ASSIGNED INCIDENT DATA -----
// Query to get the count of incidents by assigned staff (treat empty/NULL as "Unassigned")
$stmtAssigned = $pdo->query("SELECT IF(assigned_staff IS NULL OR assigned_staff = '', 'Unassigned', assigned_staff) AS staff, COUNT(*) AS count FROM incidents GROUP BY staff");
$assignedData = $stmtAssigned->fetchAll(PDO::FETCH_ASSOC);
$staffLabels = [];
$staffCounts = [];
foreach ($assignedData as $row) {
  $staffLabels[] = $row['staff'];
  $staffCounts[] = (int) $row['count'];
}
$totalAssigned = array_sum($staffCounts);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>IT Staff Dashboard</title>
  <!-- Include Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Include FontAwesome for icons -->
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
      /* collapsed width */
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

    /* Chart containers */
    .incident-charts-container {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
    }

    .chart-container {
      width: 48%;
      margin-bottom: 20px;
    }

    .chart-container canvas {
      max-width: 100%;
      height: auto;
    }

    /* Hardware chart container */
    .hardware-chart-container {
      width: 48%;
      margin-top: 40px;
    }

    /* Assigned incidents donut chart container */
    .assigned-donut-chart-container {
      width: 40%;
      margin: 40px auto 0;
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
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

  <!-- Main Container -->
  <div class="container" id="container">
    <div class="main-content">
      <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
      <!-- Incident Charts: Bar chart (left) & Donut chart (right) -->
      <div class="incident-charts-container">
        <div class="chart-container">
          <canvas id="incidentBarChart"></canvas>
        </div>
        <div class="chart-container">
          <canvas id="incidentDonutChart"></canvas>
        </div>
      </div>
      <!-- Hardware Chart: Donut chart below incidents -->
      <div class="hardware-chart-container">
        <canvas id="hardwareDonutChart"></canvas>
      </div>
      <!-- Assigned Incidents Donut Chart: New donut chart below Hardware Chart -->
      <div class="assigned-donut-chart-container">
        <canvas id="assignedDonutChart"></canvas>
      </div>
    </div>
  </div>

  <script>
    // Calculate totals
    const totalIncidents = <?php echo $totalIncidents; ?>;
    const totalHardware = <?php echo $totalHardware; ?>;

    // Convert incident bar chart remains unchanged if desired
    const incidentBarCtx = document.getElementById('incidentBarChart').getContext('2d');
    const incidentBarChart = new Chart(incidentBarCtx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($severityLabels); ?>,
        datasets: [{
          label: 'Number of Incidents',
          data: <?php echo json_encode($severityCounts); ?>,
          backgroundColor: 'rgba(54, 162, 235, 0.5)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: { title: { display: true, text: 'Incidents by Severity (Bar Chart)' } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
      }
    });

    // Incident Donut Chart (all incidents)
    const incidentDonutCtx = document.getElementById('incidentDonutChart').getContext('2d');
    const incidentDonutChart = new Chart(incidentDonutCtx, {
      type: 'doughnut',
      data: {
        labels: <?php echo json_encode($severityLabels); ?>,
        datasets: [{
          label: 'Incidents by Severity',
          data: <?php echo json_encode($severityCounts); ?>,
          backgroundColor: [
            'rgba(60, 151, 225, 0.8)',
            'rgba(41, 238, 120, 0.8)',
            'rgba(239, 199, 66, 0.8)',
            'rgba(251, 148, 51, 0.8)',
            'rgba(250, 52, 52, 0.8)'
          ],
          borderColor: [
            'rgba(99, 185, 255, 0.2)',
            'rgba(54, 235, 126, 0.2)',
            'rgba(244, 196, 40, 0.2)',
            'rgba(231, 132, 40, 0.2)',
            'rgba(243, 37, 37, 0.2)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        cutout: '70%',
        plugins: {
          title: { display: true, text: 'Incidents by Severity (Donut Chart)' }
        },
        elements: {
          center: {
            text: totalIncidents.toString(),
            color: "#000",
            fontStyle: "Arial",
            sidePadding: 20
          }
        }
      }
    });

    // Hardware Donut Chart
    const hardwareDonutCtx = document.getElementById('hardwareDonutChart').getContext('2d');
    const hardwareDonutChart = new Chart(hardwareDonutCtx, {
      type: 'doughnut',
      data: {
        labels: <?php echo json_encode($hardwareLabels); ?>,
        datasets: [{
          label: 'Hardware by Type',
          data: <?php echo json_encode($hardwareCounts); ?>,
          backgroundColor: [
            'rgba(239, 64, 44, 0.7)',
            'rgba(242, 166, 13, 0.7)',
            'rgba(66, 235, 54, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(95, 95, 95, 0.7)',
            'rgba(182, 182, 182, 0.7)'
          ],
          borderColor: [
            'rgba(239, 64, 44, 1)',
            'rgba(242, 166, 13, 1)',
            'rgba(66, 235, 54, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(95, 95, 95, 1)',
            'rgba(182, 182, 182, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        cutout: '70%',
        plugins: {
          title: { display: true, text: 'Hardware by Type (Donut Chart)' }
        },
        elements: {
          center: {
            text: totalHardware.toString(),
            color: "#000",
            fontStyle: "Arial",
            sidePadding: 20
          }
        }
      }
    });

    // Assigned Incidents Donut Chart
    const assignedDonutCtx = document.getElementById('assignedDonutChart').getContext('2d');
    const assignedDonutChart = new Chart(assignedDonutCtx, {
      type: 'doughnut',
      data: {
        labels: <?php echo json_encode($staffLabels); ?>,
        datasets: [{
          label: 'Incidents by Assigned Staff',
          data: <?php echo json_encode($staffCounts); ?>,
          backgroundColor: [
            'rgba(255, 99, 132, 0.7)',
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 159, 64, 0.7)',
            'rgba(201, 203, 207, 0.7)'
          ],
          borderColor: [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(201, 203, 207, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        cutout: '70%',
        plugins: {
          title: { display: true, text: 'Incidents by Assigned Staff (Donut Chart)' }
        },
        elements: {
          center: {
            text: totalAssigned.toString(),
            color: "#000",
            fontStyle: "Arial",
            sidePadding: 20
          }
        }
      }
    });

    // Register a custom plugin to draw text in the center of a doughnut chart
    Chart.register({
      id: 'centerText',
      afterDraw: function (chart) {
        if (chart.config.options.elements && chart.config.options.elements.center) {
          const ctx = chart.ctx;
          const centerConfig = chart.config.options.elements.center;
          const fontStyle = centerConfig.fontStyle || 'Arial';
          const txt = centerConfig.text;
          const color = centerConfig.color || '#000';
          const sidePadding = centerConfig.sidePadding || 20;
          const sidePaddingCalculated = (sidePadding / 100) * (chart.innerRadius * 2);
          ctx.font = "30px " + fontStyle;
          const stringWidth = ctx.measureText(txt).width;
          const elementWidth = (chart.innerRadius * 2) - sidePaddingCalculated;
          const widthRatio = elementWidth / stringWidth;
          const newFontSize = Math.floor(30 * widthRatio);
          const elementHeight = (chart.innerRadius * 2);
          const fontSizeToUse = Math.min(newFontSize, elementHeight);
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';
          const centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
          const centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 2);
          ctx.font = fontSizeToUse + "px " + fontStyle;
          ctx.fillStyle = color;
          ctx.fillText(txt, centerX, centerY);
        }
      }
    });

    // Force redraw so the center text appears (for each chart)
    incidentDonutChart.update();
    hardwareDonutChart.update();
    assignedDonutChart.update();

    // Sidebar toggle with persistent state
    document.addEventListener('DOMContentLoaded', function () {
      const sidebar = document.getElementById('sidebar');
      const container = document.getElementById('container');
      const storedState = localStorage.getItem('sidebarExpanded') === 'true';
      if (storedState) {
        sidebar.classList.add('expanded');
        container.classList.add('expanded');
      }
      sidebar.querySelector('h2').addEventListener('click', function () {
        sidebar.classList.toggle('expanded');
        container.classList.toggle('expanded');
        localStorage.setItem('sidebarExpanded', sidebar.classList.contains('expanded'));
      });
    });
  </script>
</body>

</html>