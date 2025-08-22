<?php

namespace App\Interfaces;

interface UserServiceInterface
{
    /**
     * Create a new user
     */
    public function createUser($data);

    /**
     * Update user
     */
    public function updateUser($userId, $data);

    /**
     * Delete user
     */
    public function deleteUser($userId);

    /**
     * Get all users
     */
    public function getAllUsers();

    /**
     * Get users by role
     */
    public function getUsersByRole($role);

    /**
     * Get students by year and section
     */
    public function getStudentsByYearSection($yearLevel, $section);
}