<?php
// show_transcript.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$videoId = $_GET['video_id'] ?? null;

if (!$videoId) {
    echo "<h2>❌ Kein Video gefunden.</h2>";
    exit();
}

$videoUrl = "https://www.youtube.com/watch?v=$videoId";
$pythonPath = "python";
$scriptPath = "C:/xampp/htdocs/LingoLoop/models/youtube.py";
$command = escapeshellcmd("$pythonPath \"$scriptPath\" $videoUrl");
$output = shell_exec($command);

if (!$output) {
    $output = "[Error: Keine Antwort vom Python Script erhalten.]";
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Transkript anzeigen</title>
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
        }
        h1 {
            font-size: 2rem;
            text-align: center;
        }
        pre {
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 8px;
            white-space: pre-wrap;
            font-size: 1rem;
            overflow-x: auto;
        }
        .video-button {
            display: block;
            margin: 30px auto;
            background-color: #28a745;
            color: white;
            padding: 15px 30px;
            text-align: center;
            text-decoration: none;
            font-size: 1.2rem;
            border-radius: 8px;
            width: max-content;
        }
    </style>
</head>
<body>

<h1>📝 Transkript</h1>
<pre><?= htmlspecialchars($output) ?></pre>

<a class="video-button" href="watch_video_embed.php?video_id=<?= urlencode($videoId) ?>">▶️ Jetzt Video ansehen</a>

</body>
</html>