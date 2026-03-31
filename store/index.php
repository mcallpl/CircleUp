<?php
require_once '../config.php';

$db = getDB();
$category = $_GET['category'] ?? null;
$search = $_GET['search'] ?? null;

// Build query
$query = "SELECT p.*, COUNT(v.id) as variant_count FROM products p LEFT JOIN variants v ON p.id = v.product_id";
$params = [];
$where = [];

if ($category && array_key_exists($category, PRODUCT_CATEGORIES)) {
    $where[] = "p.category = ?";
    $params[] = $category;
}

if ($search) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " GROUP BY p.id ORDER BY p.created_at DESC";

$stmt = $db->prepare($query);
if (!empty($params)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch categories
$categories_result = $db->query("SELECT DISTINCT category, COUNT(*) as count FROM products GROUP BY category");
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CircleUp Store — Premium Apparel</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Barlow+Condensed:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0a1628;
            --navy-mid: #1a2744;
            --navy-light: #243456;
            --red: #b22234;
            --red-bright: #e8293b;
            --red-deep: #8b1a28;
            --white: #f5f0e8;
            --white-pure: #ffffff;
            --silver: #c0c0c0;
            --silver-light: #e0ddd5;
            --gold: #c9a84c;
            --gold-bright: #ffd700;
            --chrome: #8a8a8a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Barlow Condensed', sans-serif;
            background: var(--navy);
            color: var(--white);
            line-height: 1.6;
        }

        /* PATRIOTIC STRIPES */
        .flag-stripes-top {
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

        /* HEADER */
        header {
            position: fixed;
            top: 6px;
            left: 0;
            right: 0;
            z-index: 1000;
            background: var(--navy-mid);
            border-bottom: 2px solid var(--gold);
            padding: 20px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Oswald', sans-serif;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 3px;
            color: var(--white-pure);
            text-decoration: none;
        }

        .logo span {
            color: var(--red);
        }

        .header-nav {
            display: flex;
            gap: 40px;
            align-items: center;
        }

        .header-nav a {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--gold);
            text-decoration: none;
            transition: color 0.3s;
        }

        .header-nav a:hover {
            color: var(--white-pure);
        }

        .cart-btn {
            width: 45px;
            height: 45px;
            background: var(--red);
            border: 2px solid var(--gold);
            border-radius: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .cart-btn:hover {
            background: var(--red-bright);
            box-shadow: 0 0 15px rgba(232, 41, 59, 0.6);
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--gold);
            color: var(--navy);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
        }

        /* HERO */
        .hero {
            margin-top: 85px;
            padding: 80px 60px;
            background: linear-gradient(135deg, var(--navy-light) 0%, var(--navy-mid) 100%);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 120%;
            height: 120%;
            background: radial-gradient(
                ellipse at center,
                rgba(178, 34, 52, 0.1) 0%,
                rgba(26, 39, 68, 0.3) 40%,
                transparent 70%
            );
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 64px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 20px;
            letter-spacing: 2px;
            color: var(--white-pure);
        }

        .hero h1 span {
            color: var(--gold-bright);
        }

        .hero p {
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto 40px;
            line-height: 1.8;
            letter-spacing: 1px;
            color: var(--silver-light);
        }

        .cta-button {
            display: inline-block;
            padding: 16px 45px;
            background: var(--red);
            color: var(--white-pure);
            border: 2px solid var(--gold);
            border-radius: 0;
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            letter-spacing: 2px;
            text-transform: uppercase;
            transition: all 0.3s;
        }

        .cta-button:hover {
            background: var(--red-bright);
            box-shadow: 0 0 20px rgba(232, 41, 59, 0.7);
        }

        /* CATEGORIES */
        .categories-section {
            padding: 70px 60px;
            background: var(--navy-mid);
            border-top: 2px solid var(--gold);
            border-bottom: 2px solid var(--gold);
        }

        .section-title {
            font-family: 'Oswald', sans-serif;
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 45px;
            letter-spacing: 2px;
            color: var(--gold-bright);
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
        }

        .category-card {
            padding: 20px;
            background: transparent;
            border: 2px solid var(--gold);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: var(--white);
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .category-card:hover,
        .category-card.active {
            background: var(--red);
            border-color: var(--red-bright);
            color: var(--white-pure);
            box-shadow: 0 0 15px rgba(232, 41, 59, 0.5);
        }

        .category-count {
            font-size: 10px;
            opacity: 0.8;
            margin-top: 6px;
        }

        /* PRODUCTS */
        .products-section {
            padding: 70px 60px;
            background: var(--navy);
        }

        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 50px;
            border-bottom: 2px solid var(--gold);
            padding-bottom: 20px;
        }

        .products-header h2 {
            font-family: 'Oswald', sans-serif;
            font-size: 42px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--gold-bright);
        }

        .sort-select {
            padding: 10px 15px;
            background: var(--navy-mid);
            border: 2px solid var(--gold);
            color: var(--gold);
            border-radius: 0;
            font-size: 11px;
            font-family: 'Barlow Condensed', sans-serif;
            cursor: pointer;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .sort-select:focus {
            outline: none;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 35px;
        }

        .product-card {
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            background: var(--navy-light);
            border: 1px solid var(--gold);
            padding: 0;
        }

        .product-card:hover {
            border-color: var(--red);
            box-shadow: 0 0 20px rgba(178, 34, 52, 0.4);
            transform: translateY(-5px);
        }

        .product-image {
            width: 100%;
            aspect-ratio: 1 / 1;
            background: var(--navy-mid);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--gold);
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 15px;
        }

        .product-image.empty {
            color: var(--chrome);
            font-size: 12px;
        }

        .product-info {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            padding: 22px;
        }

        .product-category {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--red);
            margin-bottom: 10px;
        }

        .product-name {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--white-pure);
            line-height: 1.4;
        }

        .product-desc {
            font-size: 12px;
            color: var(--silver);
            margin-bottom: 16px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-grow: 1;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 12px;
            border-top: 1px solid var(--gold);
        }

        .product-price {
            font-family: 'Oswald', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--gold-bright);
        }

        .add-to-cart {
            padding: 9px 16px;
            background: var(--red);
            border: 2px solid var(--gold);
            color: var(--white-pure);
            border-radius: 0;
            cursor: pointer;
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            font-size: 10px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            transition: all 0.3s;
        }

        .add-to-cart:hover {
            background: var(--red-bright);
            box-shadow: 0 0 12px rgba(232, 41, 59, 0.6);
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 80px 40px;
        }

        .empty-state h3 {
            font-family: 'Oswald', sans-serif;
            font-size: 32px;
            margin-bottom: 16px;
            color: var(--gold-bright);
            letter-spacing: 2px;
        }

        .empty-state p {
            color: var(--silver);
            margin-bottom: 28px;
            font-size: 14px;
        }

        /* FOOTER */
        footer {
            background: var(--navy-mid);
            color: var(--silver-light);
            padding: 50px 60px;
            text-align: center;
            border-top: 2px solid var(--gold);
            font-size: 11px;
            letter-spacing: 1px;
        }

        footer a {
            color: var(--gold);
            text-decoration: none;
            font-weight: 600;
        }

        footer a:hover {
            color: var(--white-pure);
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            header {
                padding: 12px 30px;
            }

            .logo {
                font-size: 24px;
            }

            .hero {
                padding: 50px 30px;
                margin-top: 70px;
            }

            .hero h1 {
                font-size: 40px;
            }

            .hero p {
                font-size: 14px;
            }

            .categories-section,
            .products-section {
                padding-left: 30px;
                padding-right: 30px;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 20px;
            }

            .products-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="flag-stripes-top"></div>

    <!-- HEADER -->
    <header>
        <a href="/CircleUp/store/" class="logo">Circle<span>Up</span></a>
        <nav class="header-nav">
            <a href="/CircleUp/store/">Shop</a>
            <a href="/CircleUp/admin/login.php">Admin</a>
            <a href="/CircleUp/store/cart.php" style="position: relative;">
                <div class="cart-btn">🛒<span class="cart-badge"></span></div>
            </a>
        </nav>
    </header>

    <!-- HERO -->
    <div class="hero">
        <div class="hero-content">
            <h1>Circle<span>Up</span></h1>
            <p>Premium Apparel for Leaders</p>
            <a href="#products" class="cta-button">Shop Collection</a>
        </div>
    </div>

    <!-- CATEGORIES -->
    <div class="categories-section">
        <h2 class="section-title">Collections</h2>
        <div class="categories-grid">
            <a href="/CircleUp/store/" class="category-card <?php echo !$category ? 'active' : ''; ?>">
                All Items
                <div class="category-count"><?php echo count($products); ?></div>
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="/CircleUp/store/?category=<?php echo urlencode($cat['category']); ?>" 
                   class="category-card <?php echo $category === $cat['category'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars(PRODUCT_CATEGORIES[$cat['category']] ?? $cat['category']); ?>
                    <div class="category-count"><?php echo $cat['count']; ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- PRODUCTS -->
    <div class="products-section" id="products">
        <div class="products-header">
            <h2><?php echo $search ? 'Results' : 'Featured'; ?></h2>
            <select class="sort-select">
                <option>Sort: Newest</option>
                <option>Price: Low to High</option>
                <option>Price: High to Low</option>
            </select>
        </div>

        <?php if (!empty($products)): ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image<?php echo !$product['image_url'] ? ' empty' : ''; ?>">
                            <?php if ($product['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     loading="lazy">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <div class="product-category">
                                <?php echo htmlspecialchars(PRODUCT_CATEGORIES[$product['category']] ?? $product['category']); ?>
                            </div>
                            <div class="product-name">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </div>
                            <div class="product-desc">
                                <?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 85)); ?>
                            </div>
                            <div class="product-footer">
                                <div class="product-price">
                                    $<?php echo number_format($product['price'], 0); ?>
                                </div>
                                <button class="add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['price']; ?>, '<?php echo htmlspecialchars($product['category']); ?>', '<?php echo htmlspecialchars($product['image_url'] ?? ''); ?>')">
                                    Add
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No Products</h3>
                <p>Check back soon</p>
                <a href="/CircleUp/store/" class="cta-button">View All</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- FOOTER -->
    <footer>
        <p>&copy; 2026 <a href="#">CircleUp</a> — Premium Apparel | <a href="/CircleUp/admin/login.php">Admin</a></p>
    </footer>

    <script src="cart.js"></script>
    <style>
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--red);
            color: var(--white-pure);
            padding: 14px 22px;
            border-radius: 0;
            font-size: 12px;
            z-index: 10000;
            animation: slideIn 0.3s ease;
            font-family: 'Oswald', sans-serif;
            letter-spacing: 1px;
            font-weight: 600;
            border: 1px solid var(--gold);
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</body>
</html>
