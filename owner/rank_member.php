<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
require_once '../config/database.php';
require_once '../auth/session.php';
require_once '../config/roblox_api.php';

header('Content-Type: application/json');

requireOwner();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Sadece POST istekleri kabul edilir']);
    exit;
}

$userId = $_POST['userId'] ?? '';
$roleId = $_POST['roleId'] ?? '';
$groupId = $_POST['groupId'] ?? '';

if (!$userId || !$roleId || !$groupId) {
    echo json_encode(['success' => false, 'error' => 'Eksik parametreler']);
    exit;
}

// Verify owner has permission for this group
$stmt = $pdo->prepare("SELECT group_id FROM users WHERE id = ? AND role = 'owner'");
$stmt->execute([$_SESSION['user_id']]);
$owner = $stmt->fetch();

if (!$owner || $owner['group_id'] != $groupId) {
    echo json_encode(['success' => false, 'error' => 'Bu grup için yetkiniz yok']);
    exit;
}

$roblox_api = new RobloxAPI();
$result = $roblox_api->setUserRank($groupId, $userId, $roleId);

if ($result['success']) {
    echo json_encode(['success' => true, 'message' => 'Rütbe başarıyla değiştirildi']);
} else {
    echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Rütbe değiştirilemedi']);
}
?>