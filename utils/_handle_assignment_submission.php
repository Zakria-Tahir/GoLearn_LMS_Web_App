<?php
include '_db_connect.php';
include '_globals.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header('location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['assignment_id'])) {
        header('location: ../course_details.php?error=' . urlencode('Assignment ID is required'));
        exit;
    }

    $assignment_id = intval($_POST['assignment_id']);
    $submission_text = $_POST['submission_text'];
    $submission_file = $_FILES['submission_file'];

    // Handle file upload
    $target_dir = "../" . SUBMITTED_ASSIGNMENT_PATH;
    $random_number = rand(1000, 9999); // Generate a random number
    $file_name = $random_number . "_" . basename($submission_file["name"]);
    $target_file = $target_dir . $file_name;
    $upload_ok = 1;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is a valid format
    $allowed_types = array("pdf", "doc", "docx", "txt");
    if (!in_array($file_type, $allowed_types)) {
        $upload_ok = 0;
        $error_message = "Only PDF, DOC, DOCX, and TXT files are allowed.";
    }

    // Check file size (limit to 5MB)
    if ($submission_file["size"] > 5000000) {
        $upload_ok = 0;
        $error_message = "File is too large.";
    }

    if ($upload_ok == 1) {
        // Fetch the old submission file path
        $sql = "SELECT submission_file FROM submitted_assignments WHERE assignment_id = ? AND student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $assignment_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $old_submission = $result->fetch_assoc();

        if ($old_submission) {
            $old_file_path = $target_dir . $old_submission['submission_file'];

            // Delete the old submission file if it exists
            if (file_exists($old_file_path)) {
                unlink($old_file_path);
            }

            // Update the assignment submission
            $sql = "UPDATE submitted_assignments SET submission_text = ?, submission_file = ? WHERE assignment_id = ? AND student_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssii', $submission_text, $file_name, $assignment_id, $user_id);
        } else {
            // Save the new assignment submission
            $sql = "INSERT INTO submitted_assignments (assignment_id, student_id, submission_text, submission_file) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iiss', $assignment_id, $user_id, $submission_text, $file_name);
        }

        if ($stmt->execute()) {
            move_uploaded_file($submission_file["tmp_name"], $target_file);
            header('location: ../course_details.php?course_id=' . $_POST['course_id'] . '&success=' . urlencode('Assignment submitted successfully'));
            exit;
        } else {
            $error_message = "There was an error saving your submission.";
        }
    }

    header('location: ../submit_assignment.php?assignment_id=' . $assignment_id . '&error=' . urlencode($error_message));
    exit;
}
