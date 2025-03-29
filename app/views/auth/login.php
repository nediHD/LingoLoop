<?php
require_once __DIR__ . '/../../../core/SessionManager.php';

if (SessionManager::isLoggedIn()) {
    header("Location: /app/views/dashboard/welcome.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | LingoLoop</title>
</head>
<body>
    <h2>Login to LingoLoop</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    
    <form method="POST" action="/public/index.php">
        <input type="hidden" name="action" value="login">
        
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="/app/views/auth/register.php">Register here</a></p>
</body>
</html>
