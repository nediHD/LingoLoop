<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/models/SessionManager.php';

SessionManager::startSession();

if (!SessionManager::isLoggedIn()) {
    header("Location: /lingoloop/view/login.php");
    exit();
}

// Obrada forme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['video_id']) && trim($_POST['video_id']) !== '') {
    $_SESSION['video_id'] = $_POST['video_id'];
    header("Location: select_video.php");
    exit();
}

$userId = $_SESSION['user_id'] ?? 1;
$pythonPath = "python";
$scriptPath = "C:/xampp/htdocs/LingoLoop/models/youtube.py";
$command = escapeshellcmd("$pythonPath \"$scriptPath\" $userId");

// Pokretanje Python skripte
$output = shell_exec($command);
$videoTitles = json_decode($output, true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Choisir un sujet</title>
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: 'Segoe UI', sans-serif;
            text-align: center;
            padding: 60px 20px;
            font-size: 1.6rem;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 40px;
        }

        .title-box {
            margin: 20px auto;
            padding: 20px;
            background-color: #1e1e1e;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
            max-width: 600px;
        }

        .video-title {
            margin: 10px 0;
            font-size: 1.2rem;
            background-color: #2a2a2a;
            padding: 10px;
            border-radius: 6px;
            word-wrap: break-word;
        }

        input[type="text"] {
            padding: 10px;
            font-size: 1.1rem;
            width: 80%;
            border-radius: 6px;
            border: none;
            background-color: #f8f9fa;
            color: #000;
        }

        .go-next {
            background-color: #28a745;
            color: white;
            padding: 18px 36px;
            font-size: 1.5rem;
            font-weight: bold;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 40px;
            transition: background-color 0.3s ease;
        }

        .go-next:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<h1>🧠 Unesite ID videa</h1>

<form id="videoForm" method="post">
    <div class="title-box">
        <div style="margin-bottom: 20px;">🎬 Predloženi naslovi videa:</div>
        <?php
        if (is_array($videoTitles)) {
            for ($i = 0; $i < min(3, count($videoTitles)); $i++) {
                echo '<div class="video-title">' . htmlspecialchars($videoTitles[$i]) . '</div>';
            }
        } else {
            echo "<p>⚠️ Nema dostupnih naslova.</p>";
        }
        ?>
    </div>

    <div class="title-box">
        <label for="custom_id">
            👉 Ručno unesite YouTube ID videa:
        </label><br><br>
        <input type="text" id="custom_id" name="video_id" placeholder="npr. dQw4w9WgXcQ">
    </div>

    <button type="submit" class="go-next">➡️ Idi na sljedeću sekciju</button>
</form>

<script>
    document.getElementById('videoForm').addEventListener('submit', function(e) {
        const input = document.getElementById('custom_id');
        if (input.value.trim() === '') {
            e.preventDefault();
            alert('Molimo unesite ID videa pre nego što nastavite.');
        }
    });
</script>

</body>
</html>
