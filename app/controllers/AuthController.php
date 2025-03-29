<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../core/SessionManager.php';
require_once __DIR__ . '/../../core/Database.php';

/**
 * Class AuthController
 * 
 * Handles authentication actions like login and register.
 */
class AuthController {
    private User $userModel;

    /**
     * Constructor
     * 
     * Initializes the User model with database connection.
     */
    public function __construct() {
        $db = Database::getInstance();
        $this->userModel = new User($db);
    }

    /**
     * login
     * 
     * Attempts to log in a user with provided credentials.
     * 
     * @param string $username
     * @param string $password
     * @return string|null - Returns error message or null if login is successful
     */
    public function login(string $username, string $password): ?string {
        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            return "User not found.";
        }

        if (password_verify($password, $user['password'])) {
            SessionManager::start($user['id'], $user['username']);
            header("Location: /app/views/dashboard/welcome.php");
            exit();
        }

        return "Invalid credentials.";
    }

    /**
     * register
     * 
     * Registers a new user after validation.
     * 
     * @param string $username
     * @param string $email
     * @param string $password
     * @return string|null - Returns error message or null if registration is successful
     */
    public function register(string $username, string $email, string $password): ?string {
        if ($this->userModel->exists($username)) {
            return "Username already taken.";
        }

        $created = $this->userModel->create($username, $email, $password);

        if ($created) {
            return null;
        }

        return "Registration failed. Try again.";
    }
}
