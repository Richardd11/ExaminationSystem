<?php

namespace Tests\Unit\Auth;

use PHPUnit\Framework\TestCase;
use App\Services\Auth\AuthService;
use App\DAO\Auth\UserDAO;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private $userDAOMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a mock for UserDAO
        $this->userDAOMock = $this->createMock(UserDAO::class);
        
        // Use reflection to inject the mock into AuthService
        $this->authService = new AuthService();
        $reflection = new \ReflectionClass($this->authService);
        $property = $reflection->getProperty('userDAO');
        $property->setAccessible(true);
        $property->setValue($this->authService, $this->userDAOMock);
        
        // Ensure completely clean session state for each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        $_SESSION = [];
    }

    /**
     * Helper method to set up an authenticated user session
     */
    private function setupAuthenticatedSession($role = 'student')
    {
        // Ensure we have a clean session state
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        $_SESSION = [];
        
        // Start a fresh session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Set all required session data
        $_SESSION['user_id'] = 1;
        $_SESSION['school_id'] = '2021-0001';
        $_SESSION['full_name'] = 'John Doe';
        $_SESSION['role'] = $role;
        $_SESSION['year_level'] = '1st';
        $_SESSION['section'] = 'A';
    }

    protected function tearDown(): void
    {
        // Clean up session completely
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        $_SESSION = [];
        
        parent::tearDown();
    }

    /** @test */
    public function it_should_return_success_when_valid_credentials_provided()
    {
        $schoolId = 'UT_' . uniqid();
        $password = 'password123';
        
        // Mock user data
        $mockUser = [
            'user_id' => 1,
            'school_id' => $schoolId,
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A',
            'password' => 'hashed_password'
        ];
        
        // Set up mock expectations
        $this->userDAOMock
            ->expects($this->once())
            ->method('authenticate')
            ->with($schoolId, $password)
            ->willReturn($mockUser);

        $result = $this->authService->login($schoolId, $password);

        $this->assertTrue($result['success']);
        $this->assertSame('Login successful!', $result['message']);
        $this->assertEquals($schoolId, $result['user']['school_id']);
        $this->assertEquals('John Doe', $result['user']['full_name']);
        $this->assertEquals('student', $result['user']['role']);
    }

    /** @test */
    public function it_should_return_failure_when_invalid_credentials_provided()
    {
        // Set up mock expectations
        $this->userDAOMock
            ->expects($this->once())
            ->method('authenticate')
            ->with('NOPE', 'bad')
            ->willReturn(false);

        $result = $this->authService->login('NOPE', 'bad');
        
        $this->assertFalse($result['success']);
        $this->assertSame('Invalid School ID or password.', $result['message']);
    }

    /** @test */
    public function it_should_return_failure_when_empty_credentials_provided()
    {
        $result = $this->authService->login('', '');
        
        $this->assertFalse($result['success']);
        $this->assertSame('School ID and password are required.', $result['message']);
    }

    /** @test */
    public function it_should_return_failure_when_whitespace_only_credentials_provided()
    {
        $result = $this->authService->login('   ', '   ');
        
        $this->assertFalse($result['success']);
        $this->assertSame('School ID and password are required.', $result['message']);
    }

    /** @test */
    public function it_should_destroy_session_on_logout()
    {
        // Set up a simple authenticated session
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'student';
        
        // Verify session has data before logout
        $this->assertNotEmpty($_SESSION, 'Session should have data before logout');
        
        $result = $this->authService->logout();
        
        $this->assertTrue($result['success']);
        $this->assertSame('Logged out successfully.', $result['message']);
        
        // Verify session is destroyed - check that key session data is cleared
        $this->assertArrayNotHasKey('user_id', $_SESSION, 'user_id should be removed from session');
        $this->assertArrayNotHasKey('role', $_SESSION, 'role should be removed from session');
    }

    /** @test */
    public function it_should_return_current_user_when_session_exists()
    {
        // Set up an authenticated session
        $this->setupAuthenticatedSession();

        $result = $this->authService->getCurrentUser();
        
        $this->assertEquals(1, $result['user_id']);
        $this->assertEquals('2021-0001', $result['school_id']);
        $this->assertEquals('John Doe', $result['full_name']);
        $this->assertEquals('student', $result['role']);
        $this->assertEquals('1st', $result['year_level']);
        $this->assertEquals('A', $result['section']);
    }

    /** @test */
    public function it_should_return_null_when_no_session_exists()
    {
        // Ensure no session data exists
        unset($_SESSION['user_id'], $_SESSION['school_id'], $_SESSION['full_name'], $_SESSION['role'], $_SESSION['year_level'], $_SESSION['section']);
        
        $result = $this->authService->getCurrentUser();
        
        $this->assertNull($result);
    }

    /** @test */
    public function it_should_trim_whitespace_from_credentials()
    {
        $schoolId = '  UT_' . uniqid() . '  ';
        $password = '  password123  ';
        
        // Mock user data
        $mockUser = [
            'user_id' => 1,
            'school_id' => trim($schoolId),
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A',
            'password' => 'hashed_password'
        ];
        
        // Set up mock expectations - should receive trimmed values
        $this->userDAOMock
            ->expects($this->once())
            ->method('authenticate')
            ->with(trim($schoolId), trim($password))
            ->willReturn($mockUser);

        $result = $this->authService->login($schoolId, $password);

        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_should_require_authentication_for_protected_resources()
    {
        $result = $this->authService->requireAuth();
        
        $this->assertFalse($result['success']);
        $this->assertSame('Authentication required.', $result['message']);
        $this->assertEquals('/login', $result['redirect']);
    }

    /** @test */
    public function it_should_require_specific_role_for_role_protected_resources()
    {
        // Test with no session
        $result = $this->authService->requireRole('admin');
        $this->assertFalse($result['success']);
        $this->assertSame('Authentication required.', $result['message']);
        
        // Test with wrong role
        $this->setupAuthenticatedSession('student');
        $result = $this->authService->requireRole('admin');
        $this->assertFalse($result['success']);
        $this->assertSame('Insufficient permissions.', $result['message']);
        
        // Test with correct role - set up a fresh admin session
        // Clear session before testing correct role
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        $_SESSION = [];
        
        // Set up a fresh admin session
        $this->setupAuthenticatedSession('admin');
        
        $result = $this->authService->requireRole('admin');
        $this->assertTrue($result['success']);
        $this->assertSame('User has required role.', $result['message']);
    }
}