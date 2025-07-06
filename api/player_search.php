<?php
error_reporting(E_ERROR | E_PARSE);
require_once '../config/roblox_api.php';

header('Content-Type: application/json');

if (!isset($_GET['username']) || empty($_GET['username'])) {
    echo json_encode(['success' => false, 'error' => 'Username is required']);
    exit;
}

$username = $_GET['username'];
$roblox_api = new RobloxAPI();

$user_data = $roblox_api->getUserByUsername($username);

// Demo data for testing
if (!$user_data && strtolower($username) == "demokullanici") {
    $user_data = [
        'id' => 123456789,
        'name' => 'DemoKullanici',
        'displayName' => 'Demo Kullanıcı',
        'description' => 'Bu demo bir kullanıcıdır',
        'created' => '2020-01-01T00:00:00.000Z'
    ];
}

if (!$user_data) {
    echo json_encode(['success' => false, 'error' => 'Kullanıcı bulunamadı veya API erişimi yok']);
    exit;
}

$user_groups = $roblox_api->getUserGroups($user_data['id']);
$groups = [];

// Demo groups for testing
if ($user_data['id'] == 123456789) {
    $groups = [
        [
            'groupId' => 123456,
            'groupName' => 'Demo Grup',
            'role' => 'Üye',
            'rank' => 1
        ],
        [
            'groupId' => 789123,
            'groupName' => 'Test Grubu',
            'role' => 'Yönetici',
            'rank' => 100
        ]
    ];
} else if ($user_groups && isset($user_groups['data'])) {
    foreach ($user_groups['data'] as $group) {
        $groups[] = [
            'groupId' => $group['group']['id'] ?? 0,
            'groupName' => $group['group']['name'] ?? 'Unknown Group',
            'role' => $group['role']['name'] ?? 'No Role',
            'rank' => $group['role']['rank'] ?? 0
        ];
    }
}

echo json_encode([
    'success' => true,
    'user' => [
        'id' => $user_data['id'] ?? 0,
        'username' => $user_data['name'] ?? $username,
        'displayName' => $user_data['displayName'] ?? $user_data['name'] ?? $username,
        'description' => $user_data['description'] ?? 'No description available',
        'created' => $user_data['created'] ?? '1970-01-01T00:00:00.000Z'
    ],
    'groups' => $groups
]);
?>
