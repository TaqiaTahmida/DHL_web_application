<?php
// backend/api/incidents.php
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
    $stmt = $pdo->query("SELECT * FROM incidents ORDER BY created_at DESC");
    $incidents = $stmt->fetchAll();
    echo json_encode(["status" => "success", "data" => $incidents]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Auto-generate a unique ref ID like INC-2026-XXXX if not provided by AI
    $ref_id = $data['incident_ref_id'] ?? ('INC-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5)));

    $issue_summary = $data['issue_summary'] ?? '';
    $department = $data['department'] ?? '';
    $priority = $data['priority'] ?? '';
    $recommended_action = $data['recommended_action'] ?? '';
    $progress_history = $data['progress_history'] ?? 'Incident reported.';
    
    $stmt = $pdo->prepare("INSERT INTO incidents (incident_ref_id, issue_summary, department, priority, recommended_action, progress_history) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$ref_id, $issue_summary, $department, $priority, $recommended_action, $progress_history])) {
        echo json_encode(["status" => "success", "message" => "Incident created", "incident_ref_id" => $ref_id]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to create incident"]);
    }
} elseif ($method === 'PUT') {
    // Used to update progress
    $data = json_decode(file_get_contents("php://input"), true);
    $incident_ref_id = $data['incident_ref_id'] ?? '';
    $new_progress = $data['new_progress'] ?? '';
    $status = $data['status'] ?? null; // Optional status update

    if (!$incident_ref_id || !$new_progress) {
        echo json_encode(["status" => "error", "message" => "Missing incident_ref_id or new_progress"]);
        exit;
    }

    // Append to existing progress
    $stmt = $pdo->prepare("SELECT progress_history FROM incidents WHERE incident_ref_id = ?");
    $stmt->execute([$incident_ref_id]);
    $incident = $stmt->fetch();

    if ($incident) {
        $timestamp = date('Y-m-d H:i:s');
        $updated_progress = $incident['progress_history'] . "\n[$timestamp] $new_progress";

        if ($status) {
            $updateStmt = $pdo->prepare("UPDATE incidents SET progress_history = ?, status = ? WHERE incident_ref_id = ?");
            $updateStmt->execute([$updated_progress, $status, $incident_ref_id]);
        } else {
            $updateStmt = $pdo->prepare("UPDATE incidents SET progress_history = ? WHERE incident_ref_id = ?");
            $updateStmt->execute([$updated_progress, $incident_ref_id]);
        }
        echo json_encode(["status" => "success", "message" => "Progress updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Incident not found"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
}
?>
