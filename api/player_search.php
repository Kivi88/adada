<?php
require_once '../config/roblox_api.php';

header('Content-Type: application/json');

if (!isset($_GET['username']) || empty($_GET['username'])) {
    echo json_encode(['success' => false, 'error' => 'Username is required']);
    exit;
}

$username = $_GET['username'];
$roblox_api = new RobloxAPI();

$user_data = $roblox_api->getUserByUsername($username);
if (!$user_data) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

$user_groups = $roblox_api->getUserGroups($user_data['id']);
$groups = [];

if ($user_groups && isset($user_groups['data'])) {
    foreach ($user_groups['data'] as $group) {
        $groups[] = [
            'groupId' => $group['group']['id'],
            'groupName' => $group['group']['name'],
            'role' => $group['role']['name'],
            'rank' => $group['role']['rank']
        ];
    }
}

echo json_encode([
    'success' => true,
    'user' => [
        'id' => $user_data['id'],
        'username' => $user_data['name'],
        'displayName' => $user_data['displayName'],
        'description' => $user_data['description'],
        'created' => $user_data['created']
    ],
    'groups' => $groups
]);
?>
