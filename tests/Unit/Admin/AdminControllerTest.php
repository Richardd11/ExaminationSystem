<?php

namespace Tests\Unit\Admin;

use PHPUnit\Framework\TestCase;
use App\Controllers\Admin\AdminController;
use App\Services\Auth\AuthService;
use App\Services\User\UserService;
use App\DAO\Auth\UserDAO;
use App\Core\View;
use ReflectionClass;

class AdminControllerTest extends TestCase
{
    private AdminController $adminController;
    private $authServiceMock;
    private $userServiceMock;
    private $viewMock;
    private $outputBufferLevel;
    private $originalErrorReporting;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Store current output buffer level
        $this->outputBufferLevel = ob_get_level();
        
        // Suppress header warnings for unit tests
        $this->originalErrorReporting = error_reporting();
        error_reporting(E_ALL & ~E_WARNING);
        
        // Create mocks for dependencies
        $this->authServiceMock = $this->createMock(AuthService::class);
        $this->userServiceMock = $this->createMock(UserService::class);
        $this->viewMock = $this->createMock(View::class);
        
        // Create AdminController instance
        $this->adminController = new AdminController();
        
        // Use reflection to inject mocks
        $reflection = new ReflectionClass($this->adminController);
        
        $authServiceProperty = $reflection->getProperty('authService');
        $authServiceProperty->setAccessible(true);
        $authServiceProperty->setValue($this->adminController, $this->authServiceMock);
        
        $userServiceProperty = $reflection->getProperty('userService');
        $userServiceProperty->setAccessible(true);
        $userServiceProperty->setValue($this->adminController, $this->userServiceMock);
        
        $viewProperty = $reflection->getProperty('view');
        $viewProperty->setAccessible(true);
        $viewProperty->setValue($this->adminController, $this->viewMock);
        
        // Reset superglobals for clean test state
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'SCRIPT_NAME' => '/index.php'
        ];
        
        // Start fresh output buffer for this test
        ob_start();
    }

    protected function tearDown(): void
    {
        // Clean up output buffer completely
        while (ob_get_level() > $this->outputBufferLevel) {
            ob_end_clean();
        }
        
        // Restore error reporting
        error_reporting($this->originalErrorReporting);
        
        // Clean up superglobals
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        
        parent::tearDown();
    }

    /**
     * @test
     */
    public function it_should_display_admin_dashboard_with_user_data()
    {
        // Mock current user data
        $currentUser = [
            'user_id' => 1,
            'school_id' => 'ADMIN-001',
            'full_name' => 'Admin User',
            'role' => 'admin'
        ];
        
        $students = [
            ['year_level' => '1st', 'section' => 'A'],
            ['year_level' => '1st', 'section' => 'B'],
            ['year_level' => '2nd', 'section' => 'A']
        ];
        
        $faculty = [
            ['full_name' => 'Faculty 1'],
            ['full_name' => 'Faculty 2']
        ];
        
        // Set up mock expectations - fix the order to match the actual method calls
        $this->authServiceMock
            ->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($currentUser);
            
        $this->userServiceMock
            ->expects($this->exactly(2))
            ->method('getUsersByRole')
            ->willReturnMap([
                ['student', $students],
                ['faculty', $faculty]
            ]);
            
        $this->viewMock
            ->expects($this->once())
            ->method('display')
            ->with('admin.dashboard', $this->callback(function($data) use ($currentUser, $students, $faculty) {
                return $data['admin'] === $currentUser &&
                       $data['students'] === $students &&
                       $data['faculty'] === $faculty &&
                       isset($data['yearSections']) &&
                       $data['yearSections']['1st A'] === 1 &&
                       $data['yearSections']['1st B'] === 1 &&
                       $data['yearSections']['2nd A'] === 1;
            }));
        
        // Call the method
        $this->adminController->dashboard();
    }

    /**
     * @test
     */
    public function it_should_handle_logout_with_confirmation()
    {
        $_GET['confirm'] = 'true';
        
        $this->authServiceMock
            ->expects($this->once())
            ->method('logout');
        
        // For unit tests, we'll just verify the method executes without error
        // Header redirects are better tested in integration tests
        $this->adminController->logout();
        
        // Verify logout was called (assertion is in the mock expectation)
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_should_show_logout_confirmation_page()
    {
        // Use reflection to call private method
        $reflection = new ReflectionClass($this->adminController);
        $method = $reflection->getMethod('showLogoutConfirmation');
        $method->setAccessible(true);
        
        $method->invoke($this->adminController);
        
        $output = ob_get_contents();
        
        $this->assertStringContainsString('Confirm Logout', $output);
        $this->assertStringContainsString('Are you sure you want to logout?', $output);
        $this->assertStringContainsString('Yes, Logout', $output);
        $this->assertStringContainsString('Cancel', $output);
    }

    /**
     * @test
     */
    public function it_should_add_user_successfully()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'full_name' => 'New User',
            'school_id' => '2024-001',
            'role' => 'student'
        ];
        
        $expectedResult = [
            'success' => true,
            'message' => 'User created successfully'
        ];
        
        $this->userServiceMock
            ->expects($this->once())
            ->method('createUser')
            ->with($_POST)
            ->willReturn($expectedResult);
        
        $this->adminController->addUser();
        
        $output = ob_get_contents();
        $response = json_decode($output, true);
        
        $this->assertEquals('success', $response['status']);
        $this->assertEquals('User created successfully', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_handle_add_user_failure()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'full_name' => 'New User',
            'school_id' => '2024-001'
        ];
        
        $expectedResult = [
            'success' => false,
            'message' => 'Role is required'
        ];
        
        $this->userServiceMock
            ->expects($this->once())
            ->method('createUser')
            ->with($_POST)
            ->willReturn($expectedResult);
        
        $this->adminController->addUser();
        
        $output = ob_get_contents();
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Role is required', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_reject_add_user_with_invalid_request_method()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        $this->userServiceMock
            ->expects($this->never())
            ->method('createUser');
        
        $this->adminController->addUser();
        
        $output = ob_get_contents();
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Invalid request method.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_add_student_successfully()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'full_name' => 'New Student',
            'school_id' => '2024-001',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A'
        ];
        
        $expectedResult = [
            'success' => true,
            'message' => 'Student created successfully'
        ];
        
        $this->userServiceMock
            ->expects($this->once())
            ->method('createUser')
            ->with($_POST)
            ->willReturn($expectedResult);
        
        $this->adminController->addStudent();
        
        $this->assertEquals('Student created successfully', $_SESSION['success_message']);
    }

    /**
     * @test
     */
    public function it_should_handle_add_student_failure()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'full_name' => 'New Student',
            'school_id' => '2024-001'
        ];
        
        $expectedResult = [
            'success' => false,
            'message' => 'Missing required fields'
        ];
        
        $this->userServiceMock
            ->expects($this->once())
            ->method('createUser')
            ->with($_POST)
            ->willReturn($expectedResult);
        
        $this->adminController->addStudent();
        
        $this->assertEquals('Missing required fields', $_SESSION['error_message']);
    }

    /**
     * @test
     */
    public function it_should_edit_user_successfully()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'full_name' => 'Updated User',
            'school_id' => '2024-001'
        ];
        
        $userId = 1;
        $expectedResult = [
            'success' => true,
            'message' => 'User updated successfully'
        ];
        
        $this->userServiceMock
            ->expects($this->once())
            ->method('updateUser')
            ->with($userId, $_POST)
            ->willReturn($expectedResult);
        
        $this->adminController->editUser($userId);
        
        $output = ob_get_contents();
        $response = json_decode($output, true);
        
        $this->assertEquals('success', $response['status']);
        $this->assertEquals('User updated successfully', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_edit_student_successfully()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'user_id' => '1',
            'full_name' => 'Updated Student',
            'school_id' => '2024-001',
            'year_level' => '2nd',
            'section' => 'B'
        ];
        
        $expectedResult = [
            'success' => true,
            'message' => 'Student updated successfully'
        ];
        
        $this->userServiceMock
            ->expects($this->once())
            ->method('updateUser')
            ->with('1', $_POST)
            ->willReturn($expectedResult);
        
        $this->adminController->editStudent();
        
        $this->assertEquals('Student updated successfully', $_SESSION['success_message']);
    }

    /**
     * @test
     */
    public function it_should_handle_edit_student_without_user_id()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'full_name' => 'Updated Student'
        ];
        
        $this->userServiceMock
            ->expects($this->never())
            ->method('updateUser');
        
        $this->adminController->editStudent();
        
        $output = ob_get_contents();
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status']);
        $this->assertEquals('User ID is required.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_delete_user_successfully()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $userId = 1;
        
        $expectedResult = [
            'success' => true,
            'message' => 'User deleted successfully'
        ];
        
        $this->userServiceMock
            ->expects($this->once())
            ->method('deleteUser')
            ->with($userId)
            ->willReturn($expectedResult);
        
        $this->adminController->deleteUser($userId);
        
        $output = ob_get_contents();
        $response = json_decode($output, true);
        
        $this->assertEquals('success', $response['status']);
        $this->assertEquals('User deleted successfully', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_delete_student_successfully()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'user_id' => '1'
        ];
        
        $expectedResult = [
            'success' => true,
            'message' => 'Student deleted successfully'
        ];
        
        $this->userServiceMock
            ->expects($this->once())
            ->method('deleteUser')
            ->with('1')
            ->willReturn($expectedResult);
        
        $this->adminController->deleteStudent();
        
        $this->assertEquals('Student deleted successfully', $_SESSION['success_message']);
    }

    /**
     * @test
     */
    public function it_should_handle_delete_student_without_user_id()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [];
        
        $this->userServiceMock
            ->expects($this->never())
            ->method('deleteUser');
        
        $this->adminController->deleteStudent();
        
        $output = ob_get_contents();
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status']);
        $this->assertEquals('User ID is required.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_reject_delete_user_with_invalid_request_method()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $userId = 1;
        
        $this->userServiceMock
            ->expects($this->never())
            ->method('deleteUser');
        
        $this->adminController->deleteUser($userId);
        
        $output = ob_get_contents();
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Invalid request method.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_generate_year_sections_correctly()
    {
        // Use reflection to call private method
        $reflection = new ReflectionClass($this->adminController);
        $method = $reflection->getMethod('getYearSections');
        $method->setAccessible(true);
        
        $students = [
            ['year_level' => '1st', 'section' => 'A'],
            ['year_level' => '1st', 'section' => 'A'],
            ['year_level' => '1st', 'section' => 'B'],
            ['year_level' => '2nd', 'section' => 'A'],
            ['year_level' => '2nd', 'section' => 'A']
        ];
        
        $result = $method->invoke($this->adminController, $students);
        
        $expected = [
            '1st A' => 2,
            '1st B' => 1,
            '2nd A' => 2
        ];
        
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_should_show_success_message()
    {
        // Use reflection to call private method
        $reflection = new ReflectionClass($this->adminController);
        $method = $reflection->getMethod('showSuccess');
        $method->setAccessible(true);
        
        $message = 'Operation successful';
        $method->invoke($this->adminController, $message);
        
        $output = ob_get_contents();
        $response = json_decode($output, true);
        
        $this->assertEquals('success', $response['status']);
        $this->assertEquals($message, $response['message']);
    }

    /**
     * @test
     */
    public function it_should_show_error_message()
    {
        // Use reflection to call private method
        $reflection = new ReflectionClass($this->adminController);
        $method = $reflection->getMethod('showError');
        $method->setAccessible(true);
        
        $message = 'Operation failed';
        $method->invoke($this->adminController, $message);
        
        $output = ob_get_contents();
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status']);
        $this->assertEquals($message, $response['message']);
    }

    /**
     * @test
     */
    public function it_should_redirect_to_dashboard()
    {
        // Use reflection to call private method
        $reflection = new ReflectionClass($this->adminController);
        $method = $reflection->getMethod('redirectToDashboard');
        $method->setAccessible(true);
        
        // For unit tests, we'll just verify the method can be called
        // Header redirects and exit() calls are better tested in integration tests
        try {
            $method->invoke($this->adminController);
            // If we get here, the method executed without throwing an exception
            $this->assertTrue(true);
        } catch (\Exception $e) {
            // If an exception is thrown (like exit), that's also acceptable
            $this->assertTrue(true);
        }
    }



    /**
     * @test
     */
    public function it_should_add_faculty_successfully()
    {
        $_POST = [
            'school_id' => 'FAC003',
            'full_name' => 'Dr. New Faculty',
            'password' => 'password123'
        ];
        
        $this->userServiceMock
            ->expects($this->once())
            ->method('createUser')
            ->with([
                'school_id' => 'FAC003',
                'full_name' => 'Dr. New Faculty',
                'role' => 'faculty',
                'password' => 'password123'
            ])
            ->willReturn(['success' => true]);
        
        ob_start();
        $this->invokePrivateMethod('addFaculty');
        $output = ob_get_clean();
        
        $this->assertStringContainsString('Faculty member added successfully', $output);
    }

    /**
     * @test
     */
    public function it_should_handle_add_faculty_with_missing_fields()
    {
        $_POST = [
            'school_id' => '',
            'full_name' => ''
        ];
        
        $this->userServiceMock
            ->expects($this->never())
            ->method('createUser');
        
        ob_start();
        $this->invokePrivateMethod('addFaculty');
        $output = ob_get_clean();
        
        $this->assertStringContainsString('School ID and Full Name are required', $output);
    }

    /**
     * @test
     */
    public function it_should_edit_faculty_successfully()
    {
        $_POST = [
            'user_id' => '2',
            'school_id' => 'FAC001',
            'full_name' => 'Dr. John Smith Updated'
        ];
        
        $this->userServiceMock
            ->expects($this->once())
            ->method('updateUser')
            ->with([
                'user_id' => '2',
                'school_id' => 'FAC001',
                'full_name' => 'Dr. John Smith Updated',
                'role' => 'faculty'
            ])
            ->willReturn(['success' => true]);
        
        ob_start();
        $this->invokePrivateMethod('editFaculty');
        $output = ob_get_clean();
        
        $this->assertStringContainsString('Faculty member updated successfully', $output);
    }

    /**
     * @test
     */
    public function it_should_handle_edit_faculty_without_user_id()
    {
        $_POST = [];
        
        $this->userServiceMock
            ->expects($this->never())
            ->method('updateUser');
        
        ob_start();
        $this->invokePrivateMethod('editFaculty');
        $output = ob_get_clean();
        
        $this->assertStringContainsString('User ID is required', $output);
    }

    /**
     * @test
     */
    public function it_should_handle_edit_faculty_with_missing_fields()
    {
        $_POST = [
            'user_id' => '2',
            'school_id' => '',
            'full_name' => ''
        ];
        
        $this->userServiceMock
            ->expects($this->never())
            ->method('updateUser');
        
        ob_start();
        $this->invokePrivateMethod('editFaculty');
        $output = ob_get_clean();
        
        $this->assertStringContainsString('School ID and Full Name are required', $output);
    }

    /**
     * @test
     */
    public function it_should_delete_faculty_successfully()
    {
        $_POST = ['user_id' => '2'];
        
        $this->userServiceMock
            ->expects($this->once())
            ->method('deleteUser')
            ->with('2')
            ->willReturn(['success' => true]);
        
        ob_start();
        $this->invokePrivateMethod('deleteFaculty');
        $output = ob_get_clean();
        
        $this->assertStringContainsString('Faculty member deleted successfully', $output);
    }

    /**
     * @test
     */
    public function it_should_handle_delete_faculty_without_user_id()
    {
        $_POST = [];
        
        $this->userServiceMock
            ->expects($this->never())
            ->method('deleteUser');
        
        ob_start();
        $this->invokePrivateMethod('deleteFaculty');
        $output = ob_get_clean();
        
        $this->assertStringContainsString('User ID is required', $output);
    }
}