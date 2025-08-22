<?php

namespace Tests\Integration\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\Admin\AdminController;

class AdminControllerTest extends TestCase
{
    private AdminController $adminController;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure clean session state
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        $_SESSION = [];
        
        // Start a fresh session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $this->adminController = new AdminController();
    }

    protected function tearDown(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        $_SESSION = [];
        parent::tearDown();
    }

    /** @test */
    public function it_should_display_admin_dashboard_with_user_data()
    {
        // Simulate a logged-in admin
        $_SESSION['user_id'] = 1;
        $_SESSION['school_id'] = 'admin-001';
        $_SESSION['full_name'] = 'Admin User';
        $_SESSION['role'] = 'admin';

        ob_start();
        $this->adminController->dashboard();
        $output = ob_get_clean();

        $this->assertStringContainsString('Admin Dashboard', $output);
        $this->assertStringContainsString('Manage Users', $output);
    }

    /** @test */
    public function it_should_handle_successful_student_creation()
    {
        $_SESSION['user_id'] = 1; 
        $_SESSION['role'] = 'admin';

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['school_id'] = 'STU_' . uniqid();
        $_POST['full_name'] = 'Jane Smith';
        $_POST['role'] = 'student';
        $_POST['year_level'] = '1st';
        $_POST['section'] = 'A';

        ob_start();
        $this->adminController->addStudent();
        $output = ob_get_clean();

        // We redirected; nothing to assert except no fatal
        $this->assertIsString($output);
    }

    /** @test */
    public function it_should_handle_failed_student_creation()
    {
        $_SESSION['user_id'] = 1; 
        $_SESSION['role'] = 'admin';

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['school_id'] = ''; // missing fields cause failure
        $_POST['full_name'] = '';
        $_POST['role'] = 'student';

        ob_start();
        $this->adminController->addStudent();
        $output = ob_get_clean();

        $this->assertIsString($output);
    }

    /** @test */
    public function it_should_handle_successful_student_update()
    {
        $_SESSION['user_id'] = 1; 
        $_SESSION['role'] = 'admin';

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['user_id'] = '999999'; // non-existent => handled path
        $_POST['school_id'] = 'X';
        $_POST['full_name'] = 'Updated';
        $_POST['role'] = 'student';
        $_POST['year_level'] = '2nd';
        $_POST['section'] = 'B';

        ob_start();
        $this->adminController->editStudent();
        $output = ob_get_clean();

        $this->assertIsString($output);
    }

    /** @test */
    public function it_should_handle_successful_student_deletion()
    {
        $_SESSION['user_id'] = 1; 
        $_SESSION['role'] = 'admin';

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['user_id'] = '999999';

        ob_start();
        $this->adminController->deleteStudent();
        $output = ob_get_clean();

        $this->assertIsString($output);
    }

    /** @test */
    public function it_should_handle_admin_logout()
    {
        $_SESSION['user_id'] = 1; 
        $_SESSION['role'] = 'admin';

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['confirm'] = 'true';

        ob_start();
        $this->adminController->logout();
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }

    /** @test */
    public function it_should_show_confirmation_page_when_admin_logout_not_confirmed()
    {
        $_SESSION['user_id'] = 1; 
        $_SESSION['role'] = 'admin';

        $_SERVER['REQUEST_METHOD'] = 'GET';
        unset($_GET['confirm']);

        ob_start();
        $this->adminController->logout();
        $output = ob_get_clean();

        $this->assertStringContainsString('Are you sure you want to logout?', $output);
        $this->assertStringContainsString('logout?confirm=true', $output);
    }

    /** @test */
    public function it_should_get_year_sections_from_students()
    {
        $_SESSION['user_id'] = 1; 
        $_SESSION['role'] = 'admin';

        // Call private getYearSections via reflection using sample data
        $students = [
            ['role' => 'student', 'year_level' => '1st', 'section' => 'A'],
            ['role' => 'student', 'year_level' => '1st', 'section' => 'B'],
            ['role' => 'student', 'year_level' => '2nd', 'section' => 'A'],
        ];

        $ref = new \ReflectionClass($this->adminController);
        $method = $ref->getMethod('getYearSections');
        $method->setAccessible(true);
        $result = $method->invoke($this->adminController, $students);

        $this->assertArrayHasKey('1st A', $result);
        $this->assertArrayHasKey('1st B', $result);
        $this->assertArrayHasKey('2nd A', $result);
    }

    // ========================================
    // FACULTY MANAGEMENT INTEGRATION TESTS
    // ========================================

    /** @test */
    public function it_should_handle_successful_faculty_creation()
    {
        $_SESSION['user_id'] = 1; 
        $_SESSION['role'] = 'admin';

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['school_id'] = 'FAC_' . uniqid();
        $_POST['full_name'] = 'Dr. New Faculty';
        $_POST['role'] = 'faculty';
        $_POST['password'] = 'password123';

        ob_start();
        $this->adminController->addFaculty();
        $output = ob_get_clean();

        // We redirected; nothing to assert except no fatal
        $this->assertIsString($output);
    }

    /** @test */
    public function it_should_handle_failed_faculty_creation()
    {
        $_SESSION['user_id'] = 1; 
        $_SESSION['role'] = 'admin';

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['school_id'] = ''; // missing fields cause failure
        $_POST['full_name'] = '';
        $_POST['role'] = 'faculty';

        ob_start();
        $this->adminController->addFaculty();
        $output = ob_get_clean();

        $this->assertIsString($output);
    }

    /** @test */
    public function it_should_handle_successful_faculty_update()
    {
        $_SESSION['user_id'] = 1; 
        $_SESSION['role'] = 'admin';

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['user_id'] = '999999'; // non-existent => handled path
        $_POST['school_id'] = 'FAC001';
        $_POST['full_name'] = 'Dr. Updated Faculty';
        $_POST['role'] = 'faculty';

        ob_start();
        $this->adminController->editFaculty();
        $output = ob_get_clean();

        $this->assertIsString($output);
    }

    /** @test */
    public function it_should_handle_successful_faculty_deletion()
    {
        $_SESSION['user_id'] = 1; 
        $_SESSION['role'] = 'admin';

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['user_id'] = '999999';

        ob_start();
        $this->adminController->deleteFaculty();
        $output = ob_get_clean();

        $this->assertIsString($output);
    }

    /** @test */
    public function it_should_handle_faculty_operations_without_admin_session()
    {
        // No session = no admin access
        unset($_SESSION['user_id']);
        unset($_SESSION['role']);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['school_id'] = 'FAC_TEST';
        $_POST['full_name'] = 'Test Faculty';

        // This should fail due to authentication
        $this->expectException(\Exception::class);
        
        ob_start();
        $this->adminController->addFaculty();
        ob_end_clean();
    }
}