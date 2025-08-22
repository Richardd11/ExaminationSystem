<?php

namespace App\DAO\Auth;

use App\Config\Database;
use App\Interfaces\UserDAOInterface;
use PDO;
use PDOException;

class UserDAO implements UserDAOInterface
{
    private $db;
    private $table = 'users';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find user by school ID
     */
    public function findBySchoolId($school_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE school_id = ?");
            $stmt->execute([$school_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Find user by ID
     */
    public function findById($user_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
            $stmt->execute([$user_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get all users
     */
    public function getAllUsers()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get users by role
     */
    public function getUsersByRole($role)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role = ? ORDER BY full_name ASC");
            $stmt->execute([$role]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get students by year and section
     */
    public function getStudentsByYearSection($year_level, $section)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role = 'student' AND year_level = ? AND section = ? ORDER BY full_name ASC");
            $stmt->execute([$year_level, $section]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Create new user
     */
    public function create($data)
    {
        try {
            // Generate default password
            $plainPassword = $data['school_id'] . $data['full_name'];
            $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

            $sql = "INSERT INTO {$this->table} (school_id, full_name, password, role, year_level, section, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['school_id'],
                $data['full_name'],
                $hashedPassword,
                $data['role'],
                $data['role'] === 'student' ? $data['year_level'] : null,
                $data['role'] === 'student' ? $data['section'] : null
            ]);

            return $result ? $this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Update user
     */
    public function update($user_id, $data)
    {
        try {
            $sql = "UPDATE {$this->table} SET school_id = ?, full_name = ?, role = ?, year_level = ?, section = ?, updated_at = NOW() 
                    WHERE user_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['school_id'],
                $data['full_name'],
                $data['role'],
                $data['role'] === 'student' ? $data['year_level'] : null,
                $data['role'] === 'student' ? $data['section'] : null,
                $user_id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Delete user
     */
    public function delete($user_id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = ?");
            return $stmt->execute([$user_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Authenticate user
     */
    public function authenticate($school_id, $password)
    {
        $user = $this->findBySchoolId($school_id);
        
        if (!$user) {
            return false;
        }

        // Check if password is hashed (starts with $) or plain text
        if (strpos($user['password'], '$') === 0) {
            return password_verify($password, $user['password']) ? $user : false;
        } else {
            return $password === $user['password'] ? $user : false;
        }
    }
}