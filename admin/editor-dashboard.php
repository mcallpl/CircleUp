<?php
require_once '../config.php';
require_once './auth.php';

requireAdmin();

$db = getDB();
$admin = getCurrentAdmin();

// Check if editor
if ($admin['role'] !== 'editor') {
    header('Location: /CircleUp/admin/dashboard.php');
    exit();
}

// Editor stats (read-only)
$stats = [
    'total_products' => $db->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'],
    'total_variants' => $db->query("SELECT COUNT(*) as count FROM variants")->fetch_assoc()['count'],
    'total_orders' => $db->query("SELECT COUNT(*) as count FROM orders WHERE status != 'cancelled'")->fetch_assoc()['count'],
    'total_revenue' => $db->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0,
    'pending_orders' => $db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'],
];

// Recent products
$recent_products = $db->query("SELECT p.id, p.name, p.price, p.category, p.created_at FROM products p ORDER BY p.created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);

// Recent orders
$recent_orders = $db->query("SELECT o.id, o.order_number, o.customer_name, o.total_amount, o.status, o.created_at FROM orders o ORDER BY o.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CircleUp Editor Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DM Sans', -apple-system, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        
        .navbar {
            background: white;
            border-bottom: 1px solid #eee;
            padding: 0 30px;
            height: 70px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .navbar h1 {
            font-size: 24px;
            color: #667eea;
        }
        
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .admin-info {
            text-align: right;
        }
        
        .admin-info p {
            font-size: 14px;
            color: #666;
        }
        
        .admin-info strong {
            display: block;
            color: #333;
        }
        
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #667eea;
            color: white;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 4px;
        }
        
        .logout-btn {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
        }
        
        .logout-btn:hover {
            background: #ff5252;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .stat-label {
            font-size: 13px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 600;
            color: #667eea;
        }
        
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .card h2 {
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.2s;
        }
        
        .btn:hover {
            background: #5568d3;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        thead {
            background: #f5f7fa;
        }
        
        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #eee;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .action-links {
            display: flex;
            gap: 10px;
        }
        
        .action-links a {
            color: #667eea;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }
        
        .action-links a:hover {
            text-decoration: underline;
        }
        
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status.completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status.shipped {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status.cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .warning-box p {
            font-size: 14px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>CircleUp Editor</h1>
        <div class="navbar-right">
            <div class="admin-info">
                <p>Editor</p>
                <strong><?php echo htmlspecialchars($admin['username']); ?></strong>
                <div class="role-badge">Editor</div>
            </div>
            <form action="/CircleUp/admin/logout.php" method="POST" style="margin: 0;">
                <button class="logout-btn">Logout</button>
            </form>
        </div>
    </div>
    
    <div class="container">
        <div class="warning-box">
            <p><strong>ℹ️ Editor Mode:</strong> You can add, edit, and delete products. You can view orders and revenue. You cannot change admin settings or user accounts.</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Products</div>
                <div class="stat-value"><?php echo $stats['total_products']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Variants</div>
                <div class="stat-value"><?php echo $stats['total_variants']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">$<?php echo number_format($stats['total_revenue'], 2); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending Orders</div>
                <div class="stat-value"><?php echo $stats['pending_orders']; ?></div>
            </div>
        </div>
        
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Products</h2>
                <a href="/CircleUp/admin/product-form.php" class="btn">+ Add Product</a>
            </div>
            
            <?php if (!empty($recent_products)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_products as $product): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($product['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars(PRODUCT_CATEGORIES[$product['category']] ?? $product['category']); ?></td>
                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                                <td>
                                    <div class="action-links">
                                        <a href="/CircleUp/admin/product-form.php?id=<?php echo $product['id']; ?>">Edit</a>
                                        <a href="/CircleUp/admin/delete-product.php?id=<?php echo $product['id']; ?>" onclick="return confirm('Delete product?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="padding: 20px; color: #666; text-align: center;">No products yet. Click "+ Add Product" to create your first one.</p>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>Recent Orders</h2>
            <?php if (!empty($recent_orders)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><span class="status <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="padding: 20px; color: #666; text-align: center;">No orders yet</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
