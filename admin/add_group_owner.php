<?php
session_start();
require_once '../config/database.php';
require_once '../auth/session.php';

requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $roblox_cookie = trim($_POST['roblox_cookie']);
    $group_id = trim($_POST['group_id']);
    $group_name = trim($_POST['group_name']);
    
    if (empty($username) || empty($password) || empty($group_id) || empty($group_name)) {
        $error = "Tüm alanlar gerekli";
    } elseif (strlen($password) < 6) {
        $error = "Şifre en az 6 karakter olmalı";
    } else {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $error = "Bu kullanıcı adı zaten alınmış";
        } else {
            // Create group owner
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, group_id, group_name, roblox_cookie, created_at, updated_at) VALUES (?, ?, 'owner', ?, ?, ?, NOW(), NOW())");
            
            if ($stmt->execute([$username, $hashed_password, $group_id, $group_name, $roblox_cookie])) {
                $success = "Grup sahibi başarıyla eklendi!";
                // Clear form
                $_POST = [];
            } else {
                $error = "Grup sahibi eklenirken hata oluştu";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grup Sahibi Ekle - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-users"></i> Roblox Grup Yönetimi
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Admin Panel</a>
                <a class="nav-link" href="../index.php">Ana Sayfa</a>
                <a class="nav-link" href="../auth/logout.php">Çıkış</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h4><i class="fas fa-crown"></i> Grup Sahibi Ekle</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Kullanıcı Adı</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Şifre</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">En az 6 karakter</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="group_id" class="form-label">Grup ID</label>
                                <input type="number" class="form-control" id="group_id" name="group_id" 
                                       value="<?php echo htmlspecialchars($_POST['group_id'] ?? ''); ?>" 
                                       placeholder="123456789" required>
                                <div class="form-text">Yönetilecek Roblox grup ID'si</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="group_name" class="form-label">Grup Adı</label>
                                <input type="text" class="form-control" id="group_name" name="group_name" 
                                       value="<?php echo htmlspecialchars($_POST['group_name'] ?? ''); ?>" 
                                       placeholder="Grup Adı" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="roblox_cookie" class="form-label">Roblox Cookie (.ROBLOSECURITY)</label>
                                <textarea class="form-control" id="roblox_cookie" name="roblox_cookie" rows="3" 
                                          placeholder="_|WARNING:-DO-NOT-SHARE-THIS..."></textarea>
                                <div class="form-text">
                                    <small>Grup sahibinin Roblox hesabından alınan .ROBLOSECURITY cookie değeri</small>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Cookie Alma Talimatları:</h6>
                                <ol class="mb-0">
                                    <li>Grup sahibi Roblox.com'da giriş yapar</li>
                                    <li>F12 → Application → Cookies → roblox.com</li>
                                    <li>.ROBLOSECURITY değerini kopyalar</li>
                                    <li>Size ileterek buraya yazarsınız</li>
                                </ol>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i> Grup Sahibi Ekle
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="dashboard.php">Admin Panel'e Dön</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>