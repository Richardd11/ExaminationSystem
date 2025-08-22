<?php

namespace Tests\Unit\DAO;

use PHPUnit\Framework\TestCase;
use App\DAO\Auth\UserDAO;
use PDO;
use PDOStatement;

class UserDAOTest extends TestCase
{
    private $pdoMock;
    private $pdoStatementMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mock PDO and PDOStatement
        $this->pdoMock = $this->createMock(PDO::class);
        $this->pdoStatementMock = $this->createMock(PDOStatement::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /** @test */
    public function it_should_find_user_by_school_id()
    {
        $schoolId = 'UT_SID_' . uniqid();
        $expectedUser = [
            'user_id' => 1,
            'school_id' => $schoolId,
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A'
        ];
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM users WHERE school_id = ?")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([$schoolId]);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('fetch')
            ->willReturn($expectedUser);

        $found = $userDAO->findBySchoolId($schoolId);
        
        $this->assertEquals($expectedUser, $found);
        $this->assertEquals($schoolId, $found['school_id']);
    }

    /** @test */
    public function it_should_return_false_when_user_not_found_by_school_id()
    {
        $schoolId = 'NON_EXIST_' . uniqid();
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM users WHERE school_id = ?")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([$schoolId]);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $found = $userDAO->findBySchoolId($schoolId);
        
        $this->assertFalse($found);
    }

    /** @test */
    public function it_should_find_user_by_id()
    {
        $userId = 1;
        $expectedUser = [
            'user_id' => $userId,
            'school_id' => 'UT_ID_' . uniqid(),
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A'
        ];
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM users WHERE user_id = ?")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([$userId]);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('fetch')
            ->willReturn($expectedUser);

        $found = $userDAO->findById($userId);
        
        $this->assertEquals($expectedUser, $found);
        $this->assertEquals($userId, (int)$found['user_id']);
    }

    /** @test */
    public function it_should_create_user_successfully()
    {
        $userData = [
            'school_id' => 'UT_CREATE_' . uniqid(),
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A'
        ];
        $expectedId = 123;
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
            
        $this->pdoMock
            ->expects($this->once())
            ->method('lastInsertId')
            ->willReturn($expectedId);

        $id = $userDAO->create($userData);
        
        $this->assertEquals($expectedId, $id);
    }

    /** @test */
    public function it_should_update_user_successfully()
    {
        $userId = 1;
        $userData = [
            'school_id' => 'UT_UPD_' . uniqid(),
            'full_name' => 'John Updated',
            'role' => 'student',
            'year_level' => '2nd',
            'section' => 'B'
        ];
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $ok = $userDAO->update($userId, $userData);
        
        $this->assertTrue($ok);
    }

    /** @test */
    public function it_should_delete_user_successfully()
    {
        $userId = 1;
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("DELETE FROM users WHERE user_id = ?")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([$userId])
            ->willReturn(true);

        $ok = $userDAO->delete($userId);
        
        $this->assertTrue($ok);
    }

    /** @test */
    public function it_should_get_all_users()
    {
        $expectedUsers = [
            ['user_id' => 1, 'school_id' => 'A_' . uniqid(), 'full_name' => 'A', 'role' => 'student'],
            ['user_id' => 2, 'school_id' => 'B_' . uniqid(), 'full_name' => 'B', 'role' => 'faculty']
        ];
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM users ORDER BY created_at DESC")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute');
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedUsers);

        $all = $userDAO->getAllUsers();
        
        $this->assertEquals($expectedUsers, $all);
        $this->assertCount(2, $all);
    }

    /** @test */
    public function it_should_get_users_by_role()
    {
        $role = 'student';
        $expectedUsers = [
            ['user_id' => 1, 'school_id' => 'S_' . uniqid(), 'full_name' => 'S1', 'role' => 'student'],
            ['user_id' => 2, 'school_id' => 'S_' . uniqid(), 'full_name' => 'S2', 'role' => 'student']
        ];
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM users WHERE role = ? ORDER BY full_name ASC")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([$role]);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedUsers);

        $students = $userDAO->getUsersByRole($role);
        
        $this->assertEquals($expectedUsers, $students);
        $this->assertCount(2, $students);
        foreach ($students as $s) { 
            $this->assertSame('student', $s['role']); 
        }
    }

    /** @test */
    public function it_should_get_students_by_year_and_section()
    {
        $yearLevel = '1st';
        $section = 'A';
        $expectedUsers = [
            ['user_id' => 1, 'school_id' => 'YS1_' . uniqid(), 'full_name' => 'A', 'role' => 'student', 'year_level' => '1st', 'section' => 'A'],
            ['user_id' => 2, 'school_id' => 'YS2_' . uniqid(), 'full_name' => 'B', 'role' => 'student', 'year_level' => '1st', 'section' => 'A']
        ];
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM users WHERE role = 'student' AND year_level = ? AND section = ? ORDER BY full_name ASC")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([$yearLevel, $section]);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedUsers);

        $res = $userDAO->getStudentsByYearSection($yearLevel, $section);
        
        $this->assertEquals($expectedUsers, $res);
        $this->assertNotEmpty($res);
        foreach ($res as $u) {
            $this->assertSame('1st', $u['year_level']);
            $this->assertSame('A', $u['section']);
        }
    }

    /** @test */
    public function it_should_authenticate_user_with_valid_credentials()
    {
        $schoolId = 'AUTH_' . uniqid();
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $user = [
            'user_id' => 1,
            'school_id' => $schoolId,
            'full_name' => 'John Doe',
            'role' => 'student',
            'password' => $hashedPassword
        ];
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Mock findBySchoolId to return the user
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM users WHERE school_id = ?")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([$schoolId]);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('fetch')
            ->willReturn($user);

        $result = $userDAO->authenticate($schoolId, $password);
        
        $this->assertEquals($user, $result);
    }

    /** @test */
    public function it_should_return_false_for_invalid_credentials()
    {
        $schoolId = 'INVALID_' . uniqid();
        $password = 'wrongpassword';
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Mock findBySchoolId to return false (user not found)
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM users WHERE school_id = ?")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([$schoolId]);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $result = $userDAO->authenticate($schoolId, $password);
        
        $this->assertFalse($result);
    }

    /** @test */
    public function it_should_handle_pdo_exception_gracefully()
    {
        $schoolId = 'EXCEPTION_' . uniqid();
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Mock PDO to throw an exception
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->willThrowException(new \PDOException('Database error'));

        $result = $userDAO->findBySchoolId($schoolId);
        
        $this->assertFalse($result);
    }

    /**
     * Helper method to create a UserDAO with a mock PDO
     */
    private function createUserDAOWithMockPDO()
    {
        // Create a new mock PDO and PDOStatement for each test
        $this->pdoMock = $this->createMock(PDO::class);
        $this->pdoStatementMock = $this->createMock(PDOStatement::class);
        
        // Use reflection to inject the mock PDO into UserDAO
        $userDAO = new UserDAO();
        $reflection = new \ReflectionClass($userDAO);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($userDAO, $this->pdoMock);
        
        return $userDAO;
    }
}