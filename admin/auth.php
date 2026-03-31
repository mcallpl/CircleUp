<?php
// Admin Authentication System

session_start();

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: /CircleUp/admin/login.php');
        exit();
    }
}

function loginAdmin($username, $password) {
    $db = getDB();
    
    // Prevent SQL injection
    $stmt = $db->prepare("SELECT id, username, password_hash FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $admin = $result->fetch_assoc();
    
    // Verify password
    if (!password_verify($password, $admin['password_hash'])) {
        return false;
    }
    
    // Set session
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    
    // Log login
    logAction($admin['id'], 'admin_login', ['username' => $username]);
    
    return true;
}

function logoutAdmin() {
    if (isAdminLoggedIn()) {
        logAction($_SESSION['admin_id'], 'admin_logout', []);
    }
    session_destroy();
}

function logAction($admin_id, $action, $details = []) {
    $db = getDB();
    $details_json = json_encode($details);
    
    $stmt = $db->prepare("INSERT INTO audit_log (admin_id, action, details) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $admin_id, $action, $details_json);
    $stmt->execute();
}

function getCurrentAdmin() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, email FROM admins WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}
