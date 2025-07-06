<?php
error_reporting(E_ERROR | E_PARSE);
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
$group_info = null;
$group_members = [];

if ($owner['group_id']) {
    $group_info = $roblox_api->getGroupInfo($owner['group_id']);
    $group_members_data = $roblox_api->getGroupMembers($owner['group_id']);
    if ($group_members_data && isset($group_members_data['data'])) {
        $group_members = $group_members_data['data'];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grup Sahibi Dashboard - Roblox Grup Yönetimi</title>
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
                <a class="nav-link" href="../index.php">Ana Sayfa</a>
                <a class="nav-link" href="../auth/logout.php">Çıkış</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2><i class="fas fa-crown"></i> Grup Sahibi Dashboard</h2>
                <p class="text-muted">Hoş geldiniz, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            </div>
        </div>

        <?php if ($group_info): ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle"></i> Grup Bilgileri</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Grup Adı:</strong><br>
                                <?php echo htmlspecialchars($group_info['name']); ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Üye Sayısı:</strong><br>
                                <?php echo number_format($group_info['memberCount']); ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Grup ID:</strong><br>
                                <?php echo htmlspecialchars($group_info['id']); ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Açıklama:</strong><br>
                                <?php echo htmlspecialchars(substr($group_info['description'] ?? 'Açıklama yok', 0, 100)); ?>...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-users"></i> Grup Üyeleri</h5>
                        <button class="btn btn-primary btn-sm" onclick="refreshMembers()">
                            <i class="fas fa-refresh"></i> Yenile
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="text" id="memberSearch" class="form-control" 
                                   placeholder="Üye ara..." onkeyup="filterMembers()">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped" id="membersTable">
                                <thead>
                                    <tr>
                                        <th>Kullanıcı Adı</th>
                                        <th>Rütbe</th>
                                        <th>Katılma Tarihi</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($group_members)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <?php if ($owner['group_id']): ?>
                                                Grup üyeleri yüklenemedi veya grup boş.
                                            <?php else: ?>
                                                Grup ID'si tanımlanmamış.
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($group_members as $member): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($member['user']['username']); ?></td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($member['role']['name']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo isset($member['user']['created']) ? date('d/m/Y', strtotime($member['user']['created'])) : 'Bilinmiyor'; ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" 
                                                        onclick="showRankModal(<?php echo $member['user']['id']; ?>, '<?php echo htmlspecialchars($member['user']['username']); ?>')">
                                                    <i class="fas fa-star"></i> Rütbe Ver
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                                <!-- Roles will be populated dynamically -->
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
        
        function refreshMembers() {
            location.reload();
        }
        
        function filterMembers() {
            const searchTerm = document.getElementById('memberSearch').value.toLowerCase();
            const rows = document.querySelectorAll('#membersTable tbody tr');
            
            rows.forEach(row => {
                const username = row.cells[0].textContent.toLowerCase();
                const role = row.cells[1].textContent.toLowerCase();
                
                if (username.includes(searchTerm) || role.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
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
                    setTimeout(() => location.reload(), 1000);
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
