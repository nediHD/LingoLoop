<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/models/SessionManager.php';
require_once BASE_PATH . '/models/VocabularyManager.php';
require_once BASE_PATH . '/models/Database.php';

SessionManager::startSession();
if (!SessionManager::isLoggedIn()) {
    header("Location: /lingoloop/view/index.php?action=login");
    exit();
}

// Reset session data
if (isset($_GET['reset'])) {
    unset($_SESSION['revise_data'], $_SESSION['rot_count'], $_SESSION['total'], $_SESSION['answer_revealed'], $_SESSION['user_input']);
    header("Location: vocabulary_dashboard.php");
    exit();
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];
$vocabManager = new VocabularyManager($db);


if (!isset($_SESSION['revise_data'])) {
    $wordsToRevise = $vocabManager->getWordsToRevise($userId);
    $_SESSION['revise_data'] = is_array($wordsToRevise) ? array_slice($wordsToRevise, 0, 10) : [];
    $_SESSION['rot_count'] = 0;
    $_SESSION['total'] = count($_SESSION['revise_data']);
    $dsds = $_SESSION['total'];
}

// Exit if no words
if (empty($_SESSION['revise_data'])) {
    unset($_SESSION['revise_data'], $_SESSION['rot_count'], $_SESSION['total'], $_SESSION['answer_revealed'], $_SESSION['user_input']);
    header("Location: vocabulary_dashboard.php");
    exit();
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $current = $_SESSION['revise_data'][0];

    if ($action === 'reveal') {
        $_SESSION['answer_revealed'] = true;
        $_SESSION['user_input'] = $_POST['user_answer'] ?? '';
    }

    if ($action === 'yes') {
    $vocabManager->updateRevision($current['id'], 'Yes');
    array_shift($_SESSION['revise_data']);
    $dsdhs = $_SESSION['revise_data'];
    $_SESSION['rot_count']++;
    unset($_SESSION['answer_revealed'], $_SESSION['user_input']);
    }

    if ($action === 'no') {
    $vocabManager->updateRevision($current['id'], 'No');
    // Pomeri trenutnu reč na kraj niza
    $word = array_shift($_SESSION['revise_data']);
    $_SESSION['revise_data'][] = $word;
    $dsdhs = $_SESSION['revise_data'];
    $_SESSION['rot_count']++;
    unset($_SESSION['answer_revealed'], $_SESSION['user_input']);
    }


    if (empty($_SESSION['revise_data'])) {
        unset($_SESSION['revise_data'], $_SESSION['rot_count'], $_SESSION['total'], $_SESSION['answer_revealed'], $_SESSION['user_input']);
        header("Location: vocabulary_dashboard.php");
        exit();
    }
}

// Determine direction and current word (after POST)
$current = $_SESSION['revise_data'][0];
$direction = $_SESSION['rot_count'] % 3 < 2 ? 'DE_EN' : 'EN_DE';
$question = $direction === 'DE_EN' ? $current['translation'] : $current['term'];
$correctAnswer = $direction === 'DE_EN' ? $current['term'] : $current['translation'];

$total = $_SESSION['total'];
$done = $total - count($_SESSION['revise_data']);
$progress = $total > 0 ? round(($done / $total) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Revise Words</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { background-color: #000; color: white; font-family: Helvetica, Arial, sans-serif; padding: 40px; text-align: center; }
        h2 { font-size: 2rem; margin-bottom: 20px; }
        .word-box { background-color: #111; padding: 30px; font-size: 1.8rem; border-radius: 10px; margin-bottom: 20px; }
        .input-box { padding: 12px; font-size: 1.1rem; width: 60%; border-radius: 6px; border: none; margin-bottom: 20px; }
        .answer { font-size: 1.3rem; margin: 20px auto; color: #ffc107; }
        .progress-container { width: 100%; background-color: #333; border-radius: 8px; overflow: hidden; margin: 30px auto; max-width: 500px; }
        .progress-bar { height: 20px; background-color: #28a745; width: <?= $progress ?>%; transition: width 0.5s ease-in-out; }
        .progress-label { margin-bottom: 8px; font-size: 1rem; color: #ccc; }
        button { padding: 12px 20px; margin: 5px; border: none; border-radius: 6px; font-size: 1rem; cursor: pointer; }
        .reveal-btn { background-color: #ffc107; color: #000; }
        .yes-btn { background-color: #28a745; color: white; }
        .no-btn { background-color: #dc3545; color: white; }
        .menu-btn { background-color: #007bff; color: white; text-decoration: none; padding: 10px 20px; border-radius: 6px; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>

<h2>🔁 <?= $direction === 'DE_EN' ? "Review: German ➜ English" : "Review: English ➜ German" ?></h2>

<div class="progress-label">Progress: <?= $done ?> / <?= $total ?> revised</div>
<div class="progress-container"><div class="progress-bar"></div></div>

<div class="word-box"><?= htmlspecialchars($question) ?></div>

<form method="POST">
    <?php if (empty($_SESSION['answer_revealed'])): ?>
        <input type="text" name="user_answer" class="input-box" placeholder="Type your translation..." autofocus required>
        <br>
        <button name="action" value="reveal" class="reveal-btn">Reveal</button>
    <?php else: ?>
        <div class="answer">
            Your answer: <strong><?= htmlspecialchars($_SESSION['user_input']) ?></strong><br>
            Correct answer: <strong><?= htmlspecialchars($correctAnswer) ?></strong>
        </div>
        <button name="action" value="yes" class="yes-btn">I Knew It</button>
        <button name="action" value="no" class="no-btn">I Didn't Know</button>
    <?php endif; ?>
</form>

<a href="revise_words.php?reset=1" class="menu-btn">🔙 Back to Menu</a>

</body>
</html>
