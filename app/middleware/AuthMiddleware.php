<?php
class AuthMiddleware {
    // Check if user is logged in
    public static function check() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }
    }
    
    // Check if user is guest (not logged in)
    public static function isGuest() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/home');
            exit;
        }
    }
}
