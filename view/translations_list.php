<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/models/SessionManager.php';
SessionManager::startSession();

if (!SessionManager::isLoggedIn()) {
    header("Location: /lingoloop/view/login.php");
    exit();
}
$translations = $_SESSION['translations'] ?? [];
$videoId = $_SESSION['video_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Saved Translations</title>
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
            font-family: 'Segoe UI', sans-serif;
            padding: 40px;
        }
        h1 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 30px;
        }
        table {
            width: 90%;
            margin: 0 auto;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #444;
            text-align: left;
        }
        th {
            background-color: #1e1e1e;
        }
        tr:nth-child(even) {
            background-color: #222;
        }
        .actions button {
            padding: 6px 12px;
            margin-right: 10px;
            font-size: 0.9rem;
            border-radius: 4px;
            cursor: pointer;
            border: none;
        }
        .save-btn {
            background-color: #28a745;
            color: white;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        .status {
            color: #00ff99;
            font-weight: bold;
        }
        .watch-btn {
            display: block;
            width: fit-content;
            margin: 40px auto 0;
            padding: 12px 24px;
            font-size: 1.2rem;
            background-color: #007bff;
            color: white;
            border-radius: 8px;
            text-decoration: none;
        }
        .watch-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<h1>📋 Saved Translations</h1>

<?php if (empty($translations)): ?>
    <p style="text-align:center;">No translations yet.</p>
<?php else: ?>
    <table id="translations-table">
        <tr>
            <th>#</th>
            <th>Original</th>
            <th>Translation</th>
            <th>Actions</th>
            <th>Status</th>
        </tr>
        <?php foreach ($translations as $index => $entry): ?>
        <tr data-index="<?= $index ?>">
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($entry['term']) ?></td>
            <td><?= htmlspecialchars($entry['translation']) ?></td>
            <td class="actions">
                <button class="save-btn" onclick="saveWord(<?= $index ?>)">💾 Save</button>
                <button class="delete-btn" onclick="deleteWord(<?= $index ?>)">🗑️ Delete</button>
            </td>
            <td class="status" id="status-<?= $index ?>"></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<a href="/lingoloop/view/watch_video_embed.php?video_id=<?= urlencode($videoId) ?>" class="watch-btn">▶️ Watch Video</a>

<script>
function saveWord(index) {
    fetch('../controller/save_word.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'index=' + encodeURIComponent(index)
    })
    .then(response => response.json())
    .then(data => {
        const statusCell = document.getElementById('status-' + index);
        const row = document.querySelector(`tr[data-index="${index}"]`);
        const actionsCell = row.querySelector('.actions');

        if (data.success) {
            statusCell.textContent = '✅ Saved';

            // Ukloni Save i Delete gumbe
            actionsCell.innerHTML = ''; // ovo briše sadržaj <td class="actions">
        } else {
            statusCell.textContent = '❌ Error';
            if (data.error) {
                console.error("Save error:", data.error);
                statusCell.textContent += " (" + data.error + ")";
            }
        }
    });
}


function deleteWord(index) {
    fetch('../controller/delete_word.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'index=' + encodeURIComponent(index)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const row = document.querySelector(`tr[data-index="${index}"]`);
            row.remove();
        } else {
            alert("Error deleting word.");
        }
    });
}
</script>

</body>
</html>
