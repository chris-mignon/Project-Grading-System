<?php

use PHPUnit\Framework\TestCase;

class EvaluationTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $this->db = (new Database())->getConnection();
    }

    public function testCanCreateEvaluation()
    {
        $project_id = 1;
        $lecturer_id = 3; // test_lecturer2
        $total_score = 88.5;
        $feedback = 'Excellent work with great implementation';

        // Start transaction
        $this->db->beginTransaction();

        try {
            // Create evaluation
            $query = "INSERT INTO evaluations (project_id, lecturer_id, total_score, feedback) 
                      VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$project_id, $lecturer_id, $total_score, $feedback]);
            
            $evaluation_id = $this->db->lastInsertId();

            // Add evaluation scores
            $scores = [
                [1, 28], // Functionality
                [2, 23], // Code Quality
                [3, 19], // Documentation
                [4, 18.5] // User Interface
            ];

            foreach ($scores as $score) {
                $query = "INSERT INTO evaluation_scores (evaluation_id, criterion_id, score) 
                          VALUES (?, ?, ?)";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$evaluation_id, $score[0], $score[1]]);
            }

            // Verify evaluation was created
            $query = "SELECT * FROM evaluations WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$evaluation_id]);
            $evaluation = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->assertIsArray($evaluation);
            $this->assertEquals($total_score, $evaluation['total_score']);
            $this->assertEquals($feedback, $evaluation['feedback']);

            // Verify scores were added
            $query = "SELECT COUNT(*) as score_count FROM evaluation_scores WHERE evaluation_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$evaluation_id]);
            $score_count = $stmt->fetch(PDO::FETCH_ASSOC)['score_count'];

            $this->assertEquals(4, $score_count);

        } finally {
            // Rollback to keep test database clean
            $this->db->rollBack();
        }
    }

    public function testCanCalculateAverageScore()
    {
        $project_id = 1;
        
        $query = "SELECT AVG(total_score) as average_score 
                  FROM evaluations 
                  WHERE project_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$project_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertIsArray($result);
        $this->assertNotNull($result['average_score']);
        $this->assertGreaterThan(0, $result['average_score']);
    }

    public function testCannotCreateEvaluationForNonExistentProject()
    {
        $this->expectException(PDOException::class);
        
        $query = "INSERT INTO evaluations (project_id, lecturer_id, total_score, feedback) 
                  VALUES (9999, 2, 85, 'Test feedback')";
        $this->db->exec($query);
    }
}