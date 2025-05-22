<?php
// PHP backend logic for session and video loading

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
$db = Database::getInstance();
$userId = $_SESSION['user_id'];
$videoId = $_GET['video_id'] ?? null;
$stmt = $db->prepare("INSERT IGNORE INTO watched_videos (user_id, video_id) VALUES (?, ?)");
$stmt->bind_param("is", $userId, $videoId);
$stmt->execute();

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
    }
    h1 {
      font-size: 2.5rem;
      text-align: center;
      margin-bottom: 20px;
    }
    .char-counter {
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 1.1rem;
      color: #00ff00;
      margin-bottom: 20px;
      border-bottom: 1px solid #444;
      padding: 10px;
      position: sticky;
      top: 0;
      background: #121212;
      z-index: 10;
    }
    .nav-button {
      background-color: #333;
      color: white;
      border: none;
      padding: 8px 14px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 1rem;
    }
    .nav-button:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }
    #translation-display {
      flex: 1;
      text-align: center;
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
    mark.translated {
      background-color: #1d7f1d;
      color: #fff;
      padding: 2px 4px;
      border-radius: 4px;
      cursor: pointer;
    }
    mark.translated.inner {
      background-color: #2bc72b;
      outline: 2px solid #00ff00;
    }
    .next-link {
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
<div class="char-counter">
  <button id="prev-btn" class="nav-button" disabled>⬅️</button>
  <span id="translation-display">🔁 Select text and click Translate</span>
  <button id="next-btn" class="nav-button" disabled>➡️</button>
</div>
<pre id="transcript"><?= htmlspecialchars($output) ?></pre>
<button id="translate-btn">Translate</button>
<div class="next-link">
  <a href="translations_list.php">✅ View All Saved Translations</a>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const transcript = document.getElementById("transcript");
  const translateBtn = document.getElementById("translate-btn");
  const translationDisplay = document.getElementById("translation-display");
  const prevBtn = document.getElementById("prev-btn");
  const nextBtn = document.getElementById("next-btn");

  let translations = [];
  let currentIndex = -1;
  let selectedText = "";

  function updateDisplay() {
    if (currentIndex >= 0 && currentIndex < translations.length) {
      translationDisplay.textContent = translations[currentIndex].translation;
    } else {
      translationDisplay.textContent = "🔁 Select text and click Translate";
    }
    prevBtn.disabled = currentIndex <= 0;
    nextBtn.disabled = currentIndex >= translations.length - 1;
  }

  document.addEventListener("selectionchange", () => {
    const selection = window.getSelection();
    const text = selection.toString().trim();
    if (!text || !selection.rangeCount || !transcript.contains(selection.anchorNode)) {
      translateBtn.style.display = "none";
      return;
    }
    selectedText = text;
    const range = selection.getRangeAt(0);
    const rect = range.getBoundingClientRect();
    translateBtn.style.left = `${rect.left + window.scrollX}px`;
    translateBtn.style.top = `${rect.bottom + window.scrollY + 5}px`;
    translateBtn.style.display = "block";
  });

  translateBtn.addEventListener("click", () => {
    if (!selectedText) return;
    fetch("translate.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "text=" + encodeURIComponent(selectedText)
    })
    .then(res => res.json())
    .then(data => {
      const translation = data.translation || selectedText;
      const index = translations.length;
      translations.push({ text: selectedText, translation });
      currentIndex = index;
      updateDisplay();

      const selection = window.getSelection();
      if (selection.rangeCount > 0) {
        const range = selection.getRangeAt(0);
        const mark = document.createElement("mark");
        mark.className = "translated";
        mark.dataset.index = index;
        mark.textContent = selectedText;

        const parentMark = range.commonAncestorContainer.parentElement.closest?.("mark.translated") || null;
        if (parentMark) mark.classList.add("inner");

        range.deleteContents();
        range.insertNode(mark);
      }
      translateBtn.style.display = "none";
      window.getSelection().removeAllRanges();
    })
    .catch(err => {
      alert("Translation failed.");
      console.error(err);
    });
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

  transcript.addEventListener("click", e => {
    if (e.target.matches("mark.translated")) {
      const index = parseInt(e.target.dataset.index);
      if (!isNaN(index)) {
        currentIndex = index;
        updateDisplay();
      }
    }
  });
});
</script>

</body>
</html>
