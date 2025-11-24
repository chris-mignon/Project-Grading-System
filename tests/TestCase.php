<?php

abstract class TestCase extends PHPUnit\Framework\TestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = (new Database())->getConnection();
        $this->db->beginTransaction(); // Start transaction for each test
    }

    protected function tearDown(): void
    {
        $this->db->rollBack(); // Rollback transaction after each test
        parent::tearDown();
    }

    protected function createTestUser($role = 'lecturer')
    {
        $username = 'test_' . $role . '_' . uniqid();
        $password = password_hash('password', PASSWORD_DEFAULT);
        $full_name = 'Test ' . ucfirst($role);
        $email = $username . '@test.edu';

        $query = "INSERT INTO users (username, password, role, full_name, email, status) 
                  VALUES (?, ?, ?, ?, ?, 'active')";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$username, $password, $role, $full_name, $email]);

        return $this->db->lastInsertId();
    }

    protected function createTestCourse($created_by = 1)
    {
        $course_name = 'Test Course ' . uniqid();
        $course_code = 'TC' . rand(100, 999);

        $query = "INSERT INTO courses (course_name, course_code, description, created_by) 
                  VALUES (?, ?, 'Test course description', ?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$course_name, $course_code, $created_by]);

        return $this->db->lastInsertId();
    }
}

    