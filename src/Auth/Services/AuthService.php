<?php

namespace App\Auth\Services;

use App\Auth\DAO\UserDAO;

class AuthService
{
    private $userDAO;

    public function __construct(UserDAO $userDAO = null)
    {
        $this->userDAO = $userDAO ?? new UserDAO();
    }

    public function login($school_id, $password)
    {
        return [
            'success' => false,
            'message' => 'Not implemented'
        ];
    }

    public function logout()
    {
        return [
            'success' => false,
            'message' => 'Not implemented'
        ];
    }

    public function isAuthenticated()
    {
        return false;
    }

    public function getCurrentUser()
    {
        return null;
    }

    public function requireAuth()
    {
        return [
            'success' => false,
            'message' => 'Not implemented'
        ];
    }

    public function requireRole($requiredRole)
    {
        return [
            'success' => false,
            'message' => 'Not implemented'
        ];
    }
}