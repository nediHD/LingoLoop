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

if (isset($_GET['reset'])) {
    unset($_SESSION['revise_data'], $_SESSION['view_mode'], $_SESSION['rot_count'], $_SESSION['total']);
    header("Location: vocabulary_dashboard.php");
    exit();
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];
$vocabManager = new VocabularyManager($db);

if (!isset($_SESSION['revise_data'])) {
    $_SESSION['revise_data'] = $vocabManager->getWordsToRevise($userId);
    $_SESSION['rot_count'] = 0;
    $_SESSION['view_mode'] = 0;
    $_SESSION['total'] = count($_SESSION['revise_data']);
}

if (empty($_SESSION['revise_data'])) {
    unset($_SESSION['revise_data'], $_SESSION['view_mode'], $_SESSION['rot_count'], $_SESSION['total']);
    header("Location: vocabulary_dashboard.php");
    exit();
}

$direction = $_SESSION['rot_count'] % 3 < 2 ? 'DE_EN' : 'EN_DE';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $current = $_SESSION['revise_data'][0];

    if ($action === 'check') {
        $_SESSION['view_mode'] = 1;
    }

    if ($action === 'yes' || $action === 'no') {
        $vocabManager->updateRevision($current['id'], ucfirst($action));
        array_shift($_SESSION['revise_data']);
        $_SESSION['rot_count']++;
        $_SESSION['view_mode'] = 0;
    }

    if (empty($_SESSION['revise_data'])) {
        unset($_SESSION['revise_data'], $_SESSION['view_mode'], $_SESSION['rot_count'], $_SESSION['total']);
        header("Location: vocabulary_dashboard.php");
        exit();
    }
}

$current = $_SESSION['revise_data'][0];
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
        .check-btn { background-color: #ffc107; color: #000; }
        .yes-btn { background-color: #28a745; color: white; }
        .no-btn { background-color: #dc3545; color: white; }
        .menu-btn { background-color: #007bff; color: white; text-decoration: none; padding: 10px 20px; border-radius: 6px; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>

<h2>🔁 <?= $direction === 'DE_EN' ? "Wiederholung: Deutsch ➜ Englisch" : "Wiederholung: Englisch ➜ Deutsch" ?></h2>

<div class="progress-label">Progress: <?= $done ?> / <?= $total ?> revised</div>
<div class="progress-container"><div class="progress-bar"></div></div>

<div class="word-box"><?= htmlspecialchars($question) ?></div>

<form method="POST">
    <?php if ($_SESSION['view_mode'] === 0): ?>
        <input type="text" name="user_answer" class="input-box" placeholder="Your answer..." autofocus required>
        <br>
        <button name="action" value="check" class="check-btn">Check</button>
    <?php else: ?>
        <div class="answer">✅ Correct answer: <strong><?= htmlspecialchars($correctAnswer) ?></strong></div>
        <button name="action" value="yes" class="yes-btn">I Knew It</button>
        <button name="action" value="no" class="no-btn">I Didn't Know</button>
    <?php endif; ?>
</form>

<a href="revise_words.php?reset=1" class="menu-btn">🔙 Menu</a>

</body>
</html>
