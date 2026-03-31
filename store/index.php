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

// Fetch popular categories
$categories_result = $db->query("SELECT DISTINCT category, COUNT(*) as count FROM products GROUP BY category LIMIT 6");
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo PRODUCT_CATEGORIES[$category] ?? 'CircleUp Store'; ?> - Shop Premium Apparel</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
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
            font-family: 'DM Sans', -apple-system, sans-serif;
            background: #fff;
            color: #333;
            line-height: 1.6;
        }
        
        /* Header */
        header {
            background: white;
            border-bottom: 1px solid #eee;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
            font-weight: 600;
            color: #667eea;
        }
        
        .search-bar {
            flex: 1;
            max-width: 400px;
            margin: 0 30px;
        }
        
        .search-bar form {
            display: flex;
            gap: 10px;
        }
        
        .search-bar input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .search-bar button {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .header-right {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .cart-icon {
            font-size: 24px;
            cursor: pointer;
            color: #333;
        }
        
        .admin-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        
        /* Hero */
        .hero {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            padding: 60px 20px;
            text-align: center;
        }
        
        .hero h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 48px;
            margin-bottom: 15px;
            color: #333;
        }
        
        .hero p {
            font-size: 18px;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Categories */
        .categories {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .categories h2 {
            font-size: 20px;
            margin-bottom: 20px;
            font-family: 'Cormorant Garamond', serif;
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .category-card {
            text-align: center;
            padding: 20px;
            border: 2px solid #eee;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: #333;
        }
        
        .category-card:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }
        
        .category-card.active {
            border-color: #667eea;
            background: #667eea;
            color: white;
        }
        
        .category-count {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        
        .category-card.active .category-count {
            color: rgba(255,255,255,0.8);
        }
        
        /* Products Grid */
        .products-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .products-header h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
        }
        
        .sort-select {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }
        
        .product-card {
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .product-image {
            width: 100%;
            aspect-ratio: 1;
            background: #f5f5f5;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .no-image {
            color: #999;
            font-size: 14px;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-name {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        
        .product-desc {
            font-size: 13px;
            color: #666;
            margin-bottom: 12px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .product-price {
            font-size: 20px;
            font-weight: 600;
            color: #667eea;
        }
        
        .add-to-cart {
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: background 0.2s;
        }
        
        .add-to-cart:hover {
            background: #5568d3;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 10px;
            font-family: 'Cormorant Garamond', serif;
        }
        
        /* Footer */
        footer {
            background: #333;
            color: #fff;
            padding: 40px 20px;
            margin-top: 60px;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <div class="logo">CircleUp</div>
            
            <div class="search-bar">
                <form method="GET">
                    <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    <button type="submit">Search</button>
                </form>
            </div>
            
            <div class="header-right">
                <div class="cart-icon">🛒</div>
                <a href="/CircleUp/admin/login.php" class="admin-link">Admin</a>
            </div>
        </div>
    </header>
    
    <!-- Hero -->
    <div class="hero">
        <h1><?php echo $category ? PRODUCT_CATEGORIES[$category] : 'CircleUp Store'; ?></h1>
        <p>Premium apparel and accessories, beautifully curated</p>
    </div>
    
    <!-- Categories -->
    <div class="categories">
        <h2>Browse by Category</h2>
        <div class="categories-grid">
            <a href="/CircleUp/store/" class="category-card <?php echo !$category ? 'active' : ''; ?>">
                All Products
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
    
    <!-- Products -->
    <div class="products-container">
        <div class="products-header">
            <h2><?php echo $search ? 'Search Results' : 'Shop'; ?></h2>
            <select class="sort-select">
                <option>Sort: Newest</option>
                <option>Price: Low to High</option>
                <option>Price: High to Low</option>
                <option>Most Popular</option>
            </select>
        </div>
        
        <?php if (!empty($products)): ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card" onclick="viewProduct(<?php echo $product['id']; ?>)">
                        <div class="product-image">
                            <?php if ($product['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <div class="no-image">No image</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-desc"><?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 80)); ?></div>
                            <div class="product-footer">
                                <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                                <button class="add-to-cart" onclick="event.stopPropagation(); addToCart(<?php echo $product['id']; ?>)">Add</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No Products Found</h3>
                <p>Try adjusting your filters or search terms</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Footer -->
    <footer>
        <p>&copy; 2026 CircleUp. All rights reserved. | <a href="/CircleUp/admin/login.php" style="color: #999;">Admin Panel</a></p>
    </footer>
    
    <script>
        function viewProduct(id) {
            // TODO: Implement product detail page
            alert('Product detail page coming soon!');
        }
        
        function addToCart(id) {
            alert('Add to cart functionality coming soon!');
        }
    </script>
</body>
</html>
