<?php
require_once "../includes/auth.php";
redirectIfNotAdmin();

require_once "../config/database.php";
$database = new Database();
$db = $database->getConnection();

// Handle form submission
if ($_POST && isset($_POST['course_name'])) {
    $course_name = trim($_POST['course_name']);
    $course_code = trim($_POST['course_code']);
    $description = trim($_POST['description']);
    
    // Validate input
    $errors = [];
    
    if (empty($course_name)) {
        $errors[] = "Course name is required";
    }
    
    if (empty($course_code)) {
        $errors[] = "Course code is required";
    }
    
    if (empty($errors)) {
        try {
            $query = "INSERT INTO courses (course_name, course_code, description, created_by) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $success = $stmt->execute([$course_name, $course_code, $description, $_SESSION['user_id']]);
            
            if ($success) {
                header("Location: courses.php?success=1");
                exit();
            } else {
                $errors[] = "Failed to add course to database";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $errors[] = "A course with this code already exists";
            } else {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    }
    
    // If there are errors, store them in session to display
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
    }
}

// Fetch courses
$query = "SELECT c.*, u.full_name as created_by_name FROM courses c 
          LEFT JOIN users u ON c.created_by = u.id 
          ORDER BY c.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Project Evaluation System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manage Courses</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                        <i class="bi bi-plus-circle"></i> Add Course
                    </button>
                </div>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Course added successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Display form errors if any -->
                <?php if (isset($_SESSION['form_errors']) && !empty($_SESSION['form_errors'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h5>Please fix the following errors:</h5>
                        <ul class="mb-0">
                            <?php foreach ($_SESSION['form_errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php 
                    // Clear the errors after displaying
                    unset($_SESSION['form_errors']);
                    unset($_SESSION['form_data']);
                    ?>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">All Courses</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($courses) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Course Code</th>
                                            <th>Course Name</th>
                                            <th>Description</th>
                                            <th>Created By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($courses as $course): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                                <td><?php echo htmlspecialchars($course['description']); ?></td>
                                                <td><?php echo htmlspecialchars($course['created_by_name']); ?></td>
                                                <td>
                                                    <a href="projects.php?course_id=<?php echo $course['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-folder"></i> Projects
                                                    </a>
                                                    <a href="rubric.php?course_id=<?php echo $course['id']; ?>" class="btn btn-sm btn-outline-info">
                                                        <i class="bi bi-clipboard-check"></i> Rubric
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-book display-1 text-muted"></i>
                                <h4 class="mt-3">No Courses Found</h4>
                                <p class="text-muted">Get started by adding your first course.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Course Modal -->
    <div class="modal fade" id="addCourseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addCourseForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="course_code" class="form-label">Course Code *</label>
                            <input type="text" class="form-control" id="course_code" name="course_code" 
                                   value="<?php echo isset($_SESSION['form_data']['course_code']) ? htmlspecialchars($_SESSION['form_data']['course_code']) : ''; ?>" 
                                   required>
                            <div class="form-text">Unique identifier for the course (e.g., CS101)</div>
                        </div>
                        <div class="mb-3">
                            <label for="course_name" class="form-label">Course Name *</label>
                            <input type="text" class="form-control" id="course_name" name="course_name" 
                                   value="<?php echo isset($_SESSION['form_data']['course_name']) ? htmlspecialchars($_SESSION['form_data']['course_name']) : ''; ?>" 
                                   required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo isset($_SESSION['form_data']['description']) ? htmlspecialchars($_SESSION['form_data']['description']) : ''; ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitCourseBtn">Add Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include "../includes/footer.php"; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Form validation
        $('#addCourseForm').submit(function(e) {
            const courseCode = $('#course_code').val().trim();
            const courseName = $('#course_name').val().trim();
            
            if (!courseCode) {
                alert('Please enter a course code');
                $('#course_code').focus();
                e.preventDefault();
                return false;
            }
            
            if (!courseName) {
                alert('Please enter a course name');
                $('#course_name').focus();
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            const submitBtn = $('#submitCourseBtn');
            submitBtn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Adding...');
            submitBtn.prop('disabled', true);
            
            return true;
        });
        
        // Auto-open modal if there were errors
        <?php if (isset($_SESSION['form_errors']) && !empty($_SESSION['form_errors'])): ?>
            $('#addCourseModal').modal('show');
        <?php endif; ?>
    });
    </script>
</body>
</html>