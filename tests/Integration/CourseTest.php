<?php

use PHPUnit\Framework\TestCase;

class CourseTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $this->db = (new Database())->getConnection();
    }

    public function testCanCreateCourse()
    {
        $course_name = 'Test Course ' . uniqid();
        $course_code = 'TC' . rand(100, 999);
        $description = 'Test course description';
        $created_by = 1; // test_admin

        $query = "INSERT INTO courses (course_name, course_code, description, created_by) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([$course_name, $course_code, $description, $created_by]);

        $this->assertTrue($result);
        
        $course_id = $this->db->lastInsertId();
        $this->assertGreaterThan(0, $course_id);

        // Verify course was created
        $query = "SELECT * FROM courses WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$course_id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals($course_name, $course['course_name']);
        $this->assertEquals($course_code, $course['course_code']);
    }

    public function testCannotCreateCourseWithDuplicateCode()
    {
        $this->expectException(PDOException::class);
        
        $query = "INSERT INTO courses (course_name, course_code, description, created_by) 
                  VALUES ('Duplicate Course', 'CS401', 'Duplicate course', 1)";
        $this->db->exec($query);
    }

    public function testCanRetrieveCoursesWithProjectsCount()
    {
        $query = "SELECT c.*, COUNT(p.id) as project_count 
                  FROM courses c 
                  LEFT JOIN projects p ON c.id = p.course_id 
                  GROUP BY c.id 
                  ORDER BY c.course_name";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertIsArray($courses);
        $this->assertGreaterThan(0, count($courses));

        foreach ($courses as $course) {
            $this->assertArrayHasKey('course_name', $course);
            $this->assertArrayHasKey('course_code', $course);
            $this->assertArrayHasKey('project_count', $course);
        }
    }
}