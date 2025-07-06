<?php
session_start();
require_once '../config/database.php';
require_once '../auth/session.php';
require_once '../config/roblox_api.php';

requireOwner();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$userId = $_POST['userId'] ?? '';
$roleId = $_POST['roleId'] ?? '';
$groupId = $_POST['groupId'] ?? '';

if (empty($userId) || empty($roleId) || empty($groupId)) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

// Verify owner has access to this group
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'owner'");
$stmt->execute([$_SESSION['user_id']]);
$owner = $stmt->fetch();

if (!$owner || $owner['group_id'] != $groupId) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access to this group']);
    exit;
}

$roblox_api = new RobloxAPI();
$result = $roblox_api->setUserRank($groupId, $userId, $roleId);

if ($result['success']) {
    // Log the activity
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description) VALUES (?, 'rank_change', ?)");
    $description = "Rütbe değiştirildi - Grup ID: $groupId, Kullanıcı ID: $userId, Yeni Rütbe ID: $roleId";
    $stmt->execute([$_SESSION['user_id'], $description]);
    
    echo json_encode(['success' => true, 'message' => 'Rütbe başarıyla değiştirildi']);
} else {
    echo json_encode(['success' => false, 'error' => $result['error']]);
}
?>
