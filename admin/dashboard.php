<?php
session_start();
require_once '../config/database.php';
require_once '../auth/session.php';

requireAdmin();

// Get group owners
$stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'owner' ORDER BY created_at DESC");
$stmt->execute();
$owners = $stmt->fetchAll();

// Get recent activities
$stmt = $pdo->prepare("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 10");
$stmt->execute();
$activities = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Roblox Grup Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-users"></i> Roblox Grup Yönetimi
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php">Ana Sayfa</a>
                <a class="nav-link" href="../auth/logout.php">Çıkış</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2><i class="fas fa-cog"></i> Admin Dashboard</h2>
                <p class="text-muted">Hoş geldiniz, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5>Grup Sahipleri</h5>
                                <h3><?php echo count($owners); ?></h3>
                            </div>
                            <div>
                                <i class="fas fa-crown fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5>Aktif Gruplar</h5>
                                <h3><?php echo count(array_unique(array_column($owners, 'group_id'))); ?></h3>
                            </div>
                            <div>
                                <i class="fas fa-users fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5>Son Aktiviteler</h5>
                                <h3><?php echo count($activities); ?></h3>
                            </div>
                            <div>
                                <i class="fas fa-chart-line fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-crown"></i> Grup Sahipleri</h5>
                        <a href="add_owner.php" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Yeni Grup Sahibi
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Kullanıcı Adı</th>
                                        <th>Grup ID</th>
                                        <th>Grup Adı</th>
                                        <th>Oluşturulma</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($owners as $owner): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($owner['username']); ?></td>
                                        <td><?php echo htmlspecialchars($owner['group_id']); ?></td>
                                        <td><?php echo htmlspecialchars($owner['group_name']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($owner['created_at'])); ?></td>
                                        <td>
                                            <a href="manage_owners.php?id=<?php echo $owner['id']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Düzenle
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-history"></i> Son Aktiviteler</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($activities)): ?>
                            <p class="text-muted">Henüz aktivite yok.</p>
                        <?php else: ?>
                            <?php foreach ($activities as $activity): ?>
                            <div class="mb-3">
                                <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?></small>
                                <p class="mb-1"><?php echo htmlspecialchars($activity['description']); ?></p>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
