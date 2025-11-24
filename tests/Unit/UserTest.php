<?php

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $this->db = (new Database())->getConnection();
    }

    public function testCanCreateUser()
    {
        $username = 'test_user_' . uniqid();
        $password = password_hash('test_password', PASSWORD_DEFAULT);
        $role = 'lecturer';
        $full_name = 'Test User';
        $email = 'test@example.com';

        $query = "INSERT INTO users (username, password, role, full_name, email, status) 
                  VALUES (?, ?, ?, ?, ?, 'active')";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([$username, $password, $role, $full_name, $email]);

        $this->assertTrue($result);
        $this->assertGreaterThan(0, $this->db->lastInsertId());
    }

    public function testCannotCreateDuplicateUsername()
    {
        $this->expectException(PDOException::class);
        
        $query = "INSERT INTO users (username, password, role, full_name, email, status) 
                  VALUES ('test_admin', 'password', 'lecturer', 'Duplicate', 'dup@test.edu', 'active')";
        $this->db->exec($query);
    }

    public function testCanRetrieveUserByUsername()
    {
        $username = 'test_admin';
        
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertIsArray($user);
        $this->assertEquals('test_admin', $user['username']);
        $this->assertEquals('admin', $user['role']);
    }

    public function testPasswordVerification()
    {
        $password = 'test_password';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $this->assertTrue(password_verify($password, $hash));
        $this->assertFalse(password_verify('wrong_password', $hash));
    }
}