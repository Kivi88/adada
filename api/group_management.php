<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
require_once '../config/database.php';
require_once '../auth/session.php';
require_once '../config/roblox_api.php';

header('Content-Type: application/json');

requireOwner();

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'update_group_name':
        updateGroupName($input, $pdo);
        break;
    case 'get_group_roles':
        getGroupRoles($input, $pdo);
        break;
    case 'update_group_settings':
        updateGroupSettings($input, $pdo);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

function updateGroupName($input, $pdo) {
    $groupId = $input['groupId'];
    $newName = $input['newName'];
    
    if (!$groupId || !$newName) {
        echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
        return;
    }
    
    if (strlen($newName) < 3 || strlen($newName) > 50) {
        echo json_encode(['success' => false, 'error' => 'Grup adı 3-50 karakter arasında olmalıdır']);
        return;
    }
    
    // In real implementation, this would call Roblox API
    // For now, just simulate success
    $roblox_api = new RobloxAPI();
    $success = $roblox_api->updateGroupName($groupId, $newName);
    
    if ($success) {
        // Update database record
        $stmt = $pdo->prepare("UPDATE users SET group_name = ? WHERE group_id = ?");
        $stmt->execute([$newName, $groupId]);
        
        echo json_encode(['success' => true, 'message' => 'Grup adı başarıyla güncellendi']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Roblox API hatası - Grup adı güncellenemedi']);
    }
}

function getGroupRoles($input, $pdo) {
    $groupId = $input['groupId'];
    
    if (!$groupId) {
        echo json_encode(['success' => false, 'error' => 'Group ID required']);
        return;
    }
    
    $roblox_api = new RobloxAPI();
    $roles = $roblox_api->getGroupRoles($groupId);
    
    if ($roles) {
        echo json_encode(['success' => true, 'roles' => $roles]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Roller yüklenemedi']);
    }
}

function updateGroupSettings($input, $pdo) {
    $groupId = $input['groupId'];
    $settings = $input['settings'];
    
    if (!$groupId || !$settings) {
        echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
        return;
    }
    
    // In real implementation, this would call Roblox API
    // For now, just simulate success
    $roblox_api = new RobloxAPI();
    $success = $roblox_api->updateGroupSettings($groupId, $settings);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Grup ayarları güncellendi']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Roblox API hatası - Ayarlar güncellenemedi']);
    }
}
?>