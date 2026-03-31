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
    <title>CircleUp — Premium Apparel for Winners</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
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
            font-family: 'Inter', -apple-system, sans-serif;
            background: #fafafa;
            color: #1a1a1a;
        }

        /* HEADER */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #fff;
            border-bottom: 1px solid #e8e8e8;
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 600;
            letter-spacing: -0.5px;
            color: #1a1a1a;
            text-decoration: none;
        }

        .logo span {
            color: #d4a574;
        }

        .header-nav {
            display: flex;
            gap: 32px;
            align-items: center;
        }

        .header-nav a {
            font-size: 13px;
            font-weight: 500;
            color: #666;
            text-decoration: none;
            transition: color 0.2s;
            letter-spacing: 0.3px;
        }

        .header-nav a:hover {
            color: #1a1a1a;
        }

        .cart-btn {
            width: 38px;
            height: 38px;
            background: #f5f5f5;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .cart-btn:hover {
            background: #f0f0f0;
            border-color: #d4a574;
        }

        /* HERO */
        .hero {
            margin-top: 60px;
            padding: 80px 40px;
            background: linear-gradient(180deg, #fafafa 0%, #f5f5f5 100%);
            border-bottom: 1px solid #e8e8e8;
            text-align: center;
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            font-weight: 600;
            line-height: 1.2;
            margin-bottom: 16px;
            color: #1a1a1a;
            letter-spacing: -0.8px;
        }

        .hero h1 .highlight {
            color: #d4a574;
        }

        .hero p {
            font-size: 16px;
            color: #666;
            max-width: 550px;
            margin: 0 auto 32px;
            line-height: 1.6;
            font-weight: 400;
        }

        .cta-button {
            display: inline-block;
            padding: 13px 28px;
            background: #1a1a1a;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            letter-spacing: 0.5px;
        }

        .cta-button:hover {
            background: #333;
            transform: translateY(-1px);
        }

        /* CATEGORIES */
        .categories-section {
            padding: 50px 40px;
            background: #fff;
            border-bottom: 1px solid #e8e8e8;
        }

        .categories-section-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 12px;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 32px;
            color: #1a1a1a;
            letter-spacing: -0.5px;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 12px;
        }

        .category-card {
            padding: 14px 16px;
            background: #f5f5f5;
            border: 1px solid #e8e8e8;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: #666;
            font-weight: 500;
            font-size: 13px;
        }

        .category-card:hover,
        .category-card.active {
            background: #1a1a1a;
            border-color: #1a1a1a;
            color: #fff;
        }

        .category-count {
            font-size: 11px;
            color: #999;
            margin-top: 4px;
            font-weight: 400;
        }

        .category-card:hover .category-count,
        .category-card.active .category-count {
            color: #ccc;
        }

        /* PRODUCTS SECTION */
        .products-section {
            padding: 50px 40px 80px;
            background: #fafafa;
        }

        .products-section-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 48px;
        }

        .products-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 600;
            color: #1a1a1a;
            letter-spacing: -0.5px;
        }

        .sort-select {
            padding: 9px 12px;
            background: #fff;
            border: 1px solid #e0e0e0;
            color: #666;
            border-radius: 4px;
            font-size: 12px;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            font-weight: 500;
        }

        .sort-select:focus {
            outline: none;
            border-color: #d4a574;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 32px;
            grid-auto-rows: auto;
        }

        .product-card {
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover .product-image {
            opacity: 0.95;
        }

        .product-image {
            width: 100%;
            aspect-ratio: 1 / 1;
            background: #f0f0f0;
            border-radius: 6px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            border: 1px solid #e8e8e8;
            transition: opacity 0.3s;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 12px;
            background: #fff;
        }

        .product-image.empty {
            color: #999;
            font-size: 12px;
        }

        .product-info {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-category {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 6px;
        }

        .product-name {
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 10px;
            color: #1a1a1a;
            line-height: 1.4;
            letter-spacing: -0.2px;
        }

        .product-desc {
            font-size: 13px;
            color: #888;
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
            border-top: 1px solid #e8e8e8;
        }

        .product-price {
            font-size: 15px;
            font-weight: 600;
            color: #1a1a1a;
            letter-spacing: -0.2px;
        }

        .add-to-cart {
            padding: 9px 16px;
            background: #1a1a1a;
            border: 1px solid #1a1a1a;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            font-size: 11px;
            letter-spacing: 0.5px;
            transition: all 0.2s;
            text-transform: uppercase;
        }

        .add-to-cart:hover {
            background: #333;
            border-color: #333;
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 100px 40px;
        }

        .empty-state h3 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            margin-bottom: 12px;
            color: #1a1a1a;
            font-weight: 600;
        }

        .empty-state p {
            color: #888;
            margin-bottom: 28px;
            font-size: 14px;
        }

        /* FOOTER */
        footer {
            background: #fff;
            border-top: 1px solid #e8e8e8;
            padding: 50px 40px;
            text-align: center;
            color: #999;
            font-size: 12px;
        }

        footer a {
            color: #1a1a1a;
            text-decoration: none;
            font-weight: 600;
            margin: 0 4px;
        }

        footer a:hover {
            text-decoration: underline;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            header {
                padding: 12px 20px;
            }

            .logo {
                font-size: 18px;
            }

            .header-nav {
                gap: 16px;
            }

            .hero {
                padding: 50px 20px;
                margin-top: 50px;
            }

            .hero h1 {
                font-size: 32px;
            }

            .hero p {
                font-size: 14px;
            }

            .categories-section,
            .products-section {
                padding-left: 20px;
                padding-right: 20px;
            }

            .section-title,
            .products-header h2 {
                font-size: 24px;
            }

            .categories-grid {
                grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 16px;
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
    <!-- HEADER -->
    <header>
        <a href="/CircleUp/store/" class="logo">Circle<span>Up</span></a>
        <nav class="header-nav">
            <a href="#all">Shop</a>
            <a href="/CircleUp/admin/login.php">Admin</a>
            <div class="cart-btn">🛒</div>
        </nav>
    </header>

    <!-- HERO -->
    <div class="hero">
        <h1>Premium Apparel for <span class="highlight">Winners</span></h1>
        <p>Thoughtfully designed. Built to last. For people who care about quality.</p>
        <a href="#products" class="cta-button">Shop Collection</a>
    </div>

    <!-- CATEGORIES -->
    <div class="categories-section">
        <div class="categories-section-content">
            <div class="section-label">Explore</div>
            <h2 class="section-title">Shop by Category</h2>
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
    </div>

    <!-- PRODUCTS -->
    <div class="products-section" id="products">
        <div class="products-section-content">
            <div class="products-header">
                <h2><?php echo $search ? 'Results' : 'Featured'; ?></h2>
                <select class="sort-select">
                    <option>Newest</option>
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
                                    No Image Available
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
                                    <button class="add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                                        Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No Products Available</h3>
                    <p>Check back soon for new collections</p>
                    <a href="/CircleUp/store/" class="cta-button">View All</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- FOOTER -->
    <footer>
        <p>&copy; 2026 <a href="#">CircleUp</a> | Premium apparel for high performers</p>
    </footer>

    <script>
        function addToCart(productId) {
            alert('Add to cart coming soon');
        }
    </script>
</body>
</html>
