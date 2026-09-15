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
    $password = $_POST['password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $admission_no = sanitize($_POST['admission_no'] ?? '');
    $class_id = intval($_POST['class_id'] ?? 0);
    $parent_phone = sanitize($_POST['parent_phone'] ?? '');
    $parent_email = sanitize($_POST['parent_email'] ?? '');
    $dob = $_POST['dob'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Name, email, and password are required']);
        exit();
    }

    $userModel = new User();
    $existingUser = $userModel->findByEmail($email);

    if ($existingUser) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit();
    }

    $userData = [
        'name' => $name,
        'email' => $email,
        'password' => $password,
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
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Registration successful. Please login.', 'redirect' => '/school-website/login']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error creating student record']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating user account']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
