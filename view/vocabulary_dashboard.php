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

$db = Database::getInstance();
$userId = $_SESSION['user_id'] ?? null;

$vocabManager = new VocabularyManager($db);
$toLearn = $vocabManager->countWordsToLearn($userId);
$toRepeat = $vocabManager->countWordsToRepeat($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vocabulary Menu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: Arial, sans-serif;
            padding: 20px;
            margin: 0;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .stats-box {
            background-color: #111;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        .stats-box p {
            font-size: 1.2rem;
            margin: 10px 0;
        }

        .button-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            max-width: 500px;
            margin: 0 auto 40px auto;
        }

        .action-btn {
            background-color: #1a1a1a;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-align: center;
            text-decoration: none;
        }

        .action-btn:hover {
            background-color: #333;
        }

        .disabled {
            background-color: #333;
            color: #777;
            cursor: not-allowed;
            pointer-events: none;
        }

        .back-btn {
            display: block;
            background-color: #28a745;
            color: white;
            text-align: center;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 1.1rem;
            text-decoration: none;
            max-width: 300px;
            margin: 0 auto;
        }
    </style>
</head>
<body>

<h2>📘 Vocabulary Menu</h2>

<div class="stats-box">
    <p>🧠 Words to Learn: <strong><?= $toLearn ?></strong></p>
    <p>🔁 Words to Repeat: <strong><?= $toRepeat ?></strong></p>
</div>

<div class="button-grid">
    <a href="/lingoloop/view/add_word.php" class="action-btn">➕ Add Words</a>
    <a href="/lingoloop/view/delete_word.php" class="action-btn">🗑️ Delete Words</a>

    <?php if ($toLearn > 0): ?>
        <a href="/lingoloop/view/learn_words.php" class="action-btn">📖 Learn Words</a>
    <?php else: ?>
        <div class="action-btn disabled" title="No words to learn">📖 Learn Words</div>
    <?php endif; ?>

    <?php if ($toRepeat > 0): ?>
        <a href="/lingoloop/view/revise_words.php" class="action-btn">🔄 Revise Words</a>
    <?php else: ?>
        <div class="action-btn disabled" title="No words to revise">🔄 Revise Words</div>
    <?php endif; ?>
</div>

<a href="/lingoloop/view/dashboard.php" class="back-btn">⬅ Back to Dashboard</a>

</body>
</html>
