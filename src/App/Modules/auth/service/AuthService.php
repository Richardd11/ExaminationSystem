<?php

namespace App\Services\Auth;

use App\DAO\Auth\UserDAO;

class AuthService
{
    private $userDAO;

    public function __construct(UserDAO $userDAO = null)
    {
        $this->userDAO = $userDAO ?? new UserDAO();
    }

    /**
     * Login user with school ID and password
     */
    public function login($school_id, $password)
    {
        // Validate inputs - check if trimmed values are empty
        if (empty(trim($school_id)) || empty(trim($password))) {
            return [
                'success' => false,
                'message' => 'School ID and password are required.'
            ];
        }

        // Sanitize inputs
        $school_id = trim($school_id);
        $password = trim($password);

        // Authenticate user
        $user = $this->userDAO->authenticate($school_id, $password);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid School ID or password.'
            ];
        }

        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Store user data in session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['school_id'] = $user['school_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['year_level'] = $user['year_level'] ?? null;
        $_SESSION['section'] = $user['section'] ?? null;

        return [
            'success' => true,
            'message' => 'Login successful!',
            'user' => [
                'user_id' => $user['user_id'],
                'school_id' => $user['school_id'],
                'full_name' => $user['full_name'],
                'role' => $user['role'],
                'year_level' => $user['year_level'] ?? null,
                'section' => $user['section'] ?? null
            ]
        ];
    }

    /**
     * Logout current user
     */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear session data
        session_unset();
        
        // Destroy session
        session_destroy();
        
        // Clear the $_SESSION array as well
        $_SESSION = [];
        
        // Additional cleanup to ensure session is completely cleared
        if (function_exists('session_write_close')) {
            session_write_close();
        }
        
        // Force clear the session array again to be absolutely sure
        $_SESSION = [];

        return [
            'success' => true,
            'message' => 'Logged out successfully.'
        ];
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['user_id']);
    }

    /**
     * Get current user data
     */
    public function getCurrentUser()
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        // Check if all required session variables exist
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id']) || 
            !isset($_SESSION['full_name']) || !isset($_SESSION['role'])) {
            return null;
        }

        return [
            'user_id' => $_SESSION['user_id'],
            'school_id' => $_SESSION['school_id'],
            'full_name' => $_SESSION['full_name'],
            'role' => $_SESSION['role'],
            'year_level' => $_SESSION['year_level'] ?? null,
            'section' => $_SESSION['section'] ?? null
        ];
    }

    /**
     * Require authentication
     */
    public function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Authentication required.',
                'redirect' => '/login'
            ];
        }

        return [
            'success' => true,
            'message' => 'User is authenticated.'
        ];
    }

    /**
     * Require specific role
     */
    public function requireRole($requiredRole)
    {
        $authResult = $this->requireAuth();
        if (!$authResult['success']) {
            return $authResult;
        }

        $user = $this->getCurrentUser();
        if (!$user || !isset($user['role'])) {
            return [
                'success' => false,
                'message' => 'User data incomplete.',
                'redirect' => '/login'
            ];
        }
        
        if ($user['role'] !== $requiredRole) {
            return [
                'success' => false,
                'message' => 'Insufficient permissions.',
                'redirect' => '/login'
            ];
        }

        return [
            'success' => true,
            'message' => 'User has required role.'
        ];
    }
}