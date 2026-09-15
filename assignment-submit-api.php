<?php
session_start();
require_once 'Database.php';
require_once 'AssignmentModel.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkPermission('student');
    
    $assignment_id = intval($_POST['assignment_id'] ?? 0);
    
    if (!isset($_FILES['submission']) || $_FILES['submission']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'File upload failed']);
        exit();
    }

    $result = uploadFile($_FILES['submission'], 'uploads/submissions/');
    
    if ($result['success']) {
        global $conn;
        $query = "INSERT INTO submissions (assignment_id, student_id, file_path, submitted_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iis', $assignment_id, $_SESSION['user_id'], $result['path']);
        
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Assignment submitted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error saving submission']);
        }
    } else {
        http_response_code(500);
        echo json_encode($result);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
