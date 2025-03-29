<?php
require_once __DIR__ . '/../../../core/SessionManager.php';

if (!SessionManager::isLoggedIn()) {
    header("Location: /app/views/auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome | LingoLoop</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars(SessionManager::getUsername()); ?> 👋</h1>

    <p>This is your dashboard.</p>

    <a href="/public/logout.php">Logout</a>
</body>
</html>
