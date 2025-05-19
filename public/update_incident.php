<?php
session_start();
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.html");
//     exit;
// }
include(__DIR__ . '/db_config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $incident_ids = $_POST['incident_ids'] ?? [];
    $assigned_staff = $_POST['assigned_staff'] ?? [];
    $statuses = $_POST['status'] ?? [];

    foreach ($incident_ids as $id) {
        if (isset($statuses[$id])) {
            $stmt = $pdo->prepare("UPDATE incidents SET assigned_staff = ?, status = ? WHERE id = ?");
            $stmt->execute([$assigned_staff[$id] ?? '', $statuses[$id], $id]);
        }
    }
}
header("Location: incident_list.php");
exit;
?>