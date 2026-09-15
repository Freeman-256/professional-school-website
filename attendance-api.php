<?php
session_start();
require_once 'Database.php';
require_once 'AttendanceModel.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkPermission('teacher');
    
    $student_id = intval($_POST['student_id'] ?? 0);
    $class_id = intval($_POST['class_id'] ?? 0);
    $attendance_date = $_POST['attendance_date'] ?? date('Y-m-d');
    $status = strtolower(sanitize($_POST['status'] ?? 'absent'));
    $remarks = sanitize($_POST['remarks'] ?? '');

    if (!in_array($status, ['present', 'absent', 'late'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid attendance status']);
        exit();
    }

    $attendanceModel = new Attendance();
    $data = [
        'student_id' => $student_id,
        'class_id' => $class_id,
        'attendance_date' => $attendance_date,
        'status' => $status,
        'remarks' => $remarks
    ];

    $result = $attendanceModel->mark($data);
    
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
