<?php
require_once BASE_PATH . '/models/SessionManager.php';

SessionManager::startSession();

if (!SessionManager::isLoggedIn()) {
    header("Location: /lingoloop/view/?action=login");
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome | LingoLoop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white flex items-center justify-center h-screen">

    <div class="bg-gray-900 p-10 rounded-xl shadow-lg text-center max-w-md w-full">
        <h1 class="text-3xl font-bold mb-4">Welcome, <?php echo htmlspecialchars($username); ?>! 👋</h1>
        <p class="text-gray-300 mb-6">You are successfully logged in to <span class="font-semibold text-white">LingoLoop</span>.</p>
        
        <a href="/lingoloop/controller/LogoutController.php" class="bg-red-600 hover:bg-red-700 transition px-6 py-2 rounded text-white font-semibold">
            Logout
        </a>
    </div>

</body>
</html>
