<?php
require_once '../config.php';
require_once './auth.php';

requireAdmin();

$admin = getCurrentAdmin();
$db = getDB();

$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    header('Location: /CircleUp/admin/dashboard.php');
    exit();
}

// Get product
$stmt = $db->prepare("SELECT id, name FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: /CircleUp/admin/dashboard.php');
    exit();
}

// Delete product (cascades to variants and order_items)
$stmt = $db->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);

if ($stmt->execute()) {
    logAction($admin['id'], 'product_deleted', ['product_id' => $product_id, 'name' => $product['name']]);
    $redirect = $admin['role'] === 'editor' ? '/CircleUp/admin/editor-dashboard.php' : '/CircleUp/admin/dashboard.php';
    header("Location: $redirect?deleted=1");
} else {
    header('Location: /CircleUp/admin/dashboard.php?error=1');
}
exit();
