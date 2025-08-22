<?php

namespace App\Controllers\Faculty;

use App\Services\Auth\AuthService;
use App\Core\View;

class FacultyController
{
    private AuthService $authService;
    private View $view;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->view = new View();

        // Enforce authentication and role
        $this->authService->requireAuth();
        $this->authService->requireRole('faculty');
    }

    public function dashboard(): void
    {
        $currentUser = $this->authService->getCurrentUser();
        $this->view->display('faculty.dashboard', [
            'faculty' => $currentUser,
        ]);
    }

    public function logout(): void
    {
        if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
            $this->authService->logout();
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $basePath = dirname($scriptName);
            header('Location: ' . $basePath . '/login');
            return;
        }

        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = dirname($scriptName);
        $logoutUrl = $basePath . '/faculty/logout?confirm=true';
        $dashboardUrl = $basePath . '/faculty/dashboard';

        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirm Logout - Faculty Dashboard</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container mt-5">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card shadow">
                            <div class="card-header bg-warning text-white">
                                <h4 class="mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Confirm Logout
                                </h4>
                            </div>
                            <div class="card-body text-center">
                                <i class="fas fa-sign-out-alt fa-3x text-warning mb-3"></i>
                                <h5>Are you sure you want to logout?</h5>
                                <p class="text-muted">You will be redirected to the login page.</p>
                                
                                <div class="mt-4">
                                    <a href="' . $logoutUrl . '" class="btn btn-warning me-2">
                                        <i class="fas fa-sign-out-alt me-2"></i>
                                        Yes, Logout
                                    </a>
                                    <a href="' . $dashboardUrl . '" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>';
        return;
    }
}