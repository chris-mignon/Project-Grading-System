<?php
require_once "../config/database.php";
require_once "../includes/auth.php";

if (!isLoggedIn() || !isLecturer()) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users

$database = new Database();
$db = $database->getConnection();

// Get and validate input data
$project_id = isset($_POST['project_id']) ? trim($_POST['project_id']) : null;
$feedback = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';
$scores = isset($_POST['scores']) ? $_POST['scores'] : [];

// Validate required fields
if (!$project_id || empty($scores)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Project ID and scores are required'
    ]);
    exit();
}

// Validate project exists and belongs to a course
try {
    $query = "SELECT p.*, c.id as course_id FROM projects p 
              JOIN courses c ON p.course_id = c.id 
              WHERE p.id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Project not found or invalid project ID'
        ]);
        exit();
    }
} catch (Exception $e) {
    error_log("Database error in project validation: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Database error while validating project'
    ]);
    exit();
}

try {
    $db->beginTransaction();
    
    // Calculate total score with validation
    $total_score = 0;
    foreach ($scores as $criterion_id => $score) {
        $score_value = floatval($score);
        if ($score_value < 0) {
            throw new Exception("Score cannot be negative for criterion {$criterion_id}");
        }
        $total_score += $score_value;
    }
    
    // Check if evaluation already exists for this lecturer and project
    $query = "SELECT id FROM evaluations WHERE project_id = ? AND lecturer_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$project_id, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() > 0) {
        // Update existing evaluation
        $evaluation = $stmt->fetch(PDO::FETCH_ASSOC);
        $eval_id = $evaluation['id'];
        
        $query = "UPDATE evaluations SET total_score = ?, feedback = ?, evaluated_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($query);
        if (!$stmt->execute([$total_score, $feedback, $eval_id])) {
            throw new Exception("Failed to update evaluation");
        }
        
        // Delete existing scores
        $query = "DELETE FROM evaluation_scores WHERE evaluation_id = ?";
        $stmt = $db->prepare($query);
        if (!$stmt->execute([$eval_id])) {
            throw new Exception("Failed to delete existing scores");
        }
        
        $action = 'updated';
    } else {
        // Create new evaluation
        $query = "INSERT INTO evaluations (project_id, lecturer_id, total_score, feedback) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        if (!$stmt->execute([$project_id, $_SESSION['user_id'], $total_score, $feedback])) {
            throw new Exception("Failed to create new evaluation");
        }
        $eval_id = $db->lastInsertId();
        
        $action = 'created';
    }
    
    // Insert/update scores for each criterion
    foreach ($scores as $criterion_id => $score) {
        // Validate criterion belongs to the project's course
        $query = "SELECT id FROM rubric_criteria WHERE id = ? AND course_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$criterion_id, $project['course_id']]);
        
        if ($stmt->rowCount() > 0) {
            $query = "INSERT INTO evaluation_scores (evaluation_id, criterion_id, score) VALUES (?, ?, ?)";
            $stmt = $db->prepare($query);
            if (!$stmt->execute([$eval_id, $criterion_id, $score])) {
                throw new Exception("Failed to save score for criterion {$criterion_id}");
            }
        } else {
            error_log("Invalid criterion ID {$criterion_id} for course {$project['course_id']}");
        }
    }
    
    $db->commit();
    
    // Log the evaluation action
    error_log("Evaluation {$action} for project {$project_id} by lecturer {$_SESSION['user_id']}");
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Evaluation saved successfully',
        'total_score' => $total_score,
        'action' => $action
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    error_log("Error saving evaluation: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Error saving evaluation: ' . $e->getMessage()
    ]);
}
?>