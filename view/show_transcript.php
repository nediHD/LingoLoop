<?php
// show_transcript.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$videoId = $_GET['video_id'] ?? null;

if (!$videoId) {
    echo "<h2>❌ No video selected.</h2>";
    exit();
}

$videoUrl = "https://www.youtube.com/watch?v=$videoId";
$pythonPath = "python";
$scriptPath = "C:/xampp/htdocs/LingoLoop/models/youtube.py";
$command = escapeshellcmd("$pythonPath \"$scriptPath\" \"$videoUrl\"");
$output = shell_exec($command);

if (!$output) {
    $output = "[Error: No response from Python script.]";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transcript</title>
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
        }
        h1 {
            font-size: 2.2rem;
            text-align: center;
            margin-bottom: 20px;
        }
        pre {
            background-color: #1e1e1e;
            padding: 30px;
            border-radius: 10px;
            white-space: pre-wrap;
            font-size: 1.2rem; /* <-- povećan font */
            overflow-x: auto;
            width: 100%; /* <-- full width */
            max-width: none; /* <-- bez ograničenja širine */
            box-sizing: border-box;
        }
        .video-button {
            display: block;
            margin: 30px auto;
            background-color: #28a745;
            color: white;
            padding: 15px 30px;
            font-size: 1.2rem;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            width: max-content;
            transition: background-color 0.3s;
        }
        .video-button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<h1>📝 Transcript</h1>

<pre><?= htmlspecialchars($output) ?></pre>

<a class="video-button" href="watch_video_embed.php?video_id=<?= urlencode($videoId) ?>">▶️ Watch Video Now</a>

</body>
</html>
