<?php
header('Content-Type: application/json');

$text = $_POST['text'] ?? '';

if (!$text) {
    echo json_encode(['error' => 'No text provided.']);
    exit;
}

$pythonPath = "python";
$scriptPath = "C:/xampp/htdocs/LingoLoop/models/ai_translation.py";
$escapedText = escapeshellarg($text);

$command = "$pythonPath \"$scriptPath\" $escapedText";
$output = shell_exec($command);

if (!$output) {
    echo json_encode(['error' => 'No output from translation script.']);
    exit;
}

// pokušaj parsiranja tupla
if (preg_match("/^\(['\"]?(.*?)['\"]?,\s*['\"]?(.*?)['\"]?\)$/", trim($output), $matches)) {
    $term = $matches[1];
    $translation = $matches[2];
    echo json_encode(['term' => $term, 'translation' => $translation]);
} else {
    echo json_encode(['error' => 'Failed to parse translation.', 'raw' => $output]);
}
