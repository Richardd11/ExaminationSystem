<?php

namespace Tests\Integration\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\Auth\AuthController;

class AuthControllerTest extends TestCase
{
    private AuthController $authController;

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
        
        $this->authController = new AuthController();
        $_SERVER['SCRIPT_NAME'] = '/index.php';
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
    public function it_should_handle_successful_login_request()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['school_id'] = 'NOUSER';
        $_POST['password'] = 'x';

        ob_start();
        $this->authController->login();
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('Login', $output);
    }

    /** @test */
    public function it_should_handle_failed_login_request()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['school_id'] = 'NOUSER';
        $_POST['password'] = 'wrong';

        ob_start();
        $this->authController->login();
        $output = ob_get_clean();

        $this->assertStringContainsString('Login', $output);
    }

    /** @test */
    public function it_should_handle_invalid_request_method()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        $this->authController->login();
        $output = ob_get_clean();
        $this->assertStringContainsString('Login', $output);
    }

    /** @test */
    public function it_should_handle_logout_request()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['confirm'] = 'true';
        $_SERVER['SCRIPT_NAME'] = '/subdir/index.php';

        // Capture headers with output buffering side effect (we cannot assert headers easily in CLI)
        ob_start();
        $this->authController->logout();
        $output = ob_get_clean();
        $this->assertSame('', $output);
    }

    /** @test */
    public function it_should_show_confirmation_page_when_logout_not_confirmed()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        unset($_GET['confirm']);

        ob_start();
        $this->authController->logout();
        $output = ob_get_clean();

        $this->assertStringContainsString('Are you sure you want to logout?', $output);
        $this->assertStringContainsString('logout?confirm=true', $output);
    }

    /** @test */
    public function it_should_display_login_page()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        $this->authController->showLogin();
        $output = ob_get_clean();

        $this->assertStringContainsString('Login', $output);
        $this->assertStringContainsString('School ID', $output);
        $this->assertStringContainsString('Password', $output);
    }

    /** @test */
    public function it_should_display_login_page_with_error_message()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION['error'] = 'Invalid credentials';

        ob_start();
        $this->authController->showLogin();
        $output = ob_get_clean();

        $this->assertStringContainsString('Invalid credentials', $output);
    }
}