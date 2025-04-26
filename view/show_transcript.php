<?php
// show_transcript.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$videoId = $_GET['video_id'] ?? null;

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
        }
        .char-counter {
            font-size: 1rem;
            text-align: center;
            margin-bottom: 20px;
            color: #ccc;
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
        #translated-text {
            position: absolute;
            display: none;
            background-color: #333;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 1.1rem;
            margin-top: 10px;
            z-index: 9;
            max-width: 300px;
            color: #00ff00;
        }
    </style>
</head>
<body>

<h1>📝 Transcript</h1>
<div class="char-counter" id="char-counter">Characters left: 100</div>

<pre id="transcript"><?= htmlspecialchars($output) ?></pre>

<button id="translate-btn">Translate</button>
<div id="translated-text"></div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const transcript = document.getElementById("transcript");
    const translateBtn = document.getElementById("translate-btn");
    const translatedTextDiv = document.getElementById("translated-text");
    const charCounter = document.getElementById("char-counter");
    const maxChars = 100;

    document.addEventListener("selectionchange", function () {
        const selection = window.getSelection();
        const selectedText = selection.toString().trim();

        // Update character counter
        let charsLeft = maxChars - selectedText.length;
        charCounter.textContent = "Characters left: " + (charsLeft >= 0 ? charsLeft : 0);

        if (!selectedText) {
            translateBtn.style.display = "none";
            translatedTextDiv.style.display = "none";
            return;
        }

        if (selectedText.length > maxChars) {
            alert(`⚠️ You can select a maximum of ${maxChars} characters.`);
            selection.removeAllRanges(); // reset selection
            translateBtn.style.display = "none";
            translatedTextDiv.style.display = "none";
            charCounter.textContent = "Characters left: " + maxChars;
        } else {
            const range = selection.getRangeAt(0);
            const rect = range.getBoundingClientRect();

            // Show translate button near the selection
            translateBtn.style.left = `${rect.left + window.scrollX}px`;
            translateBtn.style.top = `${rect.bottom + window.scrollY + 5}px`;
            translateBtn.style.display = "block";

            translatedTextDiv.style.display = "none"; // hide old translation if any
        }
    });

    translateBtn.addEventListener("click", function () {
        const selectedText = window.getSelection().toString().trim();
        if (!selectedText) {
            alert("Please select text to translate first.");
            return;
        }

        // Simulate translation (OVDJE ćeš kasnije ubaciti pravi API za prijevod!)
        const fakeTranslation = "[DE] " + selectedText; // oznaci kao fake njemački prijevod

        // Position translation div
        const btnRect = translateBtn.getBoundingClientRect();
        translatedTextDiv.style.left = `${btnRect.left}px`;
        translatedTextDiv.style.top = `${btnRect.bottom + 5}px`;

        translatedTextDiv.textContent = fakeTranslation;
        translatedTextDiv.style.display = "block";
    });
});
</script>

</body>
</html>
