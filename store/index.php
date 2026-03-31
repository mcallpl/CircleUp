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
    <title>CircleUp — Premium American Apparel</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Barlow', -apple-system, sans-serif;
            background: #fff;
            color: #000;
            line-height: 1.6;
        }

        /* HEADER */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #fff;
            border-bottom: 2px solid #000;
            padding: 20px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Oswald', sans-serif;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #000;
            text-decoration: none;
        }

        .logo span {
            color: #c41e3a;
        }

        .header-nav {
            display: flex;
            gap: 50px;
            align-items: center;
        }

        .header-nav a {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #000;
            text-decoration: none;
            transition: color 0.3s;
        }

        .header-nav a:hover {
            color: #c41e3a;
        }

        .cart-btn {
            width: 45px;
            height: 45px;
            background: #000;
            border: 2px solid #000;
            border-radius: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .cart-btn:hover {
            background: #c41e3a;
            border-color: #c41e3a;
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #c41e3a;
            color: #fff;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            font-family: 'Oswald', sans-serif;
        }

        /* HERO */
        .hero {
            margin-top: 85px;
            padding: 100px 60px;
            background: linear-gradient(135deg, #000 0%, #1a1a1a 100%);
            color: #fff;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('../circleup-hero.png') center/cover;
            opacity: 0.15;
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 72px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 20px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .hero h1 span {
            color: #c41e3a;
        }

        .hero p {
            font-size: 20px;
            font-weight: 300;
            max-width: 700px;
            margin: 0 auto 40px;
            line-height: 1.8;
            letter-spacing: 1px;
        }

        .cta-button {
            display: inline-block;
            padding: 18px 50px;
            background: #c41e3a;
            color: #fff;
            border: 3px solid #c41e3a;
            border-radius: 0;
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            letter-spacing: 2px;
            text-transform: uppercase;
            transition: all 0.3s;
        }

        .cta-button:hover {
            background: transparent;
            color: #c41e3a;
        }

        /* CATEGORIES */
        .categories-section {
            padding: 80px 60px;
            background: #f5f5f5;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }

        .section-title {
            font-family: 'Oswald', sans-serif;
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 50px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 20px;
        }

        .category-card {
            padding: 25px;
            background: #fff;
            border: 2px solid #000;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #000;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 1px;
            text-transform: uppercase;
            position: relative;
        }

        .category-card:hover,
        .category-card.active {
            background: #000;
            color: #fff;
        }

        .category-count {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 8px;
        }

        /* PRODUCTS SECTION */
        .products-section {
            padding: 80px 60px;
            background: #fff;
        }

        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 60px;
            border-bottom: 3px solid #000;
            padding-bottom: 30px;
        }

        .products-header h2 {
            font-family: 'Oswald', sans-serif;
            font-size: 48px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .sort-select {
            padding: 12px 16px;
            background: #fff;
            border: 2px solid #000;
            color: #000;
            border-radius: 0;
            font-size: 12px;
            font-family: 'Barlow', sans-serif;
            cursor: pointer;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .sort-select:focus {
            outline: none;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 40px;
            grid-auto-rows: auto;
        }

        .product-card {
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            border: 2px solid #f0f0f0;
            padding: 0;
        }

        .product-card:hover {
            border-color: #000;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        .product-image {
            width: 100%;
            aspect-ratio: 1 / 1;
            background: #f5f5f5;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0;
            border-bottom: 2px solid #f0f0f0;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 20px;
            background: #fff;
        }

        .product-image.empty {
            color: #999;
            font-size: 13px;
        }

        .product-info {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            padding: 25px;
        }

        .product-category {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #c41e3a;
            margin-bottom: 10px;
        }

        .product-name {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #000;
            line-height: 1.4;
            letter-spacing: 0.5px;
        }

        .product-desc {
            font-size: 13px;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
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
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }

        .product-price {
            font-family: 'Oswald', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: #000;
            letter-spacing: 1px;
        }

        .add-to-cart {
            padding: 10px 18px;
            background: #000;
            border: 2px solid #000;
            color: #fff;
            border-radius: 0;
            cursor: pointer;
            font-family: 'Oswald', sans-serif;
            font-weight: 600;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            transition: all 0.3s;
        }

        .add-to-cart:hover {
            background: #c41e3a;
            border-color: #c41e3a;
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 100px 40px;
        }

        .empty-state h3 {
            font-family: 'Oswald', sans-serif;
            font-size: 36px;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 32px;
            font-size: 15px;
            letter-spacing: 1px;
        }

        /* FOOTER */
        footer {
            background: #000;
            color: #fff;
            padding: 60px;
            text-align: center;
            border-top: 3px solid #c41e3a;
            font-size: 12px;
            letter-spacing: 1px;
        }

        footer a {
            color: #c41e3a;
            text-decoration: none;
            font-weight: 600;
        }

        footer a:hover {
            text-decoration: underline;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            header {
                padding: 15px 30px;
            }

            .logo {
                font-size: 24px;
                letter-spacing: 2px;
            }

            .header-nav {
                gap: 20px;
            }

            .hero {
                padding: 50px 30px;
                margin-top: 70px;
            }

            .hero h1 {
                font-size: 42px;
            }

            .hero p {
                font-size: 16px;
            }

            .categories-section,
            .products-section {
                padding-left: 30px;
                padding-right: 30px;
            }

            .section-title,
            .products-header h2 {
                font-size: 32px;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 20px;
            }

            .products-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
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
            <p>Premium American Apparel for Champions</p>
            <a href="#products" class="cta-button">Shop Now</a>
        </div>
    </div>

    <!-- CATEGORIES -->
    <div class="categories-section">
        <h2 class="section-title">Collections</h2>
        <div class="categories-grid">
            <a href="/CircleUp/store/" class="category-card <?php echo !$category ? 'active' : ''; ?>">
                All
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
            <h2><?php echo $search ? 'Search Results' : 'Featured Collection'; ?></h2>
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
                                <?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 90)); ?>
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
                <h3>No Products Found</h3>
                <p>Check back soon for new collections</p>
                <a href="/CircleUp/store/" class="cta-button">View All</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- FOOTER -->
    <footer>
        <p>&copy; 2026 <a href="#">CircleUp</a> — Premium American Apparel | <a href="/CircleUp/admin/login.php">Admin</a></p>
    </footer>

    <script src="cart.js"></script>
    <style>
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #c41e3a;
            color: #fff;
            padding: 16px 24px;
            border-radius: 0;
            font-size: 13px;
            z-index: 10000;
            animation: slideIn 0.3s ease;
            font-family: 'Oswald', sans-serif;
            letter-spacing: 1px;
            font-weight: 600;
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
