<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/models/SessionManager.php';


// Check if user is logged in
SessionManager::start();
if (!SessionManager::get('user_id')) {
    header("Location: /index.php");
    exit();
}

$username = SessionManager::get('username');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LingoLoop | Dashboard</title>
</head>
<body>
    <h1>Welcome to LingoLoop, <?= htmlspecialchars($username) ?>!</h1>

    <p>This is your user dashboard.</p>

    <p><a href="/logout.php">Logout</a></p>
</body>
</html>
