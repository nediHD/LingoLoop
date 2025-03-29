<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SessionManager.php';

// Provjeri je li korisnik ulogiran
if (!SessionManager::isLoggedIn()) {
    header("Location: /lingoloop/public/?action=login");
    exit();
}

// Poveži se s bazom
$db = Database::getInstance()->getConnection();

// Pripremi podatke
$user_id = $_POST['user_id'] ?? null;
$full_name = $_POST['full_name'] ?? '';
$birth_date = $_POST['birth_date'] ?? '';
$country = $_POST['country'] ?? '';
$native_language = $_POST['native_language'] ?? '';
$occupation = $_POST['occupation'] ?? '';
$english_level = $_POST['english_level'] ?? '';
$learning_goal = $_POST['learning_goal'] ?? '';
$learning_frequency = $_POST['learning_frequency'] ?? '';
$learning_time_per_day = $_POST['learning_time_per_day'] ?? '';
$learning_style = $_POST['learning_style'] ?? '';
$other_languages = $_POST['other_languages'] ?? '';
$preferred_language = $_POST['preferred_language'] ?? '';
$learning_difficulty = $_POST['learning_difficulty'] ?? '';
$previous_apps = $_POST['previous_apps'] ?? '';
$interests = $_POST['interests'] ?? '';
$favorite_content = $_POST['favorite_content'] ?? '';
$preferred_genres = $_POST['preferred_genres'] ?? '';

// Ubaci u bazu
$sql = "INSERT INTO user_profiles (
    user_id, full_name, birth_date, country, native_language, occupation, english_level,
    learning_goal, learning_frequency, learning_time_per_day,
    learning_style, other_languages, preferred_language,
    learning_difficulty, previous_apps, interests, favorite_content, preferred_genres
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $db->prepare($sql);
$stmt->bind_param(
    "isssssssssssssssss",
    $user_id, $full_name, $birth_date, $country, $native_language, $occupation, $english_level,
    $learning_goal, $learning_frequency, $learning_time_per_day,
    $learning_style, $other_languages, $preferred_language,
    $learning_difficulty, $previous_apps, $interests, $favorite_content, $preferred_genres
);

if ($stmt->execute()) {
    header("Location: /lingoloop/public/index.php?action=welcome");
    exit();
} else {
    echo "Error saving profile: " . $stmt->error;
}
