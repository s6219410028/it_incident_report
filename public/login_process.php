<?php
// login_process.php
session_start();
include(__DIR__ . '/db_config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare the query using a prepared statement
    $stmt = $pdo->prepare("SELECT id, user_name, user_password FROM users WHERE user_name = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Plain-text comparison (not secure) – change to password_verify() if using hashed passwords.
    if ($user && $password == $user['user_password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['user_name'];
        header("Location: dashboard.php?staffId=" . $_SESSION['user_id']);
        exit;
    } else {
        echo "Invalid username or password.";
    }
}
?>