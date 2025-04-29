<?php
// Test verzija za provjeru je li pozvan save_word.php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/models/SessionManager.php';
SessionManager::startSession();

if (!SessionManager::isLoggedIn()) {
    header("Location: /lingoloop/view/login.php");
    exit();
}
echo json_encode(['success' => true, 'message' => 'save_word.php je uspješno pozvan']);
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/SaveVocab.php';


$index = isset($_POST['index']) ? intval($_POST['index']) : -1;
$userId = $_SESSION['user_id'] ?? null;
$vocabList = $_SESSION['translations'] ?? [];
$term = $vocabList[$index]['term'] ?? 'nedefinirano';
$translation = $vocabList[$index]['translation'] ?? 'nedefinirano';
$db = Database::getInstance();
$model = new UserVocabulary($db);
$model->saveSelectedWord($userId, $term, $translation);
// Ukloni spremljenu riječ iz sesije
unset($_SESSION['translations'][$index]);

// Reindeksiraj niz da ne ostanu rupe u indeksima
$_SESSION['translations'] = array_values($_SESSION['translations']);


file_put_contents(
    'save_log.txt',
    date('Y-m-d H:i:s') .
    " - Pozvan save_word.php | index: $index | userId: $userId | term: $term | translation: $translation\n",
    FILE_APPEND
);
