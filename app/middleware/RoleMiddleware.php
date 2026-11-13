<?php
class RoleMiddleware {
    // Check if logged in user has one of the allowed roles
    public static function check($allowedRoles = []) {
        AuthMiddleware::check(); // Must be logged in first
        
        $userRole = $_SESSION['user_role'] ?? '';
        
        if (!in_array($userRole, $allowedRoles)) {
            // If unauthorized, redirect to home with a message (or generic 403 page)
            header('Location: ' . BASEURL . '/home');
            exit;
        }
    }

    // Alias for check()
    public static function allow($allowedRoles = []) {
        self::check($allowedRoles);
    }
}
