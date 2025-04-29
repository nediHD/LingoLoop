<?php
// select_video.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$videoId = $_GET['video_id'] ?? null;

if (!$videoId) {
    echo "<h2>❌ Kein Video ausgewählt. Bitte gehe zurück und wähle ein Video.</h2>";
    exit();
}

$videoUrl = "https://www.youtube.com/watch?v=$videoId";
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Was willst du tun?</title>
    <style>
        body {
            background-color: #121212;
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 30px;
        }
        .button-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 90%;
            max-width: 400px;
        }
        a.button {
            background-color: #007bff;
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        a.button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<h1>Was willst du zuerst machen?</h1>
<div class="button-container">
    <a class="button" href="/lingoloop/view/show_transcript.php?video_id=<?= urlencode($videoId) ?>">📄 Transkript anzeigen</a>
    <a class="button" href="/lingoloop/view/watch_video_embed.php?video_id=<?= urlencode($videoId) ?>">▶️ Direkt zum Video</a>
    </div>

</body>
</html>
