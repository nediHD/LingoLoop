<?php

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/app/controllers/AuthController.php';
require_once BASE_PATH . '/core/SessionManager.php';

// Handle login or register action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $auth = new AuthController();

    if ($action === 'login') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $error = $auth->login($username, $password);
        if ($error) {
            // Show login error view
            include BASE_PATH . '/app/views/auth/login.php';
        } else {
            // Redirect to dashboard or welcome
            include BASE_PATH . '/app/views/dashboard.php'; // Or create this file
        }
    }

    if ($action === 'register') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        $error = $auth->register($username, $email, $password);
        if ($error) {
            // Show registration error view
            include BASE_PATH . '/app/views/auth/register.php';
        } else {
            // Automatically log the user in after registration
            $auth->login($username, $password);
            include BASE_PATH . '/app/views/auth/login.php'; // Redirect to login
        }
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'register') {
    include BASE_PATH . '/app/views/auth/register.php';
} else {
    // Default: Show login view directly
    include BASE_PATH . '/app/views/auth/login.php';
}
