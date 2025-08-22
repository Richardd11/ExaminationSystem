<?php

namespace App\Services\User;

use App\Interfaces\UserServiceInterface;
use App\Interfaces\UserDAOInterface;

class UserService implements UserServiceInterface
{
    private $userDAO;

    public function __construct(UserDAOInterface $userDAO)
    {
        $this->userDAO = $userDAO;
    }

    /**
     * Create a new user
     */
    public function createUser($data)
    {
        // Validate required fields
        if (empty($data['school_id']) || empty($data['full_name']) || empty($data['role'])) {
            return [
                'success' => false,
                'message' => 'School ID, full name, and role are required.'
            ];
        }

        // Check if school_id already exists
        $existingUser = $this->userDAO->findBySchoolId($data['school_id']);
        if ($existingUser) {
            return [
                'success' => false,
                'message' => 'School ID already exists.'
            ];
        }

        // Validate role
        $validRoles = ['admin', 'faculty', 'student'];
        if (!in_array($data['role'], $validRoles)) {
            return [
                'success' => false,
                'message' => 'Invalid role. Must be admin, faculty, or student.'
            ];
        }

        // Validate student-specific fields
        if ($data['role'] === 'student') {
            if (empty($data['year_level']) || empty($data['section'])) {
                return [
                    'success' => false,
                    'message' => 'Year level and section are required for students.'
                ];
            }
        }

        // Create user
        $userId = $this->userDAO->create($data);
        
        if ($userId) {
            return [
                'success' => true,
                'message' => 'User created successfully!',
                'user_id' => $userId
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to create user.'
            ];
        }
    }

    /**
     * Update user
     */
    public function updateUser($userId, $data)
    {
        // Check if user exists
        $existingUser = $this->userDAO->findById($userId);
        if (!$existingUser) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }

        // Check if school_id is being changed and if it already exists
        if (isset($data['school_id']) && $data['school_id'] !== $existingUser['school_id']) {
            $userWithSchoolId = $this->userDAO->findBySchoolId($data['school_id']);
            if ($userWithSchoolId) {
                return [
                    'success' => false,
                    'message' => 'School ID already exists.'
                ];
            }
        }

        // Update user
        $result = $this->userDAO->update($userId, $data);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'User updated successfully!'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to update user.'
            ];
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($userId)
    {
        // Check if user exists
        $existingUser = $this->userDAO->findById($userId);
        if (!$existingUser) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }

        // Delete user
        $result = $this->userDAO->delete($userId);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'User deleted successfully!'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to delete user.'
            ];
        }
    }

    /**
     * Get all users
     */
    public function getAllUsers()
    {
        return $this->userDAO->getAllUsers();
    }

    /**
     * Get users by role
     */
    public function getUsersByRole($role)
    {
        return $this->userDAO->getUsersByRole($role);
    }

    /**
     * Get students by year and section
     */
    public function getStudentsByYearSection($yearLevel, $section)
    {
        return $this->userDAO->getStudentsByYearSection($yearLevel, $section);
    }
}