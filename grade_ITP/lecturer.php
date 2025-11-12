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
    FROM projects2 p
    LEFT JOIN grades2 g ON p.id = g.project_id AND g.user_id = :user_id
');
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$projects = $stmt->fetchAll();

// Handle grading submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'grade') {
    $project_id = $_POST['project_id'];
    $Syntax = $_POST['syntax'];
    $data_structure = $_POST['structure'];
    $algorithm_development = $_POST['operator'];
    $application_functionality = $_POST['modularity'];
    $presentation_delivery = $_POST['presentation'];
    
    $overall_score =  $Syntax + $data_structure + $algorithm_development + $application_functionality + $presentation_delivery;
    $user_id = $_SESSION['user_id'];

    // Check if the lecturer has graded this project before
    $stmt_check = $pdo->prepare('SELECT * FROM grades2 WHERE user_id = :user_id AND project_id = :project_id');
    $stmt_check->execute(['user_id' => $user_id, 'project_id' => $project_id]);
    $exists = $stmt_check->fetch();

    if ($exists) {
        // Update the existing grade
        $stmt = $pdo->prepare('UPDATE grades2 SET score = :score WHERE user_id = :user_id AND project_id = :project_id');
        $stmt->execute(['score' => $overall_score, 'user_id' => $user_id, 'project_id' => $project_id]);
    } else {
        // Insert new grade
        $stmt = $pdo->prepare('INSERT INTO grades2 (user_id, project_id, score) VALUES (:user_id, :project_id, :score)');
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
        <h1 class="text-center">Introduction to Programming</h1>

        <!-- Grade Projects Section -->
        <section class="mt-5">
            <h2>Grade Projects</h2>
            
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Project #</th>
                        <th>Project Title</th>
                        <th>Student Name </th>
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
                            <label class="form-label"><strong> Program Syntax</strong> <br>
                            The program should be well written using best practices and adhere to the standard syntax. 
                            </label>
                            <input type="number" class="form-control" id="Syntax" name="syntax" min="0" max="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>The use of programming structures </strong><br>
                            The correct and appropriate use of programming structures, including but not limited to Decision structures, Repetition structures and methods and collections.
                            </label>
                            <input type="number" class="form-control" id="dataStructure" name="structure" min="0" max="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong> Use of Operators</strong>
                            <br> The appropriate use of operators to perform calculations. This includes but are not limited to Arithmetic, Relational, Logical and concatenation operators.
                            </label>
                            <input type="number" class="form-control" id="algorithmDevelopment" name="operator" min="0" max="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>Modularity of programming code</strong>
                            <br>
                            The use of modules to structure and encapsulate programming code.
                            </label>
                            <input type="number" class="form-control" id="applicationFunctionality" name="modularity" min="0" max="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>Program documentation</strong> <br>
                            The program source code should be well commented on, and critical parts of the program should be well documented.</label>
                            <input type="number" class="form-control" id="presentationDelivery" name="presentation" min="0" max="10" required>
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
                $('#Syntax').val('');
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