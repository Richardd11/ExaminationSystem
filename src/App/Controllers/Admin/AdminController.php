<?php

namespace App\Controllers\Admin;

use App\Services\Auth\AuthService;
use App\Services\User\UserService;
use App\DAO\Auth\UserDAO;
use App\Core\View;

class AdminController
{
    private $authService;
    private $userService;
    private $view;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->userService = new UserService(new UserDAO());
        $this->view = new View();
        
        // Ensure user is authenticated and is admin
        $this->authService->requireAuth();
        $this->authService->requireRole('admin');
    }

    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        $currentUser = $this->authService->getCurrentUser();
        
        // Get real data from database
        $students = $this->userService->getUsersByRole('student');
        $faculty = $this->userService->getUsersByRole('faculty');
        
        $data = [
            'admin' => $currentUser,
            'students' => $students,
            'faculty' => $faculty,
            'yearSections' => $this->getYearSections($students)
        ];
        
        $this->view->display('admin.dashboard', $data);
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        // Check if user confirmed logout
        if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
            $this->authService->logout();
            
            // Get the base path for correct redirect
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $basePath = dirname($scriptName);
            $loginUrl = $basePath . '/login';
            
            header('Location: ' . $loginUrl);
            return;
        } else {
            // Show confirmation page
            $this->showLogoutConfirmation();
        }
    }

    /**
     * Show logout confirmation page
     */
    private function showLogoutConfirmation()
    {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = dirname($scriptName);
        $logoutUrl = $basePath . '/admin/logout?confirm=true';
        $dashboardUrl = $basePath . '/admin/dashboard';
        
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirm Logout - Admin Dashboard</title>
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



    /**
     * Get year-section combinations with counts
     */
    private function getYearSections($students)
    {
        $yearSections = [];
        
        foreach ($students as $student) {
            $key = $student['year_level'] . ' ' . $student['section'];
            if (!isset($yearSections[$key])) {
                $yearSections[$key] = 0;
            }
            $yearSections[$key]++;
        }
        
        return $yearSections;
    }

    /**
     * Handle add user request
     */
    public function addUser()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $result = $this->userService->createUser($_POST);
        
        if ($result['success']) {
            $this->showSuccess($result['message']);
        } else {
            $this->showError($result['message']);
        }
    }

    /**
     * Handle add student request
     */
    public function addStudent()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $result = $this->userService->createUser($_POST);
        
        if ($result['success']) {
            // Store success message in session
            $_SESSION['success_message'] = $result['message'];
            // Redirect back to dashboard
            $this->redirectToDashboard();
        } else {
            // Store error message in session
            $_SESSION['error_message'] = $result['message'];
            // Redirect back to dashboard
            $this->redirectToDashboard();
        }
    }

    /**
     * Redirect to admin dashboard
     */
    private function redirectToDashboard()
    {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = dirname($scriptName);
        header('Location: ' . $basePath . '/admin/dashboard');
        exit;
    }

    /**
     * Handle edit user request
     */
    public function editUser($userId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $result = $this->userService->updateUser($userId, $_POST);
        
        if ($result['success']) {
            $this->showSuccess($result['message']);
        } else {
            $this->showError($result['message']);
        }
    }

    /**
     * Handle edit student request
     */
    public function editStudent()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $userId = $_POST['user_id'] ?? null;
        if (!$userId) {
            $this->showError('User ID is required.');
            return;
        }

        $result = $this->userService->updateUser($userId, $_POST);
        
        if ($result['success']) {
            // Store success message in session
            $_SESSION['success_message'] = $result['message'];
            // Redirect back to dashboard
            $this->redirectToDashboard();
        } else {
            // Store error message in session
            $_SESSION['error_message'] = $result['message'];
            // Redirect back to dashboard
            $this->redirectToDashboard();
        }
    }

    /**
     * Handle delete user request
     */
    public function deleteUser($userId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $result = $this->userService->deleteUser($userId);
        
        if ($result['success']) {
            $this->showSuccess($result['message']);
        } else {
            $this->showError($result['message']);
        }
    }

    /**
     * Handle delete student request
     */
    public function deleteStudent()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $userId = $_POST['user_id'] ?? null;
        if (!$userId) {
            $this->showError('User ID is required.');
            return;
        }

        $result = $this->userService->deleteUser($userId);
        
        if ($result['success']) {
            // Store success message in session
            $_SESSION['success_message'] = $result['message'];
            // Redirect back to dashboard
            $this->redirectToDashboard();
        } else {
            // Store error message in session
            $_SESSION['error_message'] = $result['message'];
            // Redirect back to dashboard
            $this->redirectToDashboard();
        }
    }

    /**
     * Add a new faculty member
     */
    public function addFaculty()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method');
            return;
        }

        $data = [
            'school_id' => $_POST['school_id'] ?? '',
            'full_name' => $_POST['full_name'] ?? '',
            'role' => 'faculty',
            'password' => $_POST['password'] ?? ''
        ];

        // Validate required fields
        if (empty($data['school_id']) || empty($data['full_name'])) {
            $this->showError('School ID and Full Name are required');
            return;
        }

        try {
            $result = $this->userService->createUser($data);
            
            if ($result['success']) {
                $this->showSuccess('Faculty member added successfully');
                $this->redirectToDashboard();
            } else {
                $this->showError($result['message']);
                $this->redirectToDashboard();
            }
        } catch (Exception $e) {
            $this->showError('Error adding faculty member: ' . $e->getMessage());
       
       $this->redirectToDashboard(); }
    }

    /**
     * Edit an existing faculty member
     */
    public function editFaculty()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method');
            return;
        }

        $userId = $_POST['user_id'] ?? '';
        if (empty($userId)) {
            $this->showError('User ID is required');
            return;
        }

        $data = [
            'user_id' => $userId,
            'school_id' => $_POST['school_id'] ?? '',
            'full_name' => $_POST['full_name'] ?? '',
            'role' => 'faculty'
        ];

        // Validate required fields
        if (empty($data['school_id']) || empty($data['full_name'])) {
            $this->showError('School ID and Full Name are required');
            return;
        }

        try {
            $result = $this->userService->updateUser($data);
            
            if ($result['success']) {
                $this->showSuccess('Faculty member updated successfully');
            } else {
                $this->showError($result['message']);
            }
        } catch (Exception $e) {
            $this->showError('Error updating faculty member: ' . $e->getMessage());
        }
    }

    /**
     * Delete a faculty member
     */
    public function deleteFaculty()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method');
            return;
        }

        $userId = $_POST['user_id'] ?? '';
        if (empty($userId)) {
            $this->showError('User ID is required');
            return;
        }

        try {
            $result = $this->userService->deleteUser($userId);
            
            if ($result['success']) {
                $this->showSuccess('Faculty member deleted successfully');
                $this->redirectToDashboard();
            } else {
                $this->showError($result['message']);
                $this->redirectToDashboard();
            }
        } catch (Exception $e) {
            $this->showError('Error deleting faculty member: ' . $e->getMessage());
            $this->redirectToDashboard();
        }
    }

    /**
     * Show success message
     */
    private function showSuccess($message)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => $message
        ]);
    }

    /**
     * Show error message
     */
    private function showError($message)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
    }
}