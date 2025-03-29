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
    <title>Register | LingoLoop</title>
</head>
<body>
    <h2>Create an Account</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST" action="/public/index.php">
        <input type="hidden" name="action" value="register">

        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="/lingoloop/public/?action=login">Login here</a>
</body>
</html>
