<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/models/SessionManager.php';
require_once BASE_PATH . '/models/Database.php';

SessionManager::startSession();

if (!SessionManager::isLoggedIn()) {
    header("Location: /lingoloop/view/login.php");
    exit();
}
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Video ID koji dolazi iz URL-a
$db = Database::getInstance();
$userId = $_SESSION['user_id'];
$videoId = $_GET['video_id'] ?? null;
$stmt = $db->prepare("INSERT IGNORE INTO watched_videos (user_id, video_id) VALUES (?, ?)");
$stmt->bind_param("is", $userId, $videoId);
$stmt->execute();


// Ako nema video ID-a, prikaži poruku i prekini
if (!$videoId) {
    echo "<h2>❌ No video selected.</h2>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Watch Video</title>
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }
        h1 {
            margin-top: 40px;
            font-size: 2rem;
        }
        .video-wrapper {
            margin-top: 20px;
            width: 90%;
            max-width: 900px;
            aspect-ratio: 16 / 9;
            background-color: #000;
            box-shadow: 0 0 15px rgba(255,255,255,0.2);
            border-radius: 12px;
            overflow: hidden;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .back-button {
            margin-top: auto;
            margin-bottom: 40px;
            background-color: #007bff;
            color: white;
            padding: 15px 30px;
            font-size: 1.2rem;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <h1>🎬 Enjoy the Video</h1>

    <div class="video-wrapper">
        <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($videoId) ?>" allowfullscreen></iframe>
    </div>

    <a class="back-button" href="/lingoloop/view/dashboard.php">🏠 Back to Dashboard</a>

</body>
</html>
