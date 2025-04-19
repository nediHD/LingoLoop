<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/SessionManager.php';
require_once __DIR__ . '/../models/Database.php';

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
                header("Location: /lingoloop/view/setup_profile.php");


                exit();
            }

            $scriptPath = BASE_PATH . "/models/ai_vocabulary_generation.py";
            $command = "python \"$scriptPath\" {$user['id']} 2>&1";

            $output = shell_exec($command);
            echo "<pre>";
            echo "PYTHON RAW OUTPUT:\n";
            echo htmlspecialchars($output);
            echo "</pre>";
            $vocab = json_decode($output, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $_SESSION['vocab_list'] = $vocab;
            header("Location: /lingoloop/view/select_vocab.php");


            exit();
        } else {
            echo "Error decoding vocabulary list.";
        }
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
            header("Location: /lingoloop/view/setup_profile.php");
            exit();
        }

        return "Registration failed. Try again.";
    }
}
