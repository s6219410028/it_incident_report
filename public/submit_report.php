<?php
session_start();
include(__DIR__ . '/db_config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $problem_type = isset($_POST['problem_type']) ? trim($_POST['problem_type']) : '';
    $custom_problem = isset($_POST['custom_problem']) ? trim($_POST['custom_problem']) : '';
    $severity = isset($_POST['severity']) ? trim($_POST['severity']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $informant_name = isset($_POST['informant_name']) ? trim($_POST['informant_name']) : '';
    $informant_department = isset($_POST['informant_department']) ? trim($_POST['informant_department']) : '';

    // If "Other" is selected, use the custom problem value
    if ($problem_type === 'Other' && !empty($custom_problem)) {
        $problem_type = $custom_problem;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO incidents (problem_type, severity, description, employee_name, employee_department) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$problem_type, $severity, $description, $informant_name, $informant_department]);
        $success = true;
    } catch (PDOException $e) {
        $error = $e->getMessage();
        $success = false;
    }
} else {
    // If the page is accessed without a POST request, redirect back to the report form.
    header("Location: report_incident.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Submit Report</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            padding: 20px;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }

        .container h1 {
            margin-top: 0;
        }

        .message {
            font-size: 18px;
            margin: 20px 0;
        }

        button {
            padding: 10px 20px;
            background-color: #333;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }

        button:hover {
            background-color: #555;
        }
    </style>
</head>

<body>

    <div class="container">
        <?php if ($success): ?>
            <h1>Report Submitted Successfully</h1>
            <p class="message">Your incident report has been submitted.</p>
            <button onclick="window.location.href='dashboard.php'">Return to Dashboard</button>
        <?php else: ?>
            <h1>Error Submitting Report</h1>
            <p class="message">There was an error: <?php echo htmlspecialchars($error); ?></p>
            <button onclick="window.location.href='report_incident.php'">Return to Report Form</button>
        <?php endif; ?>
    </div>

</body>

</html>