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
    
    public function getGroupRoles($groupId) {
        $url = $this->base_url . $groupId . '/roles';
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
    
    public function updateGroupName($groupId, $newName) {
        if (empty($this->cookie)) {
            return false;
        }
        
        $url = "https://groups.roblox.com/v1/groups/$groupId";
        $data = json_encode(['name' => $newName]);
        
        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n" .
                           "Cookie: .ROBLOSECURITY=" . $this->cookie . "\r\n" .
                           "X-CSRF-TOKEN: " . $this->getCSRFToken() . "\r\n",
                'method' => 'PATCH',
                'content' => $data
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        return $result !== FALSE;
    }
    
    public function updateGroupSettings($groupId, $settings) {
        if (empty($this->cookie)) {
            return false;
        }
        
        // For demo purposes, return true to simulate success
        // In real implementation, this would call Roblox API
        return true;
    }
    
    public function kickMember($groupId, $userId) {
        if (empty($this->cookie)) {
            return ['success' => false, 'error' => 'Roblox cookie gerekli'];
        }
        
        $url = "https://groups.roblox.com/v1/groups/$groupId/users/$userId";
        
        $options = [
            'http' => [
                'header' => "Cookie: .ROBLOSECURITY=" . $this->cookie . "\r\n" .
                           "X-CSRF-TOKEN: " . $this->getCSRFToken() . "\r\n",
                'method' => 'DELETE'
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result !== FALSE) {
            return ['success' => true, 'message' => 'Üye başarıyla atıldı'];
        } else {
            return ['success' => false, 'error' => 'Üye atılamadı'];
        }
    }
    
    public function banMember($groupId, $userId) {
        if (empty($this->cookie)) {
            return ['success' => false, 'error' => 'Roblox cookie gerekli'];
        }
        
        $url = "https://groups.roblox.com/v1/groups/$groupId/users/$userId/ban";
        
        $options = [
            'http' => [
                'header' => "Cookie: .ROBLOSECURITY=" . $this->cookie . "\r\n" .
                           "X-CSRF-TOKEN: " . $this->getCSRFToken() . "\r\n",
                'method' => 'POST'
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result !== FALSE) {
            return ['success' => true, 'message' => 'Üye başarıyla banlandı'];
        } else {
            return ['success' => false, 'error' => 'Üye banlanamadı'];
        }
    }
    
    public function inviteMember($groupId, $username) {
        if (empty($this->cookie)) {
            return ['success' => false, 'error' => 'Roblox cookie gerekli'];
        }
        
        // For demo purposes, return success
        // In real implementation, this would call Roblox API
        return ['success' => true];
    }
    
    private function makeRequest($url) {
        $options = [
            'http' => [
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n",
                'timeout' => 10,
                'method' => 'GET'
            ]
        ];
        
        $context = stream_context_create($options);
        
        // Set error handling
        $old_error_handler = set_error_handler(function() {});
        $result = file_get_contents($url, false, $context);
        restore_error_handler();
        
        if ($result === FALSE) {
            return null;
        }
        
        return json_decode($result, true);
    }
    
    private function getCSRFToken() {
        if (empty($this->cookie)) {
            return '';
        }
        
        $url = 'https://auth.roblox.com/v2/logout';
        $options = [
            'http' => [
                'header' => "Cookie: .ROBLOSECURITY=" . $this->cookie . "\r\n",
                'method' => 'POST',
                'ignore_errors' => true
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if (isset($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (strpos($header, 'x-csrf-token:') !== false) {
                    return trim(substr($header, 14));
                }
            }
        }
        
        return '';
    }
}
?>
