<?php
session_start();
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_destroy();
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Logout successful', 'redirect' => '/school-website/']);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
