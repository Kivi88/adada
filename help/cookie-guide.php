<?php
session_start();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roblox Cookie Alma Rehberi - Roblox Grup Yönetimi</title>
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
                <a class="nav-link" href="../auth/register.php">Kayıt Ol</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header text-center">
                        <h3><i class="fas fa-cookie-bite"></i> Roblox Cookie Alma Rehberi</h3>
                        <p class="text-muted mb-0">Grup yönetimi için Roblox cookie'nizi nasıl alacağınızı öğrenin</p>
                    </div>
                    <div class="card-body">
                        
                        <!-- Step by Step Guide -->
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h5><i class="fas fa-sign-in-alt"></i> Adım 1: Roblox'a Giriş</h5>
                                    </div>
                                    <div class="card-body">
                                        <ol>
                                            <li><a href="https://www.roblox.com" target="_blank" class="text-decoration-none">Roblox.com</a>'a gidin</li>
                                            <li>Grup sahibi hesabınıza giriş yapın</li>
                                            <li>Ana sayfada olduğunuzdan emin olun</li>
                                        </ol>
                                        <div class="alert alert-info">
                                            <small><i class="fas fa-info-circle"></i> Grup sahibi olan hesapla giriş yapmalısınız!</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-success text-white">
                                        <h5><i class="fas fa-tools"></i> Adım 2: Developer Tools</h5>
                                    </div>
                                    <div class="card-body">
                                        <h6>Geliştirici araçlarını açın:</h6>
                                        <ul>
                                            <li><kbd class="bg-dark text-white">F12</kbd> tuşuna basın</li>
                                            <li>Ya da <kbd class="bg-dark text-white">Ctrl + Shift + I</kbd></li>
                                            <li><strong>Chrome:</strong> Sağ tık → "İncele"</li>
                                            <li><strong>Firefox:</strong> Sağ tık → "Öğeyi İncele"</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-warning text-dark">
                                        <h5><i class="fas fa-folder-open"></i> Adım 3: Cookies Bölümü</h5>
                                    </div>
                                    <div class="card-body">
                                        <h6>Developer Tools'da:</h6>
                                        <ol>
                                            <li><strong>"Application"</strong> sekmesine tıklayın</li>
                                            <li>Sol panelde <strong>"Storage"</strong> bölümünü bulun</li>
                                            <li><strong>"Cookies"</strong> yazısına tıklayın</li>
                                            <li><strong>"https://www.roblox.com"</strong> seçin</li>
                                        </ol>
                                        <div class="alert alert-warning">
                                            <small><i class="fas fa-exclamation-triangle"></i> "Storage" değil, "Cookies" sekmesine gidin!</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-danger text-white">
                                        <h5><i class="fas fa-copy"></i> Adım 4: Cookie Kopyalama</h5>
                                    </div>
                                    <div class="card-body">
                                        <ol>
                                            <li><strong>".ROBLOSECURITY"</strong> adlı cookie'yi bulun</li>
                                            <li>"Value" sütunundaki değeri seçin</li>
                                            <li>Tüm değeri seçip <kbd class="bg-dark text-white">Ctrl + C</kbd> ile kopyalayın</li>
                                            <li>Kayıt formundaki alana yapıştırın</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Security Warning -->
                        <div class="alert alert-danger mt-4">
                            <h5><i class="fas fa-shield-alt"></i> GÜVENLİK UYARISI</h5>
                            <ul class="mb-0">
                                <li><strong>Cookie'nizi kimseyle paylaşmayın!</strong></li>
                                <li>Bu bilgiyle hesabınıza tam erişim sağlanabilir</li>
                                <li>Güvenilir olmayan sitelere cookie vermeyin</li>
                                <li>Şüpheli durumlarda şifrenizi değiştirin</li>
                            </ul>
                        </div>

                        <div class="text-center mt-4">
                            <a href="../auth/register.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-arrow-right"></i> Kayıt Ol Sayfasına Dön
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>