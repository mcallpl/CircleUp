<?php
require_once '../config.php';
require_once './auth.php';

requireAdmin();

$db = getDB();
$admin = getCurrentAdmin();

// Route editors to their dashboard
if ($admin['role'] === 'editor') {
    header('Location: /CircleUp/admin/editor-dashboard.php');
    exit();
}

// Dashboard stats
$stats = [
    'total_products' => $db->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'],
    'total_orders' => $db->query("SELECT COUNT(*) as count FROM orders WHERE status != 'cancelled'")->fetch_assoc()['count'],
    'total_revenue' => $db->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0,
    'pending_orders' => $db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count']
];

// Recent orders
$recent_orders = $db->query("SELECT o.id, o.order_number, o.customer_name, o.total_amount, o.status, o.created_at FROM orders o ORDER BY o.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CircleUp Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Barlow+Condensed:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0a1628;
            --navy-mid: #1a2744;
            --navy-light: #243456;
            --red: #b22234;
            --red-bright: #e8293b;
            --white: #f5f0e8;
            --white-pure: #ffffff;
            --gold: #c9a84c;
            --gold-bright: #ffd700;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Barlow Condensed', sans-serif;
            background: var(--navy);
            color: var(--white);
        }

        /* PATRIOTIC STRIPE */
        .flag-stripe-top {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            z-index: 100;
            background: repeating-linear-gradient(
                90deg,
                var(--red) 0px,
                var(--red) 33.33%,
                var(--white-pure) 33.33%,
                var(--white-pure) 66.66%,
                var(--navy-mid) 66.66%,
                var(--navy-mid) 100%
            );
        }

        .navbar {
            background: var(--navy-mid);
            border-bottom: 2px solid var(--gold);
            padding: 0 40px;
            height: 70px;
            margin-top: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--gold-bright);
        }

        .navbar-center {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .navbar-center a {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--gold);
            text-decoration: none;
            transition: color 0.3s;
        }

        .navbar-center a:hover {
            color: var(--white-pure);
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .admin-info {
            text-align: right;
        }

        .admin-info p {
            font-size: 11px;
            letter-spacing: 1px;
            color: var(--gold);
        }

        .admin-info strong {
            display: block;
            font-size: 14px;
            color: var(--white-pure);
            margin-top: 3px;
        }

        .logout-btn {
            background: var(--red);
            color: var(--white-pure);
            border: 2px solid var(--gold);
            padding: 8px 16px;
            border-radius: 2px;
            cursor: pointer;
            font-family: 'Oswald', sans-serif;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: var(--red-bright);
            box-shadow: 0 0 15px rgba(232, 41, 59, 0.5);
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 40px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px;
            margin-bottom: 50px;
        }

        .stat-card {
            background: var(--navy-light);
            padding: 28px;
            border: 1px solid var(--gold);
            border-radius: 2px;
        }

        .stat-label {
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 12px;
            font-weight: 700;
        }

        .stat-value {
            font-family: 'Oswald', sans-serif;
            font-size: 40px;
            font-weight: 700;
            color: var(--gold-bright);
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        .section-title {
            font-family: 'Oswald', sans-serif;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--gold-bright);
            margin-bottom: 30px;
            border-bottom: 2px solid var(--gold);
            padding-bottom: 15px;
        }

        .card {
            background: var(--navy-light);
            padding: 28px;
            border-radius: 2px;
            border: 1px solid var(--gold);
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            padding: 11px 24px;
            background: var(--red);
            color: var(--white-pure);
            border: 2px solid var(--gold);
            border-radius: 2px;
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            font-size: 11px;
            cursor: pointer;
            text-decoration: none;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s;
        }

        .btn:hover {
            background: var(--red-bright);
            box-shadow: 0 0 15px rgba(232, 41, 59, 0.5);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        thead {
            background: var(--navy-mid);
        }

        th {
            padding: 14px;
            text-align: left;
            font-weight: 700;
            color: var(--gold);
            font-size: 11px;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-bottom: 2px solid var(--gold);
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--gold);
            font-size: 13px;
        }

        .status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 2px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .status.pending {
            background: rgba(232, 41, 59, 0.2);
            color: var(--red-bright);
            border: 1px solid var(--red);
        }

        .status.completed {
            background: rgba(201, 168, 76, 0.2);
            color: var(--gold);
            border: 1px solid var(--gold);
        }

        .empty {
            text-align: center;
            padding: 40px;
            color: var(--gold);
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 0 20px;
                flex-direction: column;
                height: auto;
                gap: 15px;
            }

            .container {
                padding: 0 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="flag-stripe-top"></div>

    <div class="navbar">
        <h1>CircleUp Admin</h1>
        <div class="navbar-center">
            <a href="/CircleUp/">Home</a>
            <a href="/CircleUp/store/">Store</a>
        </div>
        <div class="navbar-right">
            <div class="admin-info">
                <p>Administrator</p>
                <strong><?php echo htmlspecialchars($admin['username']); ?></strong>
            </div>
            <form action="/CircleUp/admin/logout.php" method="POST" style="margin: 0;">
                <button class="logout-btn">Logout</button>
            </form>
        </div>
    </div>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Products</div>
                <div class="stat-value"><?php echo $stats['total_products']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending Orders</div>
                <div class="stat-value"><?php echo $stats['pending_orders']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">$<?php echo number_format($stats['total_revenue'], 0); ?></div>
            </div>
        </div>

        <div class="content-section active" id="dashboard">
            <h2 class="section-title">Dashboard</h2>
            <div class="card">
                <h3 style="color: var(--gold-bright); margin-bottom: 20px; font-family: 'Oswald', sans-serif;">Recent Orders</h3>
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
                                    <td style="font-weight: 700;">#<?php echo htmlspecialchars($order['order_number']); ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td><span class="status <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty">No orders yet</div>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3 style="color: var(--gold-bright); margin-bottom: 20px; font-family: 'Oswald', sans-serif;">Product Management</h3>
                <a href="/CircleUp/admin/product-form.php" class="btn">+ Add New Product</a>
            </div>
        </div>
    </div>
</body>
</html>
