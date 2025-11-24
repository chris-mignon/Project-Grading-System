<?php

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    private $database;
    private $db;

    protected function setUp(): void
    {
        $this->database = new Database();
        $this->db = $this->database->getConnection();
    }

    public function testDatabaseConnection()
    {
        $this->assertInstanceOf(PDO::class, $this->db);
    }

    public function testDatabaseConnectionIsActive()
    {
        $this->assertEquals('1', $this->db->query('SELECT 1')->fetchColumn());
    }

    public function testDatabaseHasUsersTable()
    {
        $stmt = $this->db->query("SHOW TABLES LIKE 'users'");
        $this->assertNotEmpty($stmt->fetch());
    }

    public function testDatabaseHasCoursesTable()
    {
        $stmt = $this->db->query("SHOW TABLES LIKE 'courses'");
        $this->assertNotEmpty($stmt->fetch());
    }
}