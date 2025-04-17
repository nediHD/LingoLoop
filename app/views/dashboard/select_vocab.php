<?php
require_once __DIR__ . '/../../../core/SessionManager.php';

SessionManager::startSession();



if (!SessionManager::isLoggedIn()) {
    header("Location: /lingoloop/public/?action=login");
    exit();
}

$vocabList = $_SESSION['vocab_list'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Your Favorite Vocabulary</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        let selectedWords = [];

        function toggleSelection(index, button) {
            const maxSelection = 10;
            const word = index.toString();

            const alreadySelected = selectedWords.includes(word);

            if (alreadySelected) {
                selectedWords = selectedWords.filter(w => w !== word);
                button.classList.remove('selected');
            } else {
                if (selectedWords.length >= maxSelection) {
                    alert("You can only select up to 10 words.");
                    return;
                }
                selectedWords.push(word);
                button.classList.add('selected');
            }

            document.getElementById('selectedWords').value = JSON.stringify(selectedWords);
        }
    </script>
    <style>
        body { background-color: #000; color: #fff; font-family: Arial; padding: 20px; }
        .word-box { background-color: #111; margin-bottom: 10px; padding: 15px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        .heart-btn { cursor: pointer; font-size: 24px; }
        .selected { color: red; }
        button[type="submit"] { padding: 10px 20px; margin-top: 20px; border: none; background-color: #28a745; color: white; font-size: 1.1rem; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

<h2>Select Up to 10 Favorite Words ❤️</h2>

<form method="POST" action="/lingoloop/public/index.php">
    <?php foreach ($vocabList as $index => $item): ?>
        <div class="word-box">
            <div>
                <strong><?= htmlspecialchars($item[0]) ?></strong> – <?= htmlspecialchars($item[1]) ?>
            </div>
            <div class="heart-btn" onclick="toggleSelection(<?= $index ?>, this)">&#10084;</div>
        </div>
    <?php endforeach; ?>

    <input type="hidden" name="selected_words" id="selectedWords" value="[]">
    <button type="submit">Save Selected Words</button>
</form>

</body>
</html>
