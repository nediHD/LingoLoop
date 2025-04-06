<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SessionManager.php';

// Start session and validate user login
SessionManager::startSession();
if (!SessionManager::isLoggedIn()) {
    header("Location: /lingoloop/public/?action=login");
    exit();
}

$db = Database::getInstance();
$user_id = $_POST['user_id'] ?? null;

// Collect form inputs
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$birth_date = $_POST['birth_date'] ?? '';
$country = $_POST['country'] ?? '';
$english_level = $_POST['english_level'] ?? '';
$learning_goal = $_POST['learning_goal'] ?? '';
$learning_time_per_day = $_POST['learning_time_per_day'] ?? '';
$learning_style = $_POST['learning_style'] ?? '';
$previous_apps = $_POST['previous_apps'] ?? '';
$interests = $_POST['interests'] ?? '';
$favorite_content = $_POST['favorite_content'] ?? '';

// Save profile into the database
$sql = "INSERT INTO user_profiles 
    (user_id, first_name, last_name, birth_date, country, english_level,
    learning_goal, learning_time_per_day, learning_style, previous_apps,
    interests, favorite_content)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $db->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $db->error);
}

$stmt->bind_param(
    "isssssssssss",
    $user_id,
    $first_name,
    $last_name,
    $birth_date,
    $country,
    $english_level,
    $learning_goal,
    $learning_time_per_day,
    $learning_style,
    $previous_apps,
    $interests,
    $favorite_content
);

// If save was successful, call the AI and redirect
if ($stmt->execute()) {

    // 🔥 Call the vocabulary AI generation
    require_once __DIR__ . '/../app/models/ai_vocabulary_generation.php';

    $generator = new AI_VOCABULARY_GENERATION();
    $profileText = $generator->getting_data_from_ab($user_id);
    $vocabList = $generator->create_vocab($profileText);

    // Save the vocabulary list in session (for frontend display)
    $_SESSION['vocab'] = $vocabList;

    // ✅ Redirect to the vocabulary page
    header("Location: /lingoloop/public/vocabulary.php");
    exit();

} else {
    echo "Error saving profile: " . $stmt->error;
}
?>
