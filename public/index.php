<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/controllers/AuthController.php';
require_once BASE_PATH . '/core/SessionManager.php';

$auth = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;

    if ($action === 'login') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $error = $auth->login($username, $password);

        if ($error) {
            include BASE_PATH . '/app/views/auth/login.php';
        }
    }

    if ($action === 'register') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $error = $auth->register($username, $email, $password);

        if ($error) {
            include BASE_PATH . '/app/views/auth/register.php';
        }
    }
} else {
    $action = $_GET['action'] ?? null;

    if ($action === 'register') {
        include BASE_PATH . '/app/views/auth/register.php';
    } elseif ($action === 'welcome') {
        include BASE_PATH . '/app/views/dashboard/welcome.php';
    } elseif ($action === 'login') {
        include BASE_PATH . '/app/views/auth/login.php';
    } else {
        include BASE_PATH . '/app/views/profile/setup_profile.php';
    }
}
