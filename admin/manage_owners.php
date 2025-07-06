<?php
session_start();
require_once '../config/database.php';
require_once '../auth/session.php';

requireAdmin();

$owner_id = $_GET['id'] ?? null;

if (!$owner_id) {
    header('Location: dashboard.php');
    exit();
}

// Get owner details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'owner'");
$stmt->execute([$owner_id]);
$owner = $stmt->fetch();

if (!$owner) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update') {
        $group_id = $_POST['group_id'];
        $group_name = $_POST['group_name'];
        
        $stmt = $pdo->prepare("UPDATE users SET group_id = ?, group_name = ? WHERE id = ?");
        $stmt->execute([$group_id, $group_name, $owner_id]);
        
        $success = "Grup sahibi bilgileri güncellendi";
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$owner_id]);
        
        header('Location: dashboard.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grup Sahibi Yönet - Admin Dashboard</title>
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
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-edit"></i> Grup Sahibi Düzenle</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="update">
                            <div class="mb-3">
                                <label for="username" class="form-label">Kullanıcı Adı</label>
                                <input type="text" class="form-control" id="username" 
                                       value="<?php echo htmlspecialchars($owner['username']); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="group_id" class="form-label">Grup ID</label>
                                <input type="number" class="form-control" id="group_id" name="group_id" 
                                       value="<?php echo htmlspecialchars($owner['group_id']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="group_name" class="form-label">Grup Adı</label>
                                <input type="text" class="form-control" id="group_name" name="group_name" 
                                       value="<?php echo htmlspecialchars($owner['group_name']); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Güncelle
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5><i class="fas fa-trash"></i> Tehlikeli İşlemler</h5>
                    </div>
                    <div class="card-body">
                        <p>Bu grup sahibini silmek istediğinizden emin misiniz?</p>
                        <form method="POST" onsubmit="return confirm('Bu işlem geri alınamaz. Emin misiniz?')">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Grup Sahibini Sil
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
