<?php

require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../core/SessionManager.php';

// Handle login or register action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $auth = new AuthController();

    if ($action === 'login') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $error = $auth->login($username, $password);
        if ($error) {
            // Prikazi grešku u login view
            include __DIR__ . '/../app/views/auth/login.php';
        }
    }

    if ($action === 'register') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        $error = $auth->register($username, $email, $password);
        if ($error) {
            // Prikazi grešku u register view
            include __DIR__ . '/../app/views/auth/register.php';
        } else {
            // Nakon uspješne registracije, automatski ulogiraj
            $auth->login($username, $password);
        }
    }
} else {
    // Ako se direktno pristupi, preusmjeri na login
    header("Location: /app/views/auth/login.php");
    exit();
}
