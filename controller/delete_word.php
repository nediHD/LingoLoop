<?php
// Prikaz grešaka za debug (makni za produkciju)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Pokreni sesiju
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/models/SessionManager.php';
SessionManager::startSession();

// Provjera prijave
if (!SessionManager::isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Niste prijavljeni.']);
    exit();
}

// Dohvati podatke
$index = isset($_POST['index']) ? intval($_POST['index']) : -1;
$vocabList = $_SESSION['translations'] ?? [];

if ($index >= 0 && isset($vocabList[$index])) {
    // Ukloni riječ
    unset($_SESSION['translations'][$index]);

    // Reindeksiraj da se ne raspadne redoslijed
    $_SESSION['translations'] = array_values($_SESSION['translations']);

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Nevažeći indeks.']);
}
