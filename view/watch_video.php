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

// === VIDEO URL IZ FORME ILI HARDCODIRAN ZA TEST ===
$videoUrl = "https://www.youtube.com/watch?v=Zognn5hwQdk"; // PROMIJENI NA VALIDAN VIDEO SA TRANSKRIPTOM

// === PUN PUT DO PYTHONA (ako treba) ===
$pythonPath = "python"; // ili "C:/Users/IME/AppData/Local/Programs/Python/Python311/python.exe"

// === PUTANJA DO PYTHON SKRIPTE ===
$scriptPath = "C:/xampp/htdocs/LingoLoop/models/youtube.py";

// === PRIPREMA KOMANDE ===
$command = escapeshellcmd("$pythonPath \"$scriptPath\" \"$videoUrl\"");

// === IZVRŠAVANJE PYTHON KODA ===
$output = shell_exec($command);

// === PRIKAZ NA EKRANU ===
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>LingoLoop | Watch Video</title>
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
        }
        pre {
            white-space: pre-wrap;
            background: #1e1e1e;
            padding: 20px;
            border-radius: 10px;
            overflow-x: auto;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <h1>📄 Readable Transcript</h1>
    <pre>" . htmlspecialchars($output) . "</pre>
</body>
</html>";
