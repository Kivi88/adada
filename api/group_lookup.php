<?php
require_once '../config/roblox_api.php';

header('Content-Type: application/json');

if (!isset($_GET['groupId']) || empty($_GET['groupId'])) {
    echo json_encode(['success' => false, 'error' => 'Group ID is required']);
    exit;
}

$groupId = $_GET['groupId'];
$roblox_api = new RobloxAPI();

$group_info = $roblox_api->getGroupInfo($groupId);
if (!$group_info) {
    echo json_encode(['success' => false, 'error' => 'Group not found']);
    exit;
}

$group_members = $roblox_api->getGroupMembers($groupId, 100);
$members = [];

if ($group_members && isset($group_members['data'])) {
    foreach ($group_members['data'] as $member) {
        $members[] = [
            'id' => $member['user']['id'],
            'username' => $member['user']['username'],
            'displayName' => $member['user']['displayName'],
            'role' => $member['role']['name'],
            'rank' => $member['role']['rank']
        ];
    }
}

echo json_encode([
    'success' => true,
    'group' => [
        'id' => $group_info['id'],
        'name' => $group_info['name'],
        'description' => $group_info['description'],
        'memberCount' => $group_info['memberCount'],
        'isPublic' => $group_info['publicEntryAllowed']
    ],
    'members' => $members
]);
?>
