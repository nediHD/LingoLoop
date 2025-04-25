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

$userId = $_SESSION['user_id'] ?? 1;
$pythonPath = "python";
$scriptPath = "C:/xampp/htdocs/LingoLoop/models/youtube.py";
$command = escapeshellcmd("$pythonPath \"$scriptPath\" $userId");
$output = shell_exec($command);

$videoList = json_decode($output, true);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Wähle ein Video</title>
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 30px 15px;
        }

        .header h1 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        .videos {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        @media (min-width: 768px) {
            .videos {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .card {
            background: #1e1e1e;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .card img {
            width: 100%;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .card h3 {
            margin: 0 0 10px;
            font-size: 1.1rem;
            text-align: center;
        }

        .card p {
            margin: 0 0 10px;
            color: #ccc;
            font-size: 0.95rem;
            text-align: center;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            margin-top: 10px;
        }

        .duration {
            font-size: 1.1rem;
            font-weight: bold;
            color: #ffc107;
        }

        .select-radio {
            transform: scale(1.4);
            cursor: pointer;
        }

        .actions {
            margin-top: 40px;
            text-align: center;
        }

        .next-button, .dashboard-button {
            margin: 10px;
            padding: 15px 30px;
            font-size: 1.2rem;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .next-button {
            background-color: #007bff;
            color: white;
        }

        .next-button:disabled {
            background-color: #555;
            cursor: not-allowed;
        }

        .dashboard-button {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
        }

        .next-button:hover:enabled {
            background-color: #0056b3;
        }

        .dashboard-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

<h1 class="header">🎬 Wähle ein Video</h1>

<form action="select_video.php" method="get" id="videoForm">
    <div class="videos">
        <?php
        if (is_array($videoList)) {
            foreach ($videoList as $index => $video) {
                [$title, $description, $duration, $url] = $video;
                parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);
                $videoId = $queryParams['v'] ?? '';
                $thumbnailUrl = "https://img.youtube.com/vi/$videoId/hqdefault.jpg";

                echo "
                <label class='card'>
                    <img src='$thumbnailUrl' alt='Thumbnail'>
                    <h3>" . htmlspecialchars($title) . "</h3>
                    <p>" . htmlspecialchars($description) . "</p>
                    <div class='card-footer'>
                        <span class='duration'>⏱️ $duration</span>
                        <input type='radio' name='video_id' value='$videoId' class='select-radio' required>
                    </div>
                </label>
                ";
            }
        } else {
            echo "<p>⚠️ Keine Videos gefunden.</p>";
        }
        ?>
    </div>

    <div class="actions">
        <button type="submit" class="next-button" id="nextBtn" disabled>Weiter →</button>
        <a href="/lingoloop/view/dashboard.php" class="dashboard-button">🔙 Zurück zum Dashboard</a>
    </div>
</form>

<script>
    const radios = document.querySelectorAll('input[name="video_id"]');
    const nextBtn = document.getElementById('nextBtn');

    radios.forEach(radio => {
        radio.addEventListener('change', () => {
            nextBtn.disabled = false;
        });
    });
</script>

</body>
</html>
