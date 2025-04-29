<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/models/SessionManager.php';
SessionManager::startSession();

if (!SessionManager::isLoggedIn()) {
    header("Location: /lingoloop/view/login.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$videoId = $_GET['video_id'] ?? null;
$_SESSION['video_id'] = $videoId;
if (!$videoId) {
    echo "<h2>❌ No video found.</h2>";
    exit();
}

$videoUrl = "https://www.youtube.com/watch?v=$videoId";
$pythonPath = "python";
$scriptPath = "C:/xampp/htdocs/LingoLoop/models/youtube.py";
$command = escapeshellcmd("$pythonPath \"$scriptPath\" $videoUrl");
$output = shell_exec($command);

if (!$output) {
    $output = "[Error: No response from Python script.]";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Show Transcript</title>
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
            position: relative;
        }
        h1 {
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 20px;
            user-select: none;
        }
        .char-counter {
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.1rem;
            text-align: center;
            margin-bottom: 20px;
            color: #00ff00;
            position: sticky;
            top: 0;
            background-color: #121212;
            padding: 15px 10px;
            z-index: 20;
            border-bottom: 1px solid #444;
            min-height: 50px;
            user-select: none;
        }
        .nav-button {
            position: absolute;
            top: 10px;
            background-color: #333;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
        }
        #prev-btn {
            left: 10px;
        }
        #next-btn {
            right: 10px;
        }
        .nav-button:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        #translation-display {
            text-align: center;
            flex: 1;
        }
        pre {
            background-color: #1e1e1e;
            padding: 30px;
            border-radius: 8px;
            white-space: pre-wrap;
            font-size: 1.2rem;
            overflow-x: auto;
            min-height: 70vh;
        }
        #translate-btn {
            position: absolute;
            display: none;
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.2rem;
            z-index: 10;
        }
        #translate-btn:hover {
            background-color: #0056b3;
        }
        .next-link {
            display: block;
            text-align: center;
            margin-top: 30px;
        }
        .next-link a {
            background-color: #28a745;
            color: white;
            padding: 12px 24px;
            font-size: 1.2rem;
            text-decoration: none;
            border-radius: 8px;
        }
        .next-link a:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<h1>📝 Transcript</h1>

<div class="char-counter" id="char-counter">
    <button id="prev-btn" class="nav-button" disabled>⬅️</button>
    <span id="translation-display">🔁 Select text and click Translate</span>
    <button id="next-btn" class="nav-button" disabled>➡️</button>
</div>

<pre id="transcript"><?= htmlspecialchars($output) ?></pre>

<button id="translate-btn">Translate</button>

<div class="next-link">
    <a href="translations_list.php">➡️ View All Saved Translations</a>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const transcript = document.getElementById("transcript");
    const translateBtn = document.getElementById("translate-btn");
    const translationDisplay = document.getElementById("translation-display");
    const prevBtn = document.getElementById("prev-btn");
    const nextBtn = document.getElementById("next-btn");
    const maxChars = 100;

    let translations = [];
    let currentIndex = -1;

    function updateDisplay() {
        if (translations.length === 0) {
            translationDisplay.textContent = "🔁 Select text and click Translate";
        } else {
            translationDisplay.textContent = translations[currentIndex];
        }

        prevBtn.disabled = currentIndex <= 0;
        nextBtn.disabled = currentIndex >= translations.length - 1;
    }

    document.addEventListener("selectionchange", function () {
        const selection = window.getSelection();
        const selectedText = selection.toString().trim();

        if (!selectedText) {
            translateBtn.style.display = "none";
            return;
        }

        if (selectedText.length > maxChars) {
            alert(`⚠️ You can select a maximum of ${maxChars} characters.`);
            selection.removeAllRanges();
            translateBtn.style.display = "none";
        } else {
            const range = selection.getRangeAt(0);
            const rect = range.getBoundingClientRect();
            translateBtn.style.left = `${rect.left + window.scrollX}px`;
            translateBtn.style.top = `${rect.bottom + window.scrollY + 5}px`;
            translateBtn.style.display = "block";
        }
    });

    translateBtn.addEventListener("click", function () {
        const selectedText = window.getSelection().toString().trim();
        if (!selectedText) {
            alert("Please select text to translate first.");
            return;
        }

        fetch("translate.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: "text=" + encodeURIComponent(selectedText)
        })
        .then(response => response.json())
        .then(data => {
            if (data.translation) {
                const result = data.translation;
                translations.push(result);
                currentIndex = translations.length - 1;
                updateDisplay();
            } else {
                alert("❌ Translation failed.\n" + (data.error || "Unknown error."));
                console.log(data);
            }
        })
        .catch(err => {
            alert("Error contacting backend.");
            console.error(err);
        });

        translateBtn.style.display = "none";
        window.getSelection().removeAllRanges();
    });

    prevBtn.addEventListener("click", () => {
        if (currentIndex > 0) {
            currentIndex--;
            updateDisplay();
        }
    });

    nextBtn.addEventListener("click", () => {
        if (currentIndex < translations.length - 1) {
            currentIndex++;
            updateDisplay();
        }
    });
});
</script>

</body>
</html>
