<?php
session_start();
require_once '../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $roblox_cookie = trim($_POST['roblox_cookie']);
    $group_id = trim($_POST['group_id']);
    $group_name = trim($_POST['group_name']);
    
    // Validation
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Tüm alanlar gerekli";
    } elseif ($password !== $confirm_password) {
        $error = "Şifreler eşleşmiyor";
    } elseif (strlen($password) < 6) {
        $error = "Şifre en az 6 karakter olmalı";
    } else {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $error = "Bu kullanıcı adı zaten alınmış";
        } else {
            // Create user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, group_id, group_name, roblox_cookie, created_at, updated_at) VALUES (?, ?, 'owner', ?, ?, ?, NOW(), NOW())");
            
            if ($stmt->execute([$username, $hashed_password, $group_id, $group_name, $roblox_cookie])) {
                $success = "Hesap başarıyla oluşturuldu! Şimdi giriş yapabilirsiniz.";
            } else {
                $error = "Hesap oluşturulurken hata oluştu";
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
    <title>Kayıt Ol - Roblox Grup Yönetimi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card mt-5">
                    <div class="card-header text-center">
                        <h4><i class="fas fa-user-plus"></i> Yeni Hesap Oluştur</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                            <div class="text-center">
                                <a href="login.php" class="btn btn-primary">Giriş Yap</a>
                            </div>
                        <?php else: ?>
                        
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
                                <label for="confirm_password" class="form-label">Şifre Tekrar</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="group_id" class="form-label">Grup ID</label>
                                <input type="number" class="form-control" id="group_id" name="group_id" 
                                       value="<?php echo htmlspecialchars($_POST['group_id'] ?? ''); ?>" 
                                       placeholder="123456789">
                                <div class="form-text">Yönetmek istediğiniz Roblox grup ID'si</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="group_name" class="form-label">Grup Adı</label>
                                <input type="text" class="form-control" id="group_name" name="group_name" 
                                       value="<?php echo htmlspecialchars($_POST['group_name'] ?? ''); ?>" 
                                       placeholder="Grup Adı">
                            </div>
                            
                            <div class="mb-3">
                                <label for="roblox_cookie" class="form-label">Roblox Cookie (.ROBLOSECURITY)</label>
                                <textarea class="form-control" id="roblox_cookie" name="roblox_cookie" rows="3" 
                                          placeholder="_|WARNING:-DO-NOT-SHARE-THIS..."></textarea>
                                
                                <!-- Cookie Alma Rehberi -->
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <button class="btn btn-link text-decoration-none p-0" type="button" 
                                                    data-bs-toggle="collapse" data-bs-target="#cookieGuide">
                                                <i class="fas fa-question-circle"></i> Cookie Nasıl Alınır? (Adım Adım Rehber)
                                            </button>
                                        </h6>
                                    </div>
                                    <div class="collapse" id="cookieGuide">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-step-forward"></i> Adım 1: Roblox'a Giriş</h6>
                                                    <ul class="list-unstyled">
                                                        <li>• <a href="https://www.roblox.com" target="_blank">Roblox.com</a>'a gidin</li>
                                                        <li>• Hesabınıza giriş yapın</li>
                                                        <li>• Grup sahibi olduğunuz hesapta olduğunuzdan emin olun</li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-step-forward"></i> Adım 2: Developer Tools</h6>
                                                    <ul class="list-unstyled">
                                                        <li>• <kbd>F12</kbd> tuşuna basın</li>
                                                        <li>• Ya da <kbd>Ctrl + Shift + I</kbd></li>
                                                        <li>• Chrome: Sağ tık → "İncele"</li>
                                                        <li>• Firefox: Sağ tık → "Öğeyi İncele"</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-step-forward"></i> Adım 3: Cookies Sekmesi</h6>
                                                    <ul class="list-unstyled">
                                                        <li>• <strong>Application</strong> sekmesine tıklayın</li>
                                                        <li>• Sol panelde <strong>Storage</strong> bölümünü bulun</li>
                                                        <li>• <strong>Cookies</strong> yazısına tıklayın</li>
                                                        <li>• <strong>https://www.roblox.com</strong> seçin</li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-step-forward"></i> Adım 4: Cookie Kopyalama</h6>
                                                    <ul class="list-unstyled">
                                                        <li>• <strong>.ROBLOSECURITY</strong> adlı cookie'yi bulun</li>
                                                        <li>• "Value" sütunundaki değeri seçin</li>
                                                        <li>• <kbd>Ctrl + C</kbd> ile kopyalayın</li>
                                                        <li>• Yukarıdaki alana yapıştırın</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            
                                            <div class="alert alert-info">
                                                <h6><i class="fas fa-info-circle"></i> Önemli Notlar:</h6>
                                                <ul class="mb-0">
                                                    <li>Cookie <code>_|WARNING:-DO-NOT-SHARE-THIS</code> ile başlar</li>
                                                    <li>Çok uzun bir metin (300+ karakter)</li>
                                                    <li>Harf, rakam, nokta ve tire içerir</li>
                                                    <li>Kopyalarken başını/sonunu kaçırmayın</li>
                                                </ul>
                                            </div>
                                            
                                            <div class="alert alert-warning">
                                                <h6><i class="fas fa-exclamation-triangle"></i> Güvenlik Uyarısı:</h6>
                                                <p class="mb-0">Bu cookie'yi kimseyle paylaşmayın! Bu bilgiyle hesabınıza erişim sağlanabilir.</p>
                                            </div>
                                            
                                            <div class="text-center">
                                                <small class="text-muted">
                                                    Sorun yaşıyorsanız: <br>
                                                    Chrome → F12 → Application → Storage → Cookies → roblox.com → .ROBLOSECURITY
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Önemli:</strong> Cookie'nizi kimseyle paylaşmayın! Bu bilgi sadece grup yönetimi için kullanılacak.
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Hesap Oluştur</button>
                        </form>
                        
                        <?php endif; ?>
                        
                        <div class="text-center mt-3">
                            <a href="login.php">Zaten hesabınız var mı? Giriş yapın</a><br>
                            <a href="../index.php">Ana Sayfaya Dön</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>