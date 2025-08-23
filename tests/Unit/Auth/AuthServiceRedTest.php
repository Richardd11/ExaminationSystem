<?php

namespace Tests\Unit\Auth;

use PHPUnit\Framework\TestCase;
use App\Auth\Services\AuthService;
use App\Auth\DAO\UserDAO;

class AuthServiceRedTest extends TestCase
{
    private AuthService $authService;
    private $userDAOMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userDAOMock = $this->createMock(UserDAO::class);

        $this->authService = new AuthService();
        $reflection = new \ReflectionClass($this->authService);
        $property = $reflection->getProperty('userDAO');
        $property->setAccessible(true);
        $property->setValue($this->authService, $this->userDAOMock);

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        $_SESSION = [];
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
    public function login_should_set_csrf_token_in_session_but_it_does_not_yet()
    {
        // Arrange
        $schoolId = 'RED_' . uniqid();
        $password = 'password123';
        $mockUser = [
            'user_id' => 1,
            'school_id' => $schoolId,
            'full_name' => 'John Doe',
            'role' => 'student',
            'password' => 'hashed_password',
        ];

        $this->userDAOMock
            ->expects($this->once())
            ->method('authenticate')
            ->with($schoolId, $password)
            ->willReturn($mockUser);

        // Act
        $result = $this->authService->login($schoolId, $password);

        // Assert (Expected to FAIL: CSRF token not implemented in AuthService)
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('csrf_token', $_SESSION);
        $this->assertNotEmpty($_SESSION['csrf_token']);
    }

    /** @test */
    public function login_should_record_last_login_timestamp_but_it_does_not_yet()
    {
        // Arrange
        $schoolId = 'RED_' . uniqid();
        $password = 'password123';
        $mockUser = [
            'user_id' => 1,
            'school_id' => $schoolId,
            'full_name' => 'John Doe',
            'role' => 'student',
            'password' => 'hashed_password',
        ];

        $this->userDAOMock
            ->expects($this->once())
            ->method('authenticate')
            ->with($schoolId, $password)
            ->willReturn($mockUser);

        // Act
        $result = $this->authService->login($schoolId, $password);

        // Assert (Expected to FAIL: last_login_at not tracked)
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('last_login_at', $_SESSION);
        $this->assertGreaterThan(0, $_SESSION['last_login_at']);
    }

    /** @test */
    public function login_should_reject_passwords_below_minimum_length_policy_but_it_does_not_yet()
    {
        // Arrange: password shorter than 8 chars should fail by policy
        $schoolId = 'RED_' . uniqid();
        $password = '12345';

        // DAO returns an authenticated user, but service should block due to policy
        $this->userDAOMock
            ->expects($this->once())
            ->method('authenticate')
            ->with($schoolId, $password)
            ->willReturn([
                'user_id' => 1,
                'school_id' => $schoolId,
                'full_name' => 'John Doe',
                'role' => 'student',
                'password' => 'hashed_password',
            ]);

        // Act
        $result = $this->authService->login($schoolId, $password);

        // Assert (Expected to FAIL: password policy not enforced)
        $this->assertFalse($result['success']);
        $this->assertSame('Password must be at least 8 characters.', $result['message']);
    }
}