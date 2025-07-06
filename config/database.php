<?php
// Use PostgreSQL database from Replit
$host = $_ENV['PGHOST'] ?? 'ep-snowy-poetry-aebkllti.c-2.us-east-2.aws.neon.tech';
$dbname = $_ENV['PGDATABASE'] ?? 'neondb';
$username = $_ENV['PGUSER'] ?? 'neondb_owner';
$password = $_ENV['PGPASSWORD'] ?? 'npg_vZT7dDtRSU6k';
$port = $_ENV['PGPORT'] ?? '5432';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Create tables if they don't exist
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) NOT NULL CHECK (role IN ('admin', 'owner')),
            group_id INTEGER DEFAULT NULL,
            group_name VARCHAR(100) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS activity_logs (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL,
            action VARCHAR(50) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
        
        CREATE TABLE IF NOT EXISTS group_members_cache (
            id SERIAL PRIMARY KEY,
            group_id INTEGER NOT NULL,
            user_id BIGINT NOT NULL,
            username VARCHAR(50) NOT NULL,
            display_name VARCHAR(50),
            role_name VARCHAR(50),
            role_rank INTEGER,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE (group_id, user_id)
        );
        
        CREATE TABLE IF NOT EXISTS rank_history (
            id SERIAL PRIMARY KEY,
            group_id INTEGER NOT NULL,
            user_id BIGINT NOT NULL,
            old_role_name VARCHAR(50),
            old_role_rank INTEGER,
            new_role_name VARCHAR(50),
            new_role_rank INTEGER,
            changed_by INTEGER NOT NULL,
            changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE
        );
        
        CREATE TABLE IF NOT EXISTS system_settings (
            id SERIAL PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");
    
    // Insert default admin user (password: admin123)
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?) ON CONFLICT (username) DO NOTHING");
    $stmt->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
    
    // Insert default system settings
    $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON CONFLICT (setting_key) DO NOTHING");
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
    // Tables might already exist
}
?>
