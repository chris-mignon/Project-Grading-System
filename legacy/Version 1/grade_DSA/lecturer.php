<?php
session_start();
include 'db.php';

if ($_SESSION['user_role'] !== 'lecturer') {
    header('Location: dashboard.php');
    exit();
}

// Retrieve projects and their scores
$stmt = $pdo->prepare('
    SELECT p.*, g.score AS existing_score
    FROM projects p
    LEFT JOIN grades g ON p.id = g.project_id AND g.user_id = :user_id
');
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$projects = $stmt->fetchAll();

// Handle grading submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'grade') {
    $project_id = $_POST['project_id'];
    $problem_identification = $_POST['problem_identification'];
    $data_structure = $_POST['data_structure'];
    $algorithm_development = $_POST['algorithm_development'];
    $application_functionality = $_POST['application_functionality'];
    $presentation_delivery = $_POST['presentation_delivery'];
    
    $overall_score = $problem_identification + $data_structure + $algorithm_development + $application_functionality + $presentation_delivery;
    $user_id = $_SESSION['user_id'];

    // Check if the lecturer has graded this project before
    $stmt_check = $pdo->prepare('SELECT * FROM grades WHERE user_id = :user_id AND project_id = :project_id');
    $stmt_check->execute(['user_id' => $user_id, 'project_id' => $project_id]);
    $exists = $stmt_check->fetch();

    if ($exists) {
        // Update the existing grade
        $stmt = $pdo->prepare('UPDATE grades SET score = :score WHERE user_id = :user_id AND project_id = :project_id');
        $stmt->execute(['score' => $overall_score, 'user_id' => $user_id, 'project_id' => $project_id]);
    } else {
        // Insert new grade
        $stmt = $pdo->prepare('INSERT INTO grades (user_id, project_id, score) VALUES (:user_id, :project_id, :score)');
        $stmt->execute(['user_id' => $user_id, 'project_id' => $project_id, 'score' => $overall_score]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Grade submitted successfully!']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Data Structures and Algorithms</h1>

        <!-- Grade Projects Section -->
        <section class="mt-5">
            <h2>Grade Projects</h2>
            
            <table  class="table table-bordered">
                <thead>
                    <tr>
                        <th>Project ID</th>
                        <th>Project Title</th>
                        <th>Student Name</th>
                        <th>Graded</th>
                        <th>Score</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="project-list">
                    <?php foreach ($projects as $project): ?>
                        <tr id="project-<?php echo $project['id']; ?>">
                            <td><?php echo $project['id']; ?></td>
                            <td><?php echo $project['title']; ?></td>
                            <td><?php echo $project['description']; ?></td>
                            <td><?php echo ($project['existing_score'] !== null) ? 'Yes' : 'No'; ?></td>
                            <td><?php echo ($project['existing_score'] !== null) ? number_format($project['existing_score'], 2) : 'Not Graded'; ?></td>
                            <td>
                                <button class="btn btn-success btn-grade" data-id="<?php echo $project['id']; ?>" data-bs-toggle="modal" data-bs-target="#gradeProjectModal">Grade</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>

    <!-- Grade Project Modal -->
    <div class="modal fade" id="gradeProjectModal" tabindex="-1" aria-labelledby="gradeProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="gradeProjectModalLabel">Grade Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="gradeProjectForm">
                        <input type="hidden" id="gradeProjectId" name="project_id">
                        
                        <h5>Rubric Scoring (Score out of 10 each)</h5>
                        <div class="mb-3">
                            <label class="form-label">Problem Identification and Niche Served</label>
                            <input type="number" class="form-control" id="problemIdentification" name="problem_identification" min="0" max="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Data Structure Implementation</label>
                            <input type="number" class="form-control" id="dataStructure" name="data_structure" min="0" max="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Algorithm Development</label>
                            <input type="number" class="form-control" id="algorithmDevelopment" name="algorithm_development" min="0" max="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Application Functionality</label>
                            <input type="number" class="form-control" id="applicationFunctionality" name="application_functionality" min="0" max="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Presentation and Delivery</label>
                            <input type="number" class="form-control" id="presentationDelivery" name="presentation_delivery" min="0" max="10" required>
                        </div>
                        <button type="submit" class="btn btn-success">Submit Grade</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Load project data into grade modal
            $('.btn-grade').click(function() {
                var projectId = $(this).data('id');
                $('#gradeProjectId').val(projectId);
                
                // Reset scores
                $('#problemIdentification').val('');
                $('#dataStructure').val('');
                $('#algorithmDevelopment').val('');
                $('#applicationFunctionality').val('');
                $('#presentationDelivery').val('');
            });

            // Grade project via AJAX
            $('#gradeProjectForm').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize() + '&action=grade';
                $.ajax({
                    type: 'POST',
                    url: 'lecturer.php',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 'success') {
                            $('#gradeProjectModal').modal('hide');
                            alert(response.message);
                            location.reload(); // Optionally reload to update the project list
                        }
                    }
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>