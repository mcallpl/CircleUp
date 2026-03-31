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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
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
            background: #0a0e27;
            color: #f0f0f0;
            line-height: 1.6;
        }

        /* HEADER */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(10, 14, 39, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -1px;
            color: #fff;
            text-decoration: none;
        }

        .logo span {
            color: #FFD700;
        }

        .header-nav {
            display: flex;
            gap: 40px;
            align-items: center;
        }

        .header-nav a {
            font-size: 14px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.2s;
        }

        .header-nav a:hover {
            color: #FFD700;
        }

        .cart-btn {
            width: 40px;
            height: 40px;
            background: rgba(255, 215, 0, 0.1);
            border: 1px solid rgba(255, 215, 0, 0.3);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .cart-btn:hover {
            background: rgba(255, 215, 0, 0.2);
            border-color: #FFD700;
        }

        /* HERO */
        .hero {
            margin-top: 70px;
            padding: 100px 40px;
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.05) 0%, rgba(255, 215, 0, 0.02) 100%);
            border-bottom: 1px solid rgba(255, 215, 0, 0.1);
            text-align: center;
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 56px;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 20px;
            color: #fff;
            letter-spacing: -1px;
        }

        .hero h1 .highlight {
            color: #FFD700;
        }

        .hero p {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.7);
            max-width: 600px;
            margin: 0 auto 30px;
            line-height: 1.6;
        }

        .cta-button {
            display: inline-block;
            padding: 14px 32px;
            background: #FFD700;
            color: #0a0e27;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            letter-spacing: 0.5px;
        }

        .cta-button:hover {
            background: #FFF;
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
        }

        /* CATEGORIES */
        .categories-section {
            padding: 60px 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-label {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #FFD700;
            margin-bottom: 12px;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 40px;
            color: #fff;
            letter-spacing: -0.5px;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 16px;
            margin-bottom: 60px;
        }

        .category-card {
            padding: 20px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 215, 0, 0.15);
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }

        .category-card:hover,
        .category-card.active {
            background: rgba(255, 215, 0, 0.1);
            border-color: #FFD700;
            color: #fff;
        }

        .category-count {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 8px;
        }

        /* PRODUCTS */
        .products-section {
            padding: 0 40px 80px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 50px;
        }

        .products-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.5px;
        }

        .sort-select {
            padding: 10px 16px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 215, 0, 0.2);
            color: #fff;
            border-radius: 6px;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
        }

        .sort-select:focus {
            outline: none;
            border-color: #FFD700;
            background: rgba(255, 215, 0, 0.08);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 40px;
        }

        .product-card {
            cursor: pointer;
            transition: all 0.3s;
            group: true;
        }

        .product-card:hover {
            transform: translateY(-8px);
        }

        .product-image {
            width: 100%;
            aspect-ratio: 1;
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(255, 215, 0, 0.05));
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            border: 1px solid rgba(255, 215, 0, 0.1);
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-image.empty {
            color: rgba(255, 255, 255, 0.3);
            font-size: 14px;
        }

        .product-info {
            padding: 0;
        }

        .product-category {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #FFD700;
            margin-bottom: 8px;
        }

        .product-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #fff;
            line-height: 1.4;
        }

        .product-desc {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 18px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 18px;
            border-top: 1px solid rgba(255, 215, 0, 0.1);
        }

        .product-price {
            font-size: 22px;
            font-weight: 700;
            color: #FFD700;
        }

        .add-to-cart {
            padding: 10px 20px;
            background: rgba(255, 215, 0, 0.1);
            border: 1px solid rgba(255, 215, 0, 0.3);
            color: #FFD700;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
            letter-spacing: 0.5px;
            transition: all 0.2s;
            text-transform: uppercase;
        }

        .add-to-cart:hover {
            background: #FFD700;
            color: #0a0e27;
            border-color: #FFD700;
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 100px 40px;
        }

        .empty-state h3 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            margin-bottom: 16px;
            color: #fff;
        }

        .empty-state p {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 32px;
        }

        /* FOOTER */
        footer {
            background: rgba(0, 0, 0, 0.4);
            border-top: 1px solid rgba(255, 215, 0, 0.1);
            padding: 60px 40px;
            text-align: center;
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
        }

        footer a {
            color: #FFD700;
            text-decoration: none;
            font-weight: 600;
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
                font-size: 20px;
            }

            .header-nav {
                gap: 20px;
            }

            .hero {
                padding: 60px 20px;
                margin-top: 60px;
            }

            .hero h1 {
                font-size: 36px;
            }

            .hero p {
                font-size: 15px;
            }

            .categories-section,
            .products-section {
                padding-left: 20px;
                padding-right: 20px;
            }

            .section-title,
            .products-header h2 {
                font-size: 28px;
            }

            .categories-grid {
                grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
                gap: 12px;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header>
        <a href="/CircleUp/store/" class="logo">Circle<span>Up</span></a>
        <nav class="header-nav">
            <a href="/CircleUp/admin/login.php">Admin</a>
            <div class="cart-btn">🛒</div>
        </nav>
    </header>

    <!-- HERO -->
    <div class="hero">
        <h1>Premium Apparel for <span class="highlight">Winners</span></h1>
        <p>Quality products. Bold design. Built for people who mean business.</p>
        <a href="#products" class="cta-button">Shop Now</a>
    </div>

    <!-- CATEGORIES -->
    <div class="categories-section">
        <div class="section-label">Collections</div>
        <h2 class="section-title">Browse by Category</h2>
        <div class="categories-grid">
            <a href="/CircleUp/store/" class="category-card <?php echo !$category ? 'active' : ''; ?>">
                All
                <div class="category-count">(<?php echo count($products); ?>)</div>
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="/CircleUp/store/?category=<?php echo urlencode($cat['category']); ?>" 
                   class="category-card <?php echo $category === $cat['category'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars(PRODUCT_CATEGORIES[$cat['category']] ?? $cat['category']); ?>
                    <div class="category-count">(<?php echo $cat['count']; ?>)</div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- PRODUCTS -->
    <div class="products-section" id="products">
        <div class="products-header">
            <h2><?php echo $search ? 'Search Results' : 'Latest Collection'; ?></h2>
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
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <div class="product-category">
                                <?php echo htmlspecialchars(PRODUCT_CATEGORIES[$product['category']] ?? $product['category']); ?>
                            </div>
                            <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-desc"><?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 100)); ?></div>
                            <div class="product-footer">
                                <div class="product-price">$<?php echo number_format($product['price'], 0); ?></div>
                                <button class="add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">Add</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No Products Found</h3>
                <p>Check back soon for new collections</p>
                <a href="/CircleUp/store/" class="cta-button">View All Products</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- FOOTER -->
    <footer>
        <p>&copy; 2026 CircleUp. Premium apparel for high performers. | <a href="/CircleUp/admin/login.php">Admin</a></p>
    </footer>

    <script>
        function addToCart(productId) {
            alert('Add to cart coming soon');
        }
    </script>
</body>
</html>
