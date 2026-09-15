<?php
session_start();
require_once 'Database.php';
require_once 'GradeModel.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkPermission('teacher');
    
    $student_id = intval($_POST['student_id'] ?? 0);
    $subject_id = intval($_POST['subject_id'] ?? 0);
    $term = intval($_POST['term'] ?? 1);
    $score = floatval($_POST['score'] ?? 0);
    $grade = getGradeLetter($score);

    if ($student_id === 0 || $subject_id === 0 || $score < 0 || $score > 100) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        exit();
    }

    $gradeModel = new Grade();
    $data = [
        'student_id' => $student_id,
        'subject_id' => $subject_id,
        'term' => $term,
        'score' => $score,
        'grade' => $grade,
        'teacher_id' => $_SESSION['user_id']
    ];

    $result = $gradeModel->create($data);
    
    if ($result['success']) {
        http_response_code(201);
        echo json_encode($result);
    } else {
        http_response_code(500);
        echo json_encode($result);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
