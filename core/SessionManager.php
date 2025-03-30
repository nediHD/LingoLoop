<?php

/**
 * Class SessionManager
 * 
 * Manages user sessions: start, destroy, and check login status.
 */
class SessionManager {
    
    /**
     * start
     * 
     * Starts a new session and stores user data.
     * 
     * @param int $userId
     * @param string $username
     * @return void
     */
    public static function start(int $userId, string $username): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
    }

    /**
     * destroy
     * 
     * Ends the session and clears session data.
     * 
     * @return void
     */
    public static function destroy(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        session_destroy();
    }

    /**
     * isLoggedIn
     * 
     * Checks if the user is currently logged in.
     * 
     * @return bool
     */
    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }


    

    public static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * getUsername
     * 
     * Returns the currently logged-in username.
     * 
     * @return string|null
     */
    public static function getUsername(): ?string {
        return $_SESSION['username'] ?? null;
    }

    
    
}
