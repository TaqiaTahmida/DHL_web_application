<?php
// backend/api/auth.php
require_once 'cors.php';
require_once '../config.php';
require_once '../jwt_helper.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$action = $_GET['action'] ?? '';

if ($action === 'register') {
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if (strlen($password) < 6) {
        echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters long"]);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPassword]);
        $userId = $pdo->lastInsertId();

        $token = JWT::encode(["id" => $userId, "email" => $email, "name" => $name]);
        echo json_encode(["status" => "success", "token" => $token]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(["status" => "error", "message" => "Email already exists"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    }
} elseif ($action === 'login') {
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $token = JWT::encode(["id" => $user['id'], "email" => $user['email'], "name" => $user['name']]);
        echo json_encode(["status" => "success", "token" => $token, "user" => ["id" => $user['id'], "name" => $user['name'], "email" => $user['email']]]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid action"]);
}
?>
