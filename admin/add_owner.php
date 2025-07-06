<?php
session_start();
require_once '../config/database.php';
require_once '../auth/session.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $group_id = $_POST['group_id'];
    $group_name = trim($_POST['group_name']);
    
    if (empty($username) || empty($password) || empty($group_id) || empty($group_name)) {
        $error = "Tüm alanlar gereklidir";
    } else {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "Bu kullanıcı adı zaten kullanılıyor";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, group_id, group_name) VALUES (?, ?, 'owner', ?, ?)");
            if ($stmt->execute([$username, $hashed_password, $group_id, $group_name])) {
                $success = "Grup sahibi başarıyla eklendi";
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
    <title>Yeni Grup Sahibi Ekle - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-users"></i> Roblox Grup Yönetimi
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="../auth/logout.php">Çıkış</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-plus"></i> Yeni Grup Sahibi Ekle</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($success)): ?>
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
                            </div>
                            <div class="mb-3">
                                <label for="group_id" class="form-label">Grup ID</label>
                                <input type="number" class="form-control" id="group_id" name="group_id" 
                                       value="<?php echo htmlspecialchars($_POST['group_id'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="group_name" class="form-label">Grup Adı</label>
                                <input type="text" class="form-control" id="group_name" name="group_name" 
                                       value="<?php echo htmlspecialchars($_POST['group_name'] ?? ''); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus"></i> Grup Sahibi Ekle
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Geri Dön
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
