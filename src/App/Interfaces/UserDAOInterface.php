<?php

namespace App\Interfaces;

interface UserDAOInterface
{
    /**
     * Find user by school ID
     */
    public function findBySchoolId($school_id);

    /**
     * Find user by ID
     */
    public function findById($user_id);

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
    public function getStudentsByYearSection($year_level, $section);

    /**
     * Create new user
     */
    public function create($data);

    /**
     * Update user
     */
    public function update($user_id, $data);

    /**
     * Delete user
     */
    public function delete($user_id);

    /**
     * Authenticate user
     */
    public function authenticate($school_id, $password);
}