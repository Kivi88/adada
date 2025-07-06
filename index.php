<?php
session_start();
require_once 'config/database.php';
require_once 'auth/session.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roblox Grup Yönetim Sistemi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-users"></i> Roblox Grup Yönetimi
            </a>
            <div class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a class="nav-link" href="admin/dashboard.php">
                            <i class="fas fa-cog"></i> Admin Panel
                        </a>
                    <?php elseif (isOwner()): ?>
                        <a class="nav-link" href="owner/dashboard.php">
                            <i class="fas fa-crown"></i> Grup Sahibi Panel
                        </a>
                    <?php endif; ?>
                    <a class="nav-link" href="auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Çıkış
                    </a>
                <?php else: ?>
                    <a class="nav-link" href="auth/login.php">
                        <i class="fas fa-sign-in-alt"></i> Giriş
                    </a>
                    <a class="nav-link" href="auth/register.php">
                        <i class="fas fa-user-plus"></i> Kayıt Ol
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <!-- Group Lookup Section -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-search"></i> Grup Arama</h5>
                    </div>
                    <div class="card-body">
                        <form id="groupLookupForm">
                            <div class="mb-3">
                                <label for="groupId" class="form-label">Grup ID</label>
                                <input type="number" class="form-control" id="groupId" placeholder="Grup ID girin (Demo: 123456)" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Grup Ara
                            </button>
                        </form>
                        <div id="groupResults" class="mt-3"></div>
                    </div>
                </div>
            </div>

            <!-- Player Search Section -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-user-search"></i> Oyuncu Arama</h5>
                    </div>
                    <div class="card-body">
                        <form id="playerSearchForm">
                            <div class="mb-3">
                                <label for="playerName" class="form-label">Oyuncu Adı</label>
                                <input type="text" class="form-control" id="playerName" placeholder="Oyuncu adı girin (Demo: DemoKullanici)" required>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-search"></i> Oyuncu Ara
                            </button>
                        </form>
                        <div id="playerResults" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Section for Non-authenticated Users -->
        <?php if (!isLoggedIn()): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h5>Grup Yönetimi Özellikleri</h5>
                        <p>Grup sahibi veya admin olarak giriş yaparak daha fazla özelliğe erişebilirsiniz.</p>
                        <a href="auth/login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Giriş Yap
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
