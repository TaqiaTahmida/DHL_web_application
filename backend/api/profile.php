<?php
// backend/api/profile.php
require_once 'cors.php';
require_once '../config.php';
require_once '../jwt_helper.php';

header('Content-Type: application/json');

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$jwt = str_replace('Bearer ', '', $authHeader);

$user = JWT::decode($jwt);

if (!$user) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $profile = $stmt->fetch();
    
    if ($profile) {
        echo json_encode(["status" => "success", "data" => $profile]);
    } else {
        echo json_encode(["status" => "error", "message" => "Profile not found"]);
    }
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $name = $data['name'] ?? $user['name'];
    
    $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
    if ($stmt->execute([$name, $user['id']])) {
        echo json_encode(["status" => "success", "message" => "Profile updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update profile"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
}
?>
