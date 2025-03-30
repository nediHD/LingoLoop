<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../core/SessionManager.php';
require_once __DIR__ . '/../../core/Database.php';

class AuthController {
    private User $userModel;

    public function __construct() {
        $db = Database::getInstance();
        $this->userModel = new User($db);
    }

    public function login(string $username, string $password): ?string {
        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            return "User not found.";
        }

        if (password_verify($password, $user['password'])) {
            SessionManager::start($user['id'], $user['username']);

            // 👇 Provjeri da li korisnik već ima profil
            if (!$this->userModel->hasProfile($user['id'])) {
                header("Location: /lingoloop/public/index.php?action=setup_profile");
                exit();
            }

            header("Location: /lingoloop/public/index.php?action=welcome");
            exit();
        }

        return "Invalid credentials.";
    }

    public function register(string $username, string $email, string $password): ?string {
        if ($this->userModel->exists($username)) {
            return "Username already taken.";
        }

        $created = $this->userModel->create($username, $email, $password);

        if ($created) {
            // ✅ Automatski login i redirect na profil setup
            $user = $this->userModel->findByUsername($username);
            SessionManager::start($user['id'], $user['username']);
            header("Location: /lingoloop/public/index.php?action=setup_profile");
            exit();
        }

        return "Registration failed. Try again.";
    }
}
