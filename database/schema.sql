-- Database schema for Roblox Group Management System

-- Create database
CREATE DATABASE IF NOT EXISTS roblox_group_management;
USE roblox_group_management;

-- Users table (Admin and Group Owners)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'owner') NOT NULL,
    group_id INT DEFAULT NULL,
    group_name VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Activity logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Group members cache table (for performance)
CREATE TABLE IF NOT EXISTS group_members_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id BIGINT NOT NULL,
    username VARCHAR(50) NOT NULL,
    display_name VARCHAR(50),
    role_name VARCHAR(50),
    role_rank INT,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_group_user (group_id, user_id),
    INDEX idx_group_id (group_id),
    INDEX idx_username (username)
);

-- Rank change history table
CREATE TABLE IF NOT EXISTS rank_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id BIGINT NOT NULL,
    old_role_name VARCHAR(50),
    old_role_rank INT,
    new_role_name VARCHAR(50),
    new_role_rank INT,
    changed_by INT NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_group_user (group_id, user_id),
    INDEX idx_changed_at (changed_at)
);

-- System settings table
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE username = username;

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value) VALUES 
('site_name', 'Roblox Grup Yönetim Sistemi'),
('cache_timeout', '300'),
('max_members_per_page', '100'),
('auto_refresh_interval', '60')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- Create indexes for better performance
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_group_id ON users(group_id);
CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id);
CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at);

-- Create triggers for automatic logging
DELIMITER //

CREATE TRIGGER IF NOT EXISTS log_user_changes
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF OLD.group_id != NEW.group_id OR OLD.group_name != NEW.group_name THEN
        INSERT INTO activity_logs (user_id, action, description) VALUES 
        (NEW.id, 'profile_update', CONCAT('Grup bilgileri güncellendi: ', OLD.group_name, ' -> ', NEW.group_name));
    END IF;
END//

CREATE TRIGGER IF NOT EXISTS log_rank_changes
AFTER INSERT ON rank_history
FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (user_id, action, description) VALUES 
    (NEW.changed_by, 'rank_change', CONCAT('Rütbe değiştirildi - Grup: ', NEW.group_id, ', Kullanıcı: ', NEW.user_id, ', Eski: ', NEW.old_role_name, ', Yeni: ', NEW.new_role_name));
END//

DELIMITER ;

-- Create view for user dashboard statistics
CREATE VIEW IF NOT EXISTS user_dashboard_stats AS
SELECT 
    u.id as user_id,
    u.username,
    u.role,
    u.group_id,
    u.group_name,
    COUNT(DISTINCT gmc.user_id) as total_members,
    COUNT(DISTINCT al.id) as total_activities,
    MAX(al.created_at) as last_activity
FROM users u
LEFT JOIN group_members_cache gmc ON u.group_id = gmc.group_id
LEFT JOIN activity_logs al ON u.id = al.user_id
WHERE u.role = 'owner'
GROUP BY u.id, u.username, u.role, u.group_id, u.group_name;

-- Create view for recent activities
CREATE VIEW IF NOT EXISTS recent_activities AS
SELECT 
    al.id,
    al.action,
    al.description,
    al.created_at,
    u.username as user_username,
    u.role as user_role
FROM activity_logs al
JOIN users u ON al.user_id = u.id
ORDER BY al.created_at DESC
LIMIT 50;

-- Grant permissions (if needed)
-- GRANT ALL PRIVILEGES ON roblox_group_management.* TO 'web_user'@'localhost';
-- FLUSH PRIVILEGES;
