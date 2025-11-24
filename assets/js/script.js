$(document).ready(function() {
    // Evaluation modal handling
    $('.evaluate-btn').click(function() {
        const projectId = $(this).data('project-id');
        const projectName = $(this).data('project-name');
        
        $('#modal-project-name').text(projectName);
        $('#project-id').val(projectId);
        
        // Show loading state
        $('#criteria-container').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading evaluation criteria...</p>
            </div>
        `);
        
        // Load criteria via AJAX
        $.ajax({
            url: '../ajax/get_criteria.php',
            method: 'POST',
            data: { project_id: projectId },
            success: function(response) {
                $('#criteria-container').html(response);
                calculateTotalScore();
                
                // Load existing evaluation if any
                loadExistingEvaluation(projectId);
            },
            error: function() {
                $('#criteria-container').html('<div class="alert alert-danger">Error loading criteria</div>');
            }
        });
        
        $('#evaluationModal').modal('show');
    });
    
    function loadExistingEvaluation(projectId) {
        $.ajax({
            url: '../ajax/get_evaluation.php',
            method: 'POST',
            data: { project_id: projectId },
            success: function(response) {
                if (response.success && response.data) {
                    $('#feedback').val(response.data.feedback);
                    if (response.data.scores) {
                        $.each(response.data.scores, function(criterionId, score) {
                            $(`#score-${criterionId}`).val(score);
                        });
                        calculateTotalScore();
                    }
                }
            }
        });
    }
    
    // Calculate total score when scores change
    $(document).on('input', '.score-input', function() {
        const maxScore = $(this).attr('max');
        const currentScore = $(this).val();
        
        // Validate score doesn't exceed max
        if (parseFloat(currentScore) > parseFloat(maxScore)) {
            $(this).val(maxScore);
        }
        
        calculateTotalScore();
    });
    
    // Evaluation form submission
    $('#evaluation-form').submit(function(e) {
        e.preventDefault();
        
        const projectId = $('#project-id').val();
        const feedback = $('#feedback').val();
        const scores = {};
        
        $('.score-input').each(function() {
            const criterionId = $(this).data('criterion-id');
            const score = $(this).val() || 0;
            scores[criterionId] = score;
        });
        
        // Validate at least one score is provided
        if (Object.keys(scores).length === 0) {
            alert('Please provide scores for at least one criterion.');
            return;
        }
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Saving...');
        submitBtn.prop('disabled', true);
        
        $.ajax({
            url: '../ajax/save_evaluation.php',
            method: 'POST',
            data: {
                project_id: projectId,
                feedback: feedback,
                scores: scores
            },
            success: function(response) {
                if (response.success) {
                    $('#evaluationModal').modal('hide');
                    showToast('Evaluation saved successfully!', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast('Error: ' + response.message, 'error');
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                }
            },
            error: function() {
                showToast('Error saving evaluation', 'error');
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
            }
        });
    });
    
    function calculateTotalScore() {
        let total = 0;
        $('.score-input').each(function() {
            const score = parseFloat($(this).val()) || 0;
            total += score;
        });
        $('#total-score').text(total.toFixed(2));
        
        // Update color based on score
        const totalElement = $('#total-score');
        totalElement.removeClass('text-success text-warning text-danger');
        if (total >= 80) {
            totalElement.addClass('text-success');
        } else if (total >= 60) {
            totalElement.addClass('text-warning');
        } else {
            totalElement.addClass('text-danger');
        }
    }
    
    // Toast notification function
    function showToast(message, type = 'info') {
        const toastClass = {
            'success': 'bg-success',
            'error': 'bg-danger',
            'warning': 'bg-warning',
            'info': 'bg-info'
        }[type] || 'bg-info';
        
        const toastHtml = `
            <div class="toast align-items-center text-white ${toastClass} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        $('#toast-container').html(toastHtml);
        $('.toast').toast('show');
    }
    
    // Auto-check for URL parameters to open evaluation modal
    const urlParams = new URLSearchParams(window.location.search);
    const evaluateProjectId = urlParams.get('evaluate');
    if (evaluateProjectId) {
        // Find and trigger the evaluate button for this project
        $(`.evaluate-btn[data-project-id="${evaluateProjectId}"]`).click();
    }
});

// Add toast container to body if not exists
if ($('#toast-container').length === 0) {
    $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>');
}

// Submit evaluation
$('#submit-evaluation').click(function() {
    const projectId = $('#project-id').val();
    const feedback = $('#feedback').val();
    const scores = {};
    
    // Validate at least one score is provided
    let hasScores = false;
    $('.score-input').each(function() {
        const criterionId = $(this).data('criterion-id');
        const score = $(this).val() || 0;
        scores[criterionId] = score;
        
        if (parseFloat(score) > 0) {
            hasScores = true;
        }
    });
    
    if (!hasScores) {
        alert('Please provide scores for at least one criterion.');
        return;
    }
    
    // Validate feedback is provided
    if (!feedback.trim()) {
        if (!confirm('You haven\'t provided any feedback. Continue without feedback?')) {
            return;
        }
    }
    
    // Show loading state
    const submitBtn = $(this);
    const originalText = submitBtn.html();
    submitBtn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Saving...');
    submitBtn.prop('disabled', true);
    
    $.ajax({
        url: '../ajax/save_evaluation.php',
        method: 'POST',
        data: {
            project_id: projectId,
            feedback: feedback,
            scores: scores
        },
        success: function(response) {
            if (response.success) {
                $('#evaluationModal').modal('hide');
                $('#successModal').modal('show');
                
                // Reload page after a delay to show updated status
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                // Show specific error message from server
                const errorMessage = response.message || 'Unknown error occurred';
                showToast('Error: ' + errorMessage, 'error');
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            // Enhanced error handling
            let errorMessage = 'Network error occurred';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 0) {
                errorMessage = 'Network connection failed. Please check your internet connection.';
            } else if (xhr.status === 403) {
                errorMessage = 'Access denied. Please log in again.';
            } else if (xhr.status === 500) {
                errorMessage = 'Server error occurred. Please try again later.';
            } else if (error) {
                errorMessage = 'Error: ' + error;
            }
            
            showToast(errorMessage, 'error');
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
            
            // Log detailed error for debugging
            console.error('AJAX Error:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
        }
    });
});

// Toast notification function
function showToast(message, type = 'info') {
    const toastClass = {
        'success': 'bg-success',
        'error': 'bg-danger',
        'warning': 'bg-warning',
        'info': 'bg-info'
    }[type] || 'bg-info';
    
    // Ensure toast container exists
    if ($('#toast-container').length === 0) {
        $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
    }
    
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white ${toastClass} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    $('#toast-container').append(toastHtml);
    const toastElement = new bootstrap.Toast($('#' + toastId), {
        autohide: true,
        delay: type === 'error' ? 10000 : 5000
    });
    toastElement.show();
    
    // Remove toast from DOM after it's hidden
    $('#' + toastId).on('hidden.bs.toast', function() {
        $(this).remove();
    });
}