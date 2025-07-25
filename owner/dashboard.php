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

$roblox_api = new RobloxAPI($owner['roblox_cookie']);
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-info-circle"></i> Grup Bilgileri</h5>
                        <button class="btn btn-primary btn-sm" onclick="showEditGroupModal()">
                            <i class="fas fa-edit"></i> Grup Adını Düzenle
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Grup Adı:</strong><br>
                                <span id="current-group-name"><?php echo htmlspecialchars($group_info['name']); ?></span>
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

        <!-- Quick Actions Row -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-user-plus fa-2x text-primary mb-2"></i>
                        <h6>Üye Davet Et</h6>
                        <button class="btn btn-primary btn-sm" onclick="showInviteModal()">
                            <i class="fas fa-plus"></i> Davet Et
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-chart-bar fa-2x text-success mb-2"></i>
                        <h6>Üye İstatistikleri</h6>
                        <button class="btn btn-success btn-sm" onclick="showStatsModal()">
                            <i class="fas fa-chart-line"></i> Görüntüle
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-ban fa-2x text-warning mb-2"></i>
                        <h6>Banlanan Üyeler</h6>
                        <button class="btn btn-warning btn-sm" onclick="showBannedModal()">
                            <i class="fas fa-eye"></i> Görüntüle
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-user-shield fa-2x text-info mb-2"></i>
                        <h6>Yardımcı Yönetimi</h6>
                        <button class="btn btn-info btn-sm" onclick="showHelpersModal()">
                            <i class="fas fa-users-cog"></i> Yönet
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Helper Management Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-user-shield"></i> Grup Yardımcıları</h5>
                        <button class="btn btn-success btn-sm" onclick="showAddHelperModal()">
                            <i class="fas fa-plus"></i> Yardımcı Ekle
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="helpersTable">
                                <thead>
                                    <tr>
                                        <th>Kullanıcı Adı</th>
                                        <th>Yetkiler</th>
                                        <th>Eklenme Tarihi</th>
                                        <th>Durum</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody id="helpersTableBody">
                                    <!-- Helper data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-users"></i> Grup Üyeleri</h5>
                        <div>
                            <button class="btn btn-primary btn-sm" onclick="refreshMembers()">
                                <i class="fas fa-refresh"></i> Yenile
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="exportMembers()">
                                <i class="fas fa-download"></i> Dışa Aktar
                            </button>
                        </div>
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
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-warning" 
                                                            onclick="showRankModal(<?php echo $member['user']['id']; ?>, '<?php echo htmlspecialchars($member['user']['username']); ?>')">
                                                        <i class="fas fa-star"></i> Rütbe
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" 
                                                            onclick="kickMember(<?php echo $member['user']['id']; ?>, '<?php echo htmlspecialchars($member['user']['username']); ?>')">
                                                        <i class="fas fa-user-times"></i> At
                                                    </button>
                                                    <button class="btn btn-sm btn-secondary" 
                                                            onclick="banMember(<?php echo $member['user']['id']; ?>, '<?php echo htmlspecialchars($member['user']['username']); ?>')">
                                                        <i class="fas fa-ban"></i> Banla
                                                    </button>
                                                </div>
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

    <!-- Statistics Modal -->
    <div class="modal fade" id="statsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Grup İstatistikleri</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h4 class="text-primary">Toplam Üye</h4>
                                    <h2 id="totalMembers">-</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h4 class="text-success">Aktif Üyeler</h4>
                                    <h2 id="activeMembers">-</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h6>Rütbe Dağılımı</h6>
                        <div id="roleDistribution"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invite Modal -->
    <div class="modal fade" id="inviteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Üye Davet Et</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inviteUsername" class="form-label">Kullanıcı Adı</label>
                        <input type="text" class="form-control" id="inviteUsername" placeholder="Roblox kullanıcı adını girin">
                    </div>
                    <div class="mb-3">
                        <label for="inviteRole" class="form-label">Başlangıç Rütbesi</label>
                        <select class="form-select" id="inviteRole">
                            <option value="1">Üye</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="inviteMember()">Davet Gönder</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Grup Ayarları</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="groupDescription" class="form-label">Grup Açıklaması</label>
                        <textarea class="form-control" id="groupDescription" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="publicEntry">
                            <label class="form-check-label" for="publicEntry">
                                Herkese açık katılım
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="saveSettings()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Group Name Modal -->
    <div class="modal fade" id="editGroupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Grup Adını Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editGroupForm">
                        <div class="mb-3">
                            <label for="newGroupName" class="form-label">Yeni Grup Adı</label>
                            <input type="text" class="form-control" id="newGroupName" maxlength="50" required>
                            <div class="form-text">Maximum 50 karakter</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="updateGroupName()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Helper Modal -->
    <div class="modal fade" id="addHelperModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yardımcı Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addHelperForm">
                        <div class="mb-3">
                            <label for="helperUsername" class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" id="helperUsername" placeholder="Kullanıcı adını girin" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Yetkiler</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="perm_manage_ranks" value="manage_ranks">
                                <label class="form-check-label" for="perm_manage_ranks">
                                    Rütbe Yönetimi
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="perm_edit_group_name" value="edit_group_name">
                                <label class="form-check-label" for="perm_edit_group_name">
                                    Grup Adını Değiştirme
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="perm_kick_members" value="kick_members">
                                <label class="form-check-label" for="perm_kick_members">
                                    Üye Atma
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="perm_invite_members" value="invite_members">
                                <label class="form-check-label" for="perm_invite_members">
                                    Üye Davet Etme
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="perm_ban_members" value="ban_members">
                                <label class="form-check-label" for="perm_ban_members">
                                    Üye Banlama
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="addHelper()">Yardımcı Ekle</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Helper Modal -->
    <div class="modal fade" id="editHelperModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yardımcı Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editHelperForm">
                        <input type="hidden" id="edit_helper_id">
                        <div class="mb-3">
                            <label for="edit_helper_username" class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" id="edit_helper_username" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Yetkiler</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_perm_manage_ranks" value="manage_ranks">
                                <label class="form-check-label" for="edit_perm_manage_ranks">
                                    Rütbe Yönetimi
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_perm_edit_group_name" value="edit_group_name">
                                <label class="form-check-label" for="edit_perm_edit_group_name">
                                    Grup Adını Değiştirme
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_perm_kick_members" value="kick_members">
                                <label class="form-check-label" for="edit_perm_kick_members">
                                    Üye Atma
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_perm_invite_members" value="invite_members">
                                <label class="form-check-label" for="edit_perm_invite_members">
                                    Üye Davet Etme
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_perm_ban_members" value="ban_members">
                                <label class="form-check-label" for="edit_perm_ban_members">
                                    Üye Banlama
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="updateHelper()">Güncelle</button>
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

        function kickMember(userId, username) {
            if (confirm(`${username} kullanıcısını gruptan atmak istediğinizden emin misiniz?`)) {
                // Demo için şimdilik alert göster
                alert('Üye atma özelliği yakında eklenecek');
            }
        }

        function banMember(userId, username) {
            if (confirm(`${username} kullanıcısını banlamak istediğinizden emin misiniz?`)) {
                // Demo için şimdilik alert göster
                alert('Banlama özelliği yakında eklenecek');
            }
        }

        function showStatsModal() {
            // Demo veriler
            document.getElementById('totalMembers').textContent = '<?php echo $group_info ? $group_info["memberCount"] : "0"; ?>';
            document.getElementById('activeMembers').textContent = '<?php echo count($group_members); ?>';
            
            const roleDistribution = document.getElementById('roleDistribution');
            roleDistribution.innerHTML = `
                <div class="progress mb-2">
                    <div class="progress-bar bg-primary" style="width: 60%">Üye (60%)</div>
                </div>
                <div class="progress mb-2">
                    <div class="progress-bar bg-warning" style="width: 30%">Moderatör (30%)</div>
                </div>
                <div class="progress">
                    <div class="progress-bar bg-success" style="width: 10%">Yönetici (10%)</div>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('statsModal')).show();
        }

        function showInviteModal() {
            new bootstrap.Modal(document.getElementById('inviteModal')).show();
        }

        function showBannedModal() {
            alert('Banlanan üyeler listesi yakında eklenecek');
        }

        function showSettingsModal() {
            document.getElementById('groupDescription').value = '<?php echo htmlspecialchars($group_info["description"] ?? ""); ?>';
            document.getElementById('publicEntry').checked = <?php echo ($group_info["publicEntryAllowed"] ?? false) ? "true" : "false"; ?>;
            new bootstrap.Modal(document.getElementById('settingsModal')).show();
        }

        function inviteMember() {
            const username = document.getElementById('inviteUsername').value;
            if (!username) {
                alert('Lütfen kullanıcı adı girin');
                return;
            }
            alert('Davet özelliği yakında eklenecek');
        }

        function saveSettings() {
            alert('Ayar kaydetme özelliği yakında eklenecek');
        }

        function exportMembers() {
            alert('Üye listesi dışa aktarma özelliği yakında eklenecek');
        }

        // Group name editing functions
        function showEditGroupModal() {
            document.getElementById('newGroupName').value = document.getElementById('current-group-name').textContent;
            new bootstrap.Modal(document.getElementById('editGroupModal')).show();
        }

        function updateGroupName() {
            const newName = document.getElementById('newGroupName').value.trim();
            if (!newName || newName.length < 3) {
                alert('Grup adı en az 3 karakter olmalıdır');
                return;
            }
            
            fetch('../api/group_management.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update_group_name',
                    groupId: groupId,
                    newName: newName
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('current-group-name').textContent = newName;
                    bootstrap.Modal.getInstance(document.getElementById('editGroupModal')).hide();
                    alert('Grup adı başarıyla güncellendi');
                } else {
                    alert('Hata: ' + (data.error || 'Bilinmeyen hata'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('İstek gönderilirken hata oluştu');
            });
        }

        // Helper management functions
        function showHelpersModal() {
            loadHelpers();
            new bootstrap.Modal(document.getElementById('helpersModal')).show();
        }

        function showAddHelperModal() {
            new bootstrap.Modal(document.getElementById('addHelperModal')).show();
        }

        function addHelper() {
            const username = document.getElementById('helperUsername').value.trim();
            if (!username) {
                alert('Lütfen kullanıcı adı girin');
                return;
            }

            const permissions = [];
            document.querySelectorAll('#addHelperForm input[type="checkbox"]:checked').forEach(checkbox => {
                permissions.push(checkbox.value);
            });

            if (permissions.length === 0) {
                alert('Lütfen en az bir yetki seçin');
                return;
            }

            fetch('../api/helper_management.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add_helper',
                    groupId: groupId,
                    username: username,
                    permissions: permissions
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addHelperModal')).hide();
                    loadHelpers();
                    alert('Yardımcı başarıyla eklendi');
                    document.getElementById('addHelperForm').reset();
                } else {
                    alert('Hata: ' + (data.error || 'Bilinmeyen hata'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('İstek gönderilirken hata oluştu');
            });
        }

        function loadHelpers() {
            fetch('../api/helper_management.php?action=get_helpers&groupId=' + groupId)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('helpersTableBody');
                    tbody.innerHTML = '';
                    
                    if (data.success && data.helpers) {
                        data.helpers.forEach(helper => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${helper.username}</td>
                                <td>
                                    ${helper.permissions.map(perm => `<span class="badge bg-info me-1">${getPermissionName(perm)}</span>`).join('')}
                                </td>
                                <td>${new Date(helper.created_at).toLocaleDateString('tr-TR')}</td>
                                <td>
                                    <span class="badge bg-success">Aktif</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editHelper(${helper.id}, '${helper.username}', ${JSON.stringify(helper.permissions).replace(/"/g, '&quot;')})">
                                        <i class="fas fa-edit"></i> Düzenle
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="removeHelper(${helper.id}, '${helper.username}')">
                                        <i class="fas fa-trash"></i> Kaldır
                                    </button>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Henüz yardımcı eklenmemiş</td></tr>';
                    }
                });
        }

        function editHelper(id, username, permissions) {
            document.getElementById('edit_helper_id').value = id;
            document.getElementById('edit_helper_username').value = username;
            
            // Reset all checkboxes
            document.querySelectorAll('#editHelperForm input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Check current permissions
            permissions.forEach(perm => {
                const checkbox = document.getElementById('edit_perm_' + perm);
                if (checkbox) checkbox.checked = true;
            });
            
            new bootstrap.Modal(document.getElementById('editHelperModal')).show();
        }

        function updateHelper() {
            const id = document.getElementById('edit_helper_id').value;
            const permissions = [];
            document.querySelectorAll('#editHelperForm input[type="checkbox"]:checked').forEach(checkbox => {
                permissions.push(checkbox.value);
            });

            if (permissions.length === 0) {
                alert('Lütfen en az bir yetki seçin');
                return;
            }

            fetch('../api/helper_management.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update_helper',
                    id: id,
                    permissions: permissions
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editHelperModal')).hide();
                    loadHelpers();
                    alert('Yardımcı yetkiler başarıyla güncellendi');
                } else {
                    alert('Hata: ' + (data.error || 'Bilinmeyen hata'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('İstek gönderilirken hata oluştu');
            });
        }

        function removeHelper(id, username) {
            if (confirm(`${username} yardımcısını kaldırmak istediğinizden emin misiniz?`)) {
                fetch('../api/helper_management.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'remove_helper',
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadHelpers();
                        alert('Yardımcı başarıyla kaldırıldı');
                    } else {
                        alert('Hata: ' + (data.error || 'Bilinmeyen hata'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('İstek gönderilirken hata oluştu');
                });
            }
        }

        function getPermissionName(perm) {
            const permissionNames = {
                'manage_ranks': 'Rütbe Yönetimi',
                'edit_group_name': 'Grup Adı Düzenleme',
                'kick_members': 'Üye Atma',
                'invite_members': 'Üye Davet',
                'ban_members': 'Üye Banlama'
            };
            return permissionNames[perm] || perm;
        }

        // Load helpers on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadHelpers();
        });
    </script>
</body>
</html>
