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

// Brisanje reči
if (isset($_GET['delete'])) {
    $wordId = intval($_GET['delete']);
    $vocabManager->deleteWord($wordId);
    header("Location: delete_word.php");
    exit();
}

$allWords = $vocabManager->getAllWords($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Words</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background-color: #000;
            color: white;
            font-family: Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 40px 20px;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            text-align: center;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 30px;
        }

        .button {
            padding: 14px 26px;
            font-size: 1.1rem;
            border: none;
            border-radius: 8px;
            margin: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .menu-btn {
            background-color: #007bff;
            color: white;
        }

        .search-btn {
            background-color: #28a745;
            color: white;
        }

        .button:hover {
            opacity: 0.85;
        }

        .search-box {
            width: 100%;
            max-width: 400px;
            padding: 10px;
            margin: 30px auto;
            font-size: 1.1rem;
            border-radius: 6px;
            border: none;
        }

        .word-card {
            background-color: #111;
            padding: 16px;
            margin-bottom: 12px;
            border-radius: 6px;
            text-align: center;
        }

        .word-card span {
            font-size: 1.1rem;
            display: block;
            margin-bottom: 10px;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
    <script>
        function filterWords() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let cards = document.getElementsByClassName('word-card');

            for (let card of cards) {
                let text = card.innerText.toLowerCase();
                card.style.display = text.includes(input) ? 'block' : 'none';
            }
        }
    </script>
</head>
<body>

<div class="container">
    <h1>🗑️ Delete Words</h1>

    <a href="vocabulary_dashboard.php" class="button menu-btn">📚 Words Menu</a>

    <input type="text" id="searchInput" onkeyup="filterWords()" class="search-box" placeholder="🔍 Search words...">

    <?php if (empty($allWords)): ?>
        <p>No words found.</p>
    <?php else: ?>
        <?php foreach ($allWords as $word): ?>
            <div class="word-card">
                <span><?= htmlspecialchars($word['term']) ?> – <?= htmlspecialchars($word['translation']) ?></span>
                <form method="GET" onsubmit="return confirm('Are you sure you want to delete this word?');">
                    <input type="hidden" name="delete" value="<?= $word['id'] ?>">
                    <button type="submit" class="delete-btn">Delete</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
