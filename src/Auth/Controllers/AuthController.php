<?php

namespace App\Auth\Controllers;

use App\Auth\Services\AuthService;
use App\Core\View;

class AuthController
{
    private $authService;
    private $view;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->view = new View();
    }

    /**
     * Show login page
     */
    public function showLogin()
    {
        // If user is already logged in, redirect to appropriate dashboard
        if ($this->authService->isAuthenticated()) {
            $user = $this->authService->getCurrentUser();
            $this->redirectToDashboard($user['role']);
            return;
        }

        $this->view->display('auth.login');
    }

    /**
     * Handle login request
     */
    public function login()
    {
        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showLoginError('Invalid request method.');
            return;
        }

        try {
            // Get POST data
            $school_id = $_POST['school_id'] ?? '';
            $password = $_POST['password'] ?? '';

            // Validate input
            if (empty($school_id) || empty($password)) {
                $this->showLoginError('School ID and password are required.');
                return;
            }

            // Use AuthService for login
            $result = $this->authService->login($school_id, $password);

            if ($result['success']) {
                // Redirect based on role
                $this->redirectToDashboard($result['user']['role']);
            } else {
                // Show error message
                $this->showLoginError($result['message']);
            }
        } catch (\Exception $e) {
            // Handle any unexpected errors
            $this->showLoginError('An error occurred during login.');
        }
    }

    /**
     * Show login page with error
     */
    private function showLoginError($message)
    {
        $this->view->display('auth.login', ['error' => $message]);
    }

    /**
     * Handle logout request
     */
    public function logout()
    {
        header('Content-Type: application/json');

        $result = $this->authService->logout();

        echo json_encode([
            'status' => 'success',
            'message' => $result['message']
        ]);
    }

    /**
     * Redirect to appropriate dashboard based on role
     */
    private function redirectToDashboard($role)
    {
        // Get the base path for correct redirect
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = dirname($scriptName);
        
        switch ($role) {
            case 'admin':
                header('Location: ' . $basePath . '/admin/dashboard');
                break;
            case 'faculty':
                header('Location: ' . $basePath . '/faculty/dashboard');
                break;
            case 'student':
                header('Location: ' . $basePath . '/student-success');
                break;
            default:
                header('Location: ' . $basePath . '/login');
        }
        return;
    }
}