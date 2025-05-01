<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/models/SessionManager.php';

SessionManager::startSession();

if (!SessionManager::isLoggedIn()) {
    header("Location: /lingoloop/view/login.php");
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LingoLoop | Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .btn-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 100%;
            max-width: 300px;
        }

        .btn {
            background-color: #1e1e1e;
            color: #fff;
            padding: 15px;
            font-size: 1.2rem;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-align: center;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #007bff;
        }

        a.logout {
            margin-top: 30px;
            color: #aaa;
            text-decoration: none;
            font-size: 1rem;
        }

        a.logout:hover {
            color: #fff;
        }
    </style>
</head>
<body>

<h1>Welcome back, <?= htmlspecialchars($username) ?> 👋</h1>

<div class="btn-container">
    <a href="/lingoloop/view/vocabulary_dashboard.php" class="btn">📚 Vocabulary</a>
    <a href="/lingoloop/view/watch_video.php" class="btn">🎬 Watch Videos</a>
    <a href="/lingoloop/view/ichat.php" class="btn">💬 iChatting</a>
</div>

<a href="/lingoloop/controller/LogoutController.php" class="logout">Log out</a>

</body>
</html>
