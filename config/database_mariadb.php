<?php
// MariaDB/MySQL konfigürasyonu - Plesk için
$host = 'localhost';
$dbname = 'roblox_group_mgmt';
$username = 'your_db_user';
$password = 'your_db_password';
$port = '3306';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Veritabanı bağlantı hatası.");
}

// Create tables if they don't exist (MariaDB/MySQL uyumlu)
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'owner') NOT NULL DEFAULT 'owner',
            group_id BIGINT DEFAULT NULL,
            group_name VARCHAR(100) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
        CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(50) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
        CREATE TABLE IF NOT EXISTS group_members_cache (
            id INT AUTO_INCREMENT PRIMARY KEY,
            group_id BIGINT NOT NULL,
            user_id BIGINT NOT NULL,
            username VARCHAR(50) NOT NULL,
            display_name VARCHAR(50),
            role_name VARCHAR(50),
            role_rank INT,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_group_user (group_id, user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
        CREATE TABLE IF NOT EXISTS rank_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            group_id BIGINT NOT NULL,
            user_id BIGINT NOT NULL,
            old_role_name VARCHAR(50),
            old_role_rank INT,
            new_role_name VARCHAR(50),
            new_role_rank INT,
            changed_by INT NOT NULL,
            changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
        CREATE TABLE IF NOT EXISTS system_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    // Insert default admin user (password: admin123)
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
    
    // Insert demo owner user (password: demo123)
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, role, group_id) VALUES (?, ?, ?, ?)");
    $stmt->execute(['demo_owner', password_hash('demo123', PASSWORD_DEFAULT), 'owner', 123456]);
    
    // Insert default system settings
    $stmt = $pdo->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES (?, ?)");
    $settings = [
        ['site_name', 'Roblox Grup Yönetim Sistemi'],
        ['cache_timeout', '300'],
        ['max_members_per_page', '100'],
        ['auto_refresh_interval', '60']
    ];
    foreach ($settings as $setting) {
        $stmt->execute($setting);
    }
    
    // Create indexes for better performance
    $pdo->exec("
        CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
        CREATE INDEX IF NOT EXISTS idx_users_group_id ON users(group_id);
        CREATE INDEX IF NOT EXISTS idx_activity_logs_user_id ON activity_logs(user_id);
        CREATE INDEX IF NOT EXISTS idx_activity_logs_created_at ON activity_logs(created_at);
        CREATE INDEX IF NOT EXISTS idx_group_members_cache_group_id ON group_members_cache(group_id);
        CREATE INDEX IF NOT EXISTS idx_group_members_cache_username ON group_members_cache(username);
        CREATE INDEX IF NOT EXISTS idx_rank_history_group_user ON rank_history(group_id, user_id);
        CREATE INDEX IF NOT EXISTS idx_rank_history_changed_at ON rank_history(changed_at);
    ");
    
} catch(PDOException $e) {
    // Log error but don't die - tables might already exist
    error_log("Database setup error: " . $e->getMessage());
}
?>