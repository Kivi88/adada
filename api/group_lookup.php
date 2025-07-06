<?php
error_reporting(E_ERROR | E_PARSE);
require_once '../config/roblox_api.php';

header('Content-Type: application/json');

if (!isset($_GET['groupId']) || empty($_GET['groupId'])) {
    echo json_encode(['success' => false, 'error' => 'Group ID is required']);
    exit;
}

$groupId = $_GET['groupId'];
$roblox_api = new RobloxAPI();

$group_info = $roblox_api->getGroupInfo($groupId);

// Demo data for testing
if (!$group_info && $groupId == "123456") {
    $group_info = [
        'id' => 123456,
        'name' => 'Demo Grup',
        'description' => 'Bu demo bir gruptur',
        'memberCount' => 10,
        'publicEntryAllowed' => true
    ];
}

if (!$group_info) {
    echo json_encode(['success' => false, 'error' => 'Grup bulunamadı veya API erişimi yok']);
    exit;
}

$group_members = $roblox_api->getGroupMembers($groupId, 100);
$members = [];

// Demo members for testing
if ($groupId == "123456") {
    $members = [
        [
            'id' => 123456789,
            'username' => 'DemoKullanici1',
            'displayName' => 'Demo Kullanıcı 1',
            'role' => 'Üye',
            'rank' => 1
        ],
        [
            'id' => 987654321,
            'username' => 'DemoYonetici',
            'displayName' => 'Demo Yönetici',
            'role' => 'Yönetici',
            'rank' => 100
        ]
    ];
} else if ($group_members && isset($group_members['data'])) {
    foreach ($group_members['data'] as $member) {
        $members[] = [
            'id' => $member['user']['id'] ?? 0,
            'username' => $member['user']['username'] ?? 'Unknown',
            'displayName' => $member['user']['displayName'] ?? $member['user']['username'] ?? 'Unknown',
            'role' => $member['role']['name'] ?? 'No Role',
            'rank' => $member['role']['rank'] ?? 0
        ];
    }
}

echo json_encode([
    'success' => true,
    'group' => [
        'id' => $group_info['id'] ?? $groupId,
        'name' => $group_info['name'] ?? 'Unknown Group',
        'description' => $group_info['description'] ?? 'No description available',
        'memberCount' => $group_info['memberCount'] ?? 0,
        'isPublic' => $group_info['publicEntryAllowed'] ?? false
    ],
    'members' => $members
]);
?>
