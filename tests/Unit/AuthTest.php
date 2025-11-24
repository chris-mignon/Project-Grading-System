<?php

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $this->db = (new Database())->getConnection();
        
        // Clear session before each test
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testIsLoggedInReturnsFalseWhenNotLoggedIn()
    {
        $this->assertFalse(isLoggedIn());
    }

    public function testIsLoggedInReturnsTrueWhenLoggedIn()
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'test_user';
        
        $this->assertTrue(isLoggedIn());
    }

    public function testIsAdminReturnsTrueForAdminUser()
    {
        $_SESSION['role'] = 'admin';
        $this->assertTrue(isAdmin());
    }

    public function testIsAdminReturnsFalseForLecturerUser()
    {
        $_SESSION['role'] = 'lecturer';
        $this->assertFalse(isAdmin());
    }

    public function testIsLecturerReturnsTrueForLecturerUser()
    {
        $_SESSION['role'] = 'lecturer';
        $this->assertTrue(isLecturer());
    }

    public function testIsLecturerReturnsFalseForAdminUser()
    {
        $_SESSION['role'] = 'admin';
        $this->assertFalse(isLecturer());
    }
}