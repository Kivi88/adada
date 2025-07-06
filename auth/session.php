<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isOwner() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'owner';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../auth/login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit();
    }
}

function requireOwner() {
    requireLogin();
    if (!isOwner() && !isHelper()) {
        header('Location: ../index.php');
        exit();
    }
}

function isHelper() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'helper';
}

function hasPermission($permission) {
    if (isAdmin() || isOwner()) {
        return true;
    }
    
    if (isHelper() && isset($_SESSION['permissions'])) {
        return in_array($permission, $_SESSION['permissions']);
    }
    
    return false;
}
?>
