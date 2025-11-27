<?php
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
}

function hasPermission($requiredRole) {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    
    // Admin has all permissions
    if ($_SESSION['role'] === 'admin') {
        return true;
    }
    
    // Check if user's role matches required role
    return $_SESSION['role'] === $requiredRole;
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}
?>