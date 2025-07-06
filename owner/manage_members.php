<?php
session_start();
require_once '../config/database.php';
require_once '../auth/session.php';
require_once '../config/roblox_api.php';

requireOwner();

// Get owner details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$owner = $stmt->fetch();

$roblox_api = new RobloxAPI();
$search_results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_user'])) {
    $username = trim($_POST['username']);
    if (!empty($username)) {
        $user_data = $roblox_api->getUserByUsername($username);
        if ($user_data) {
            $user_groups = $roblox_api->getUserGroups($user_data['id']);
            $search_results = [
                'user' => $user_data,
                'groups' => $user_groups
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Üye Yönetimi - Grup Sahibi Dashboard</title>
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
            <div class="col-md-12">
                <h2><i class="fas fa-users-cog"></i> Üye Yönetimi</h2>
                <p class="text-muted">Grup üyelerinizi yönetin ve rütbelerini değiştirin.</p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-search"></i> Oyuncu Arama</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="input-group">
                                <input type="text" name="username" class="form-control" 
                                       placeholder="Oyuncu adını girin (örn: AKINCI_MELO)" 
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                                <button type="submit" name="search_user" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Ara
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($search_results)): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-user"></i> Oyuncu Bilgileri</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Kullanıcı Adı:</strong><br>
                                <?php echo htmlspecialchars($search_results['user']['name']); ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Kullanıcı ID:</strong><br>
                                <?php echo htmlspecialchars($search_results['user']['id']); ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Oluşturma Tarihi:</strong><br>
                                <?php echo date('d/m/Y', strtotime($search_results['user']['created'])); ?>
                            </div>
                        </div>
                        
                        <?php if (isset($search_results['groups']['data']) && !empty($search_results['groups']['data'])): ?>
                        <hr>
                        <h6><i class="fas fa-history"></i> Grup Geçmişi ve Mevcut Rütbeler</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Grup Adı</th>
                                        <th>Rütbe</th>
                                        <th>Grup ID</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($search_results['groups']['data'] as $group): ?>
                                    <tr <?php echo $group['group']['id'] == $owner['group_id'] ? 'class="table-primary"' : ''; ?>>
                                        <td><?php echo htmlspecialchars($group['group']['name']); ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo htmlspecialchars($group['role']['name']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($group['group']['id']); ?></td>
                                        <td>
                                            <?php if ($group['group']['id'] == $owner['group_id']): ?>
                                                <button class="btn btn-sm btn-warning" 
                                                        onclick="showRankModal(<?php echo $search_results['user']['id']; ?>, '<?php echo htmlspecialchars($search_results['user']['name']); ?>')">
                                                    <i class="fas fa-star"></i> Rütbe Değiştir
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">Başka grup</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Bu oyuncu hiçbir gruba üye değil.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Rank Modal -->
    <div class="modal fade" id="rankModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rütbe Değiştir</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="rankForm">
                        <input type="hidden" id="userId" name="userId">
                        <div class="mb-3">
                            <label for="username" class="form-label">Kullanıcı</label>
                            <input type="text" class="form-control" id="modalUsername" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="roleId" class="form-label">Yeni Rütbe</label>
                            <select class="form-select" id="roleId" name="roleId" required>
                                <option value="">Rütbe Seçin</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="changeRank()">Rütbe Değiştir</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const groupId = <?php echo json_encode($owner['group_id']); ?>;
        
        function showRankModal(userId, username) {
            document.getElementById('userId').value = userId;
            document.getElementById('modalUsername').value = username;
            
            // Load available roles
            fetch('../api/roblox_proxy.php?action=get_roles&groupId=' + groupId)
                .then(response => response.json())
                .then(data => {
                    const roleSelect = document.getElementById('roleId');
                    roleSelect.innerHTML = '<option value="">Rütbe Seçin</option>';
                    
                    if (data.roles) {
                        data.roles.forEach(role => {
                            const option = document.createElement('option');
                            option.value = role.id;
                            option.textContent = role.name + ' (Rank: ' + role.rank + ')';
                            roleSelect.appendChild(option);
                        });
                    }
                });
            
            new bootstrap.Modal(document.getElementById('rankModal')).show();
        }
        
        function changeRank() {
            const userId = document.getElementById('userId').value;
            const roleId = document.getElementById('roleId').value;
            
            if (!roleId) {
                alert('Lütfen bir rütbe seçin');
                return;
            }
            
            fetch('rank_member.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `userId=${userId}&roleId=${roleId}&groupId=${groupId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Rütbe başarıyla değiştirildi');
                    bootstrap.Modal.getInstance(document.getElementById('rankModal')).hide();
                } else {
                    alert('Hata: ' + (data.error || 'Bilinmeyen hata'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('İstek gönderilirken hata oluştu');
            });
        }
    </script>
</body>
</html>
