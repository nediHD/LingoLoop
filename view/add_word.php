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

if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {
    $word = trim($_POST['term']);
    if ($word) {
        $translation = $vocabManager->translation_to($word);
        if ($word && $translation) {
            $vocabManager->addWordToProfile($userId, $word, $translation);
        } else {
            echo "<p style='color:red;'>❌ Translation failed. Please check your internet connection or AI service.</p>";

        }
        
    }
}

// Današnje reči
$todayWords = array_filter(
    $vocabManager->getAllWords($userId),
    fn($word) => $word['date_added'] === date('Y-m-d')
);

// Brisanje
if (isset($_GET['delete'])) {
    $wordId = intval($_GET['delete']);
    $vocabManager->deleteWord($wordId);
    header("Location: add_words.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Word (Auto Translation)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { background-color: #000; color: #fff; font-family: Arial, sans-serif; padding: 20px; }
        h2 { text-align: center; margin-bottom: 20px; }

        form { max-width: 600px; margin: 0 auto 30px auto; }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            margin-bottom: 10px;
            border-radius: 6px;
            border: none;
        }

        button[type="submit"], .menu-btn {
            padding: 12px 20px;
            font-size: 1rem;
            border: none;
            border-radius: 6px;
            background-color: #28a745;
            color: white;
            cursor: pointer;
            margin-top: 10px;
            display: inline-block;
        }

        .menu-btn {
            background-color: #007bff;
            text-decoration: none;
        }

        .scroll-container {
            max-height: 400px;
            overflow-y: auto;
            margin-top: 30px;
            border-top: 1px solid #333;
            padding-top: 20px;
        }

        .word-button {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #111;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 6px;
        }

        .word-button span {
            font-size: 1.1rem;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h2>🧠 Add Word (Auto-Translate)</h2>

<form method="POST">
    <input type="text" name="term" placeholder="Enter word" required>
    <button type="submit">Submit</button>
    <a href="vocabulary_dashboard.php" class="menu-btn">🔙 Menu</a>
</form>

<div class="scroll-container">
    <h3>📅 Words Added Today</h3>
    <?php foreach ($todayWords as $word): ?>
        <div class="word-button">
            <span><?= htmlspecialchars($word['term']) ?> – <?= htmlspecialchars($word['translation']) ?></span>
            <form method="GET" onsubmit="return confirm('Delete this word?');">
                <input type="hidden" name="delete" value="<?= $word['id'] ?>">
                <button type="submit" class="delete-btn">Delete</button>
            </form>
        </div>
    <?php endforeach; ?>
    <?php if (empty($todayWords)): ?>
        <p>No words added today.</p>
    <?php endif; ?>
</div>

</body>
</html>
