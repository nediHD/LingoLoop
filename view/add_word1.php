<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/models/SessionManager.php';
require_once BASE_PATH . '/models/VocabularyManager.php';
require_once BASE_PATH . '/models/Database.php';
$db = Database::getInstance();

$vocabManager = new VocabularyManager($db);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simple Input</title>
</head>
<body>

<h2>Enter Something</h2>

<form method="POST">
    <input type="text" name="user_input" placeholder="Type here..." required>
    <button type="submit">Submit</button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = htmlspecialchars($_POST['user_input']);
    echo "<h3>You entered:</h3>";
    echo "<p>$input</p>";
    $x = $vocabManager->translation_to($input);
    echo "<p>$x</p>";
}
?>

</body>
</html>
