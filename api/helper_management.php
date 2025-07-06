<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
require_once '../config/database.php';
require_once '../auth/session.php';

header('Content-Type: application/json');

requireOwner();

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add_helper':
        addHelper($input, $pdo);
        break;
    case 'get_helpers':
        getHelpers($_GET['groupId'], $pdo);
        break;
    case 'update_helper':
        updateHelper($input, $pdo);
        break;
    case 'remove_helper':
        removeHelper($input, $pdo);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

function addHelper($input, $pdo) {
    $groupId = $input['groupId'];
    $username = $input['username'];
    $permissions = $input['permissions'];
    
    // Validate input
    if (!$groupId || !$username || !$permissions) {
        echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
        return;
    }
    
    // Check if helper already exists
    $stmt = $pdo->prepare("SELECT id FROM group_helpers WHERE group_id = ? AND username = ?");
    $stmt->execute([$groupId, $username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Bu kullanıcı zaten yardımcı olarak eklenmiş']);
        return;
    }
    
    // Add helper
    $stmt = $pdo->prepare("INSERT INTO group_helpers (group_id, username, permissions, created_by, created_at) VALUES (?, ?, ?, ?, NOW())");
    $result = $stmt->execute([$groupId, $username, json_encode($permissions), $_SESSION['user_id']]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Yardımcı başarıyla eklendi']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Veritabanı hatası']);
    }
}

function getHelpers($groupId, $pdo) {
    if (!$groupId) {
        echo json_encode(['success' => false, 'error' => 'Group ID required']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT id, username, permissions, created_at FROM group_helpers WHERE group_id = ? ORDER BY created_at DESC");
    $stmt->execute([$groupId]);
    $helpers = $stmt->fetchAll();
    
    // Parse permissions JSON
    foreach ($helpers as &$helper) {
        $helper['permissions'] = json_decode($helper['permissions'], true) ?? [];
    }
    
    echo json_encode(['success' => true, 'helpers' => $helpers]);
}

function updateHelper($input, $pdo) {
    $id = $input['id'];
    $permissions = $input['permissions'];
    
    if (!$id || !$permissions) {
        echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
        return;
    }
    
    $stmt = $pdo->prepare("UPDATE group_helpers SET permissions = ?, updated_at = NOW() WHERE id = ?");
    $result = $stmt->execute([json_encode($permissions), $id]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Yardımcı yetkiler güncellendi']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Veritabanı hatası']);
    }
}

function removeHelper($input, $pdo) {
    $id = $input['id'];
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Helper ID required']);
        return;
    }
    
    $stmt = $pdo->prepare("DELETE FROM group_helpers WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Yardımcı kaldırıldı']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Veritabanı hatası']);
    }
}
?>