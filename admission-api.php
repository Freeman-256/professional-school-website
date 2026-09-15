<?php
session_start();
require_once 'Database.php';
require_once 'UserModel.php';
require_once 'StudentModel.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $class_id = intval($_POST['class_id'] ?? 0);
    $parent_name = sanitize($_POST['parent_name'] ?? '');
    $parent_phone = sanitize($_POST['parent_phone'] ?? '');
    $parent_email = sanitize($_POST['parent_email'] ?? '');
    $dob = $_POST['dob'] ?? '';

    if (empty($name) || empty($email) || empty($dob)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Required fields missing']);
        exit();
    }

    $userModel = new User();
    $existingUser = $userModel->findByEmail($email);

    if ($existingUser) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit();
    }

    $admission_no = 'ADM-' . date('Ymd') . '-' . rand(1000, 9999);
    $temp_password = substr(md5($email), 0, 8);

    $userData = [
        'name' => $name,
        'email' => $email,
        'password' => $temp_password,
        'role' => 'student',
        'phone' => $phone,
        'address' => $address
    ];

    $userResult = $userModel->create($userData);

    if ($userResult['success']) {
        $studentModel = new Student();
        $studentData = [
            'user_id' => $userResult['id'],
            'admission_no' => $admission_no,
            'class_id' => $class_id,
            'parent_phone' => $parent_phone,
            'parent_email' => $parent_email,
            'dob' => $dob
        ];

        $studentResult = $studentModel->create($studentData);

        if ($studentResult['success']) {
            $admission_message = "Your admission has been successful!\nAdmission No: $admission_no\nTemporary Password: $temp_password";
            sendNotification($email, 'Admission Confirmation', $admission_message);
            
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Application submitted successfully. Check your email for details.', 'admission_no' => $admission_no]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error processing application']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating account']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
