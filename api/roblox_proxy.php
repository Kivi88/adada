<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
require_once '../config/database.php';
require_once '../auth/session.php';
require_once '../config/roblox_api.php';

header('Content-Type: application/json');

requireOwner();

$action = $_GET['action'] ?? '';
$groupId = $_GET['groupId'] ?? '';

if (!$action) {
    echo json_encode(['success' => false, 'error' => 'Action belirtilmedi']);
    exit;
}

// Verify owner has permission for this group
$stmt = $pdo->prepare("SELECT group_id FROM users WHERE id = ? AND role = 'owner'");
$stmt->execute([$_SESSION['user_id']]);
$owner = $stmt->fetch();

if (!$owner || ($groupId && $owner['group_id'] != $groupId)) {
    echo json_encode(['success' => false, 'error' => 'Bu grup için yetkiniz yok']);
    exit;
}

$roblox_api = new RobloxAPI();

switch ($action) {
    case 'get_roles':
        if (!$groupId) {
            echo json_encode(['success' => false, 'error' => 'Grup ID gerekli']);
            exit;
        }
        
        $roles = $roblox_api->getGroupRoles($groupId);
        
        // Demo roles for testing
        if (!$roles && $groupId == "123456") {
            $roles = [
                ['id' => 1, 'name' => 'Üye', 'rank' => 1],
                ['id' => 2, 'name' => 'Deneyimli Üye', 'rank' => 50],
                ['id' => 3, 'name' => 'Moderatör', 'rank' => 75],
                ['id' => 4, 'name' => 'Yönetici', 'rank' => 100],
                ['id' => 5, 'name' => 'Kurucu', 'rank' => 255]
            ];
        }
        
        echo json_encode(['success' => true, 'roles' => $roles]);
        break;
        
    case 'get_group_info':
        if (!$groupId) {
            echo json_encode(['success' => false, 'error' => 'Grup ID gerekli']);
            exit;
        }
        
        $group_info = $roblox_api->getGroupInfo($groupId);
        echo json_encode(['success' => true, 'group' => $group_info]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Geçersiz action']);
        break;
}
?>