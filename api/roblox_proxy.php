<?php
require_once '../config/roblox_api.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$roblox_api = new RobloxAPI();

switch ($action) {
    case 'get_roles':
        $groupId = $_GET['groupId'] ?? '';
        if (empty($groupId)) {
            echo json_encode(['success' => false, 'error' => 'Group ID is required']);
            exit;
        }
        
        $url = 'https://groups.roblox.com/v1/groups/' . $groupId . '/roles';
        $options = [
            'http' => [
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n"
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            echo json_encode(['success' => false, 'error' => 'Failed to fetch roles']);
            exit;
        }
        
        $data = json_decode($result, true);
        echo json_encode(['success' => true, 'roles' => $data['roles'] ?? []]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}
?>
