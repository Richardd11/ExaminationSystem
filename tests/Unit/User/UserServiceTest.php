<?php

namespace Tests\Unit\User;

use PHPUnit\Framework\TestCase;
use App\Services\User\UserService;
use App\Interfaces\UserDAOInterface;

class FakeUserDAO implements UserDAOInterface
{
    private array $users = [];
    private int $nextId = 1;

    public function findBySchoolId($school_id)
    {
        foreach ($this->users as $user) {
            if ($user['school_id'] === $school_id) { return $user; }
        }
        return null;
    }

    public function findById($user_id)
    {
        foreach ($this->users as $user) {
            if ($user['user_id'] === $user_id) { return $user; }
        }
        return null;
    }

    public function getAllUsers()
    {
        return array_values($this->users);
    }

    public function getUsersByRole($role)
    {
        return array_values(array_filter($this->users, fn($u) => $u['role'] === $role));
    }

    public function getStudentsByYearSection($year_level, $section)
    {
        return array_values(array_filter($this->users, function ($u) use ($year_level, $section) {
            return $u['role'] === 'student' && ($u['year_level'] ?? null) === $year_level && ($u['section'] ?? null) === $section;
        }));
    }

    public function create($data)
    {
        $user = $data;
        $user['user_id'] = $this->nextId++;
        $this->users[$user['user_id']] = $user;
        return $user['user_id'];
    }

    public function update($user_id, $data)
    {
        if (!isset($this->users[$user_id])) { return false; }
        $this->users[$user_id] = array_merge($this->users[$user_id], $data);
        return true;
    }

    public function delete($user_id)
    {
        if (!isset($this->users[$user_id])) { return false; }
        unset($this->users[$user_id]);
        return true;
    }

    public function authenticate($school_id, $password)
    {
        // Not used by UserService tests
        return false;
    }
}

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private FakeUserDAO $fakeDAO;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeDAO = new FakeUserDAO();
        $this->userService = new UserService($this->fakeDAO);
    }

    /** @test */
    public function it_should_create_user_successfully_with_valid_data()
    {
        $userData = [
            'school_id' => '2021-0001',
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A',
        ];

        $result = $this->userService->createUser($userData);
        $this->assertTrue($result['success']);
        $this->assertSame('User created successfully!', $result['message']);
        $this->assertIsInt($result['user_id']);
    }

    /** @test */
    public function it_should_fail_when_creating_user_with_existing_school_id()
    {
        $existing = [
            'school_id' => '2021-0001',
            'full_name' => 'Existing',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A',
        ];
        $this->fakeDAO->create($existing);

        $userData = [
            'school_id' => '2021-0001',
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A',
        ];

        $result = $this->userService->createUser($userData);
        $this->assertFalse($result['success']);
        $this->assertSame('School ID already exists.', $result['message']);
    }

    /** @test */
    public function it_should_fail_when_creating_user_with_missing_required_fields()
    {
        $userData = [
            'school_id' => '',
            'full_name' => '',
            'role' => 'student',
        ];

        $result = $this->userService->createUser($userData);
        $this->assertFalse($result['success']);
        $this->assertSame('School ID, full name, and role are required.', $result['message']);
    }

    /** @test */
    public function it_should_update_user_successfully_with_valid_data()
    {
        $id = $this->fakeDAO->create([
            'school_id' => '2021-0001',
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A',
        ]);

        $userData = [
            'school_id' => '2021-0001',
            'full_name' => 'John Updated',
            'role' => 'student',
            'year_level' => '2nd',
            'section' => 'B',
        ];

        $result = $this->userService->updateUser($id, $userData);
        $this->assertTrue($result['success']);
        $this->assertSame('User updated successfully!', $result['message']);
    }

    /** @test */
    public function it_should_fail_when_updating_nonexistent_user()
    {
        $result = $this->userService->updateUser(999, ['full_name' => 'Nope']);
        $this->assertFalse($result['success']);
        $this->assertSame('User not found.', $result['message']);
    }

    /** @test */
    public function it_should_delete_user_successfully()
    {
        $id = $this->fakeDAO->create([
            'school_id' => '2021-0001',
            'full_name' => 'John Doe',
            'role' => 'student',
        ]);

        $result = $this->userService->deleteUser($id);
        $this->assertTrue($result['success']);
        $this->assertSame('User deleted successfully!', $result['message']);
    }

    /** @test */
    public function it_should_fail_when_deleting_nonexistent_user()
    {
        $result = $this->userService->deleteUser(999);
        $this->assertFalse($result['success']);
        $this->assertSame('User not found.', $result['message']);
    }

    /** @test */
    public function it_should_get_all_users()
    {
        $this->fakeDAO->create(['school_id' => 'A', 'full_name' => 'A', 'role' => 'student']);
        $this->fakeDAO->create(['school_id' => 'B', 'full_name' => 'B', 'role' => 'faculty']);

        $result = $this->userService->getAllUsers();
        $this->assertCount(2, $result);
    }

    /** @test */
    public function it_should_get_users_by_role()
    {
        $this->fakeDAO->create(['school_id' => 'A', 'full_name' => 'A', 'role' => 'student']);
        $this->fakeDAO->create(['school_id' => 'B', 'full_name' => 'B', 'role' => 'student']);
        $this->fakeDAO->create(['school_id' => 'C', 'full_name' => 'C', 'role' => 'faculty']);

        $students = $this->userService->getUsersByRole('student');
        $this->assertCount(2, $students);
        foreach ($students as $s) { $this->assertSame('student', $s['role']); }
    }

    /** @test */
    public function it_should_get_students_by_year_and_section()
    {
        $this->fakeDAO->create(['school_id' => 'A', 'full_name' => 'A', 'role' => 'student', 'year_level' => '1st', 'section' => 'A']);
        $this->fakeDAO->create(['school_id' => 'B', 'full_name' => 'B', 'role' => 'student', 'year_level' => '1st', 'section' => 'B']);
        $this->fakeDAO->create(['school_id' => 'C', 'full_name' => 'C', 'role' => 'student', 'year_level' => '2nd', 'section' => 'A']);

        $result = $this->userService->getStudentsByYearSection('1st', 'A');
        $this->assertCount(1, $result);
        $this->assertSame('A', $result[0]['section']);
    }
}