<?php
class RobloxAPI {
    private $base_url = 'https://groups.roblox.com/v1/groups/';
    private $users_url = 'https://users.roblox.com/v1/users/';
    private $cookie;
    
    public function __construct() {
        $this->cookie = $_ENV['ROBLOX_COOKIE'] ?? '';
    }
    
    public function getGroupInfo($groupId) {
        $url = $this->base_url . $groupId;
        return $this->makeRequest($url);
    }
    
    public function getGroupMembers($groupId, $limit = 100) {
        $url = $this->base_url . $groupId . '/users?limit=' . $limit;
        return $this->makeRequest($url);
    }
    
    public function getUserByUsername($username) {
        $url = 'https://users.roblox.com/v1/usernames/users';
        $data = json_encode(['usernames' => [$username]]);
        
        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n",
                'method' => 'POST',
                'content' => $data
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            return null;
        }
        
        $response = json_decode($result, true);
        return isset($response['data'][0]) ? $response['data'][0] : null;
    }
    
    public function getUserGroups($userId) {
        $url = $this->users_url . $userId . '/groups/roles';
        return $this->makeRequest($url);
    }
    
    public function setUserRank($groupId, $userId, $roleId) {
        if (empty($this->cookie)) {
            return ['success' => false, 'error' => 'Roblox cookie gerekli'];
        }
        
        $url = $this->base_url . $groupId . '/users/' . $userId;
        $data = json_encode(['roleId' => $roleId]);
        
        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n" .
                           "Cookie: " . $this->cookie . "\r\n",
                'method' => 'PATCH',
                'content' => $data
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            return ['success' => false, 'error' => 'API isteği başarısız'];
        }
        
        return ['success' => true, 'data' => json_decode($result, true)];
    }
    
    private function makeRequest($url) {
        $options = [
            'http' => [
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n"
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            return null;
        }
        
        return json_decode($result, true);
    }
}
?>
