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

    // Track login count and last login time
    $stmt2 = $db->prepare("UPDATE admins SET login_count = login_count + 1, last_login_at = NOW() WHERE id = ?");
    $stmt2->bind_param("i", $admin['id']);
    $stmt2->execute();

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
    $stmt = $db->prepare("SELECT id, username, email, role FROM admins WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

function isEditor() {
    $admin = getCurrentAdmin();
    return $admin && $admin['role'] === 'editor';
}

function isAdminUser() {
    $admin = getCurrentAdmin();
    return $admin && $admin['role'] === 'admin';
}

function requireEditor() {
    if (!isAdminLoggedIn() || !isEditor()) {
        header('Location: /CircleUp/admin/login.php');
        exit();
    }
}
