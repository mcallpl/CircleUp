<?php
require_once '../config.php';

$db = getDB();
$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    header('Location: /CircleUp/store/');
    exit();
}

$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: /CircleUp/store/');
    exit();
}

// Get all images for this product
$stmt = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// If no images in product_images table, fall back to product.image_url
if (empty($images) && !empty($product['image_url'])) {
    $images = [['image_url' => $product['image_url'], 'sort_order' => 0]];
}

// Get variants
$stmt = $db->prepare("SELECT * FROM variants WHERE product_id = ? ORDER BY size, color");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$variants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get related products (same category, excluding current)
$stmt = $db->prepare("SELECT p.*, (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order ASC LIMIT 1) as first_image FROM products p WHERE p.category = ? AND p.id != ? ORDER BY RAND() LIMIT 4");
$stmt->bind_param("si", $product['category'], $product_id);
$stmt->execute();
$related = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> — CircleUp</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Barlow+Condensed:wght@400;600;700;800&display=swap" rel="stylesheet">
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

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Barlow Condensed', sans-serif;
            background: var(--navy);
            color: var(--white);
            line-height: 1.6;
        }

        .flag-stripes-top {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            z-index: 100;
            background: repeating-linear-gradient(
                90deg,
                var(--red) 0px, var(--red) 33.33%,
                var(--white-pure) 33.33%, var(--white-pure) 66.66%,
                var(--navy-mid) 66.66%, var(--navy-mid) 100%
            );
        }

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
            text-decoration: none;
            color: var(--white-pure);
        }

        .logo span { color: var(--red); }

        .header-nav {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .header-nav a {
            font-family: 'Oswald', sans-serif;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--gold);
            text-decoration: none;
            transition: color 0.3s;
        }

        .header-nav a:hover { color: var(--white-pure); }

        .cart-btn {
            font-size: 18px;
            position: relative;
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -10px;
            background: var(--red);
            color: var(--white-pure);
            font-size: 10px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Oswald', sans-serif;
        }

        /* ========== PRODUCT LAYOUT ========== */
        .product-container {
            max-width: 1200px;
            margin: 110px auto 0;
            padding: 40px 60px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: start;
        }

        /* ========== IMAGE GALLERY ========== */
        .gallery {
            position: sticky;
            top: 120px;
        }

        .hero-image {
            width: 100%;
            aspect-ratio: 1;
            background: var(--navy-light);
            border: 1px solid var(--gold);
            border-radius: 2px;
            overflow: hidden;
            cursor: zoom-in;
            position: relative;
        }

        .hero-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 20px;
            transition: opacity 0.3s ease;
        }

        .hero-image.zoomed {
            cursor: zoom-out;
        }

        .hero-image.zoomed img {
            object-fit: cover;
            padding: 0;
            transform-origin: var(--zoom-x, 50%) var(--zoom-y, 50%);
            transform: scale(2);
        }

        .thumbnails {
            display: flex;
            gap: 12px;
            margin-top: 16px;
        }

        .thumbnail {
            width: 80px;
            height: 80px;
            border: 2px solid transparent;
            border-radius: 2px;
            overflow: hidden;
            cursor: pointer;
            background: var(--navy-light);
            transition: border-color 0.3s, transform 0.2s;
            flex-shrink: 0;
        }

        .thumbnail:hover {
            border-color: var(--silver);
            transform: translateY(-2px);
        }

        .thumbnail.active {
            border-color: var(--gold);
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 4px;
        }

        /* ========== PRODUCT DETAILS ========== */
        .product-details {
            padding-top: 10px;
        }

        .breadcrumb {
            font-size: 12px;
            color: var(--chrome);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .breadcrumb a {
            color: var(--gold);
            text-decoration: none;
        }

        .breadcrumb a:hover { color: var(--white-pure); }

        .product-name {
            font-family: 'Oswald', sans-serif;
            font-size: 40px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--white-pure);
            line-height: 1.15;
            margin-bottom: 8px;
        }

        .product-category {
            font-family: 'Oswald', sans-serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 20px;
        }

        .product-price {
            font-family: 'Oswald', sans-serif;
            font-size: 36px;
            font-weight: 700;
            color: var(--gold-bright);
            margin-bottom: 24px;
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, var(--gold), transparent);
            margin: 24px 0;
            opacity: 0.4;
        }

        .product-description {
            font-size: 16px;
            line-height: 1.8;
            color: var(--silver-light);
            margin-bottom: 30px;
        }

        /* Variant selectors */
        .variant-section {
            margin-bottom: 24px;
        }

        .variant-label {
            font-family: 'Oswald', sans-serif;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 10px;
        }

        .variant-options {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .variant-option {
            padding: 10px 20px;
            border: 1px solid var(--gold);
            background: transparent;
            color: var(--white);
            font-family: 'Oswald', sans-serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s;
            border-radius: 2px;
        }

        .variant-option:hover {
            background: var(--navy-light);
            border-color: var(--white-pure);
        }

        .variant-option.selected {
            background: var(--gold);
            color: var(--navy);
            border-color: var(--gold);
        }

        /* Quantity */
        .quantity-row {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            border: 1px solid var(--gold);
            border-radius: 2px;
        }

        .quantity-control button {
            width: 40px;
            height: 40px;
            background: transparent;
            border: none;
            color: var(--gold);
            font-size: 18px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .quantity-control button:hover {
            background: var(--navy-light);
        }

        .quantity-control input {
            width: 50px;
            height: 40px;
            text-align: center;
            background: transparent;
            border: none;
            border-left: 1px solid var(--gold);
            border-right: 1px solid var(--gold);
            color: var(--white-pure);
            font-family: 'Oswald', sans-serif;
            font-size: 16px;
            font-weight: 600;
        }

        .quantity-control input:focus { outline: none; }

        .add-to-cart-btn {
            width: 100%;
            padding: 16px;
            background: var(--red);
            color: var(--white-pure);
            border: 2px solid var(--gold);
            border-radius: 2px;
            font-family: 'Oswald', sans-serif;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
        }

        .add-to-cart-btn:hover {
            background: var(--red-bright);
            box-shadow: 0 0 20px rgba(232, 41, 59, 0.4);
        }

        .add-to-cart-btn:active {
            transform: scale(0.98);
        }

        /* ========== RELATED PRODUCTS ========== */
        .related-section {
            max-width: 1200px;
            margin: 80px auto 0;
            padding: 0 60px 40px;
        }

        .related-title {
            font-family: 'Oswald', sans-serif;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--gold-bright);
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 24px;
        }

        .related-card {
            background: var(--navy-light);
            border: 1px solid transparent;
            border-radius: 2px;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            transition: border-color 0.3s, transform 0.3s;
        }

        .related-card:hover {
            border-color: var(--red);
            transform: translateY(-4px);
        }

        .related-card-image {
            aspect-ratio: 1;
            background: var(--navy-mid);
            overflow: hidden;
        }

        .related-card-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 15px;
        }

        .related-card-info {
            padding: 14px 16px;
        }

        .related-card-name {
            font-family: 'Oswald', sans-serif;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1px;
            color: var(--white-pure);
            margin-bottom: 4px;
        }

        .related-card-price {
            font-family: 'Oswald', sans-serif;
            font-size: 16px;
            font-weight: 700;
            color: var(--gold-bright);
        }

        /* ========== FOOTER ========== */
        footer {
            background: var(--navy-mid);
            border-top: 2px solid var(--gold);
            padding: 40px 60px 30px;
            text-align: center;
            margin-top: 60px;
        }

        .footer-nav {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 16px;
        }

        .footer-nav a {
            color: var(--gold);
            text-decoration: none;
            font-family: 'Oswald', sans-serif;
            font-weight: 600;
            font-size: 12px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            transition: color 0.3s;
        }

        .footer-nav a:hover { color: var(--white-pure); }

        .footer-dot {
            width: 4px;
            height: 4px;
            background: var(--gold);
            border-radius: 50%;
            opacity: 0.5;
        }

        footer p {
            color: var(--chrome);
            font-size: 11px;
            letter-spacing: 1px;
        }

        /* ========== NOTIFICATION ========== */
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--red);
            color: var(--white-pure);
            padding: 14px 22px;
            font-size: 12px;
            z-index: 10000;
            animation: slideIn 0.3s ease;
            font-family: 'Oswald', sans-serif;
            letter-spacing: 1px;
            font-weight: 600;
            border: 1px solid var(--gold);
            border-radius: 2px;
        }

        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            header { padding: 15px 20px; }

            .product-container {
                grid-template-columns: 1fr;
                padding: 20px;
                gap: 30px;
                margin-top: 90px;
            }

            .gallery { position: static; }

            .product-name { font-size: 28px; }
            .product-price { font-size: 28px; }

            .thumbnails { gap: 8px; }
            .thumbnail { width: 60px; height: 60px; }

            .related-section { padding: 0 20px 40px; }
            .related-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }

            footer { padding: 30px 20px 20px; }
        }
    </style>
</head>
<body>
    <div class="flag-stripes-top"></div>

    <header>
        <a href="/CircleUp/store/" class="logo">Circle<span>Up</span></a>
        <nav class="header-nav">
            <a href="/CircleUp/">Home</a>
            <a href="/CircleUp/store/">Shop</a>
            <a href="/CircleUp/admin/login.php">Admin</a>
            <a href="/CircleUp/store/cart.php" style="position: relative;">
                <div class="cart-btn">🛒<span class="cart-badge"></span></div>
            </a>
        </nav>
    </header>

    <div class="product-container">
        <!-- IMAGE GALLERY -->
        <div class="gallery">
            <div class="hero-image" id="hero-image" onclick="toggleZoom(event)">
                <?php if (!empty($images)): ?>
                    <img id="hero-img" src="<?php echo htmlspecialchars($images[0]['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <?php else: ?>
                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--chrome); font-size: 18px;">No Image</div>
                <?php endif; ?>
            </div>

            <?php if (count($images) > 1): ?>
                <div class="thumbnails">
                    <?php foreach ($images as $idx => $img): ?>
                        <div class="thumbnail <?php echo $idx === 0 ? 'active' : ''; ?>" onclick="switchImage(this, '<?php echo htmlspecialchars($img['image_url']); ?>')">
                            <img src="<?php echo htmlspecialchars($img['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- PRODUCT DETAILS -->
        <div class="product-details">
            <div class="breadcrumb">
                <a href="/CircleUp/store/">Shop</a> /
                <a href="/CircleUp/store/?category=<?php echo urlencode($product['category']); ?>"><?php echo htmlspecialchars(PRODUCT_CATEGORIES[$product['category']] ?? $product['category']); ?></a> /
                <?php echo htmlspecialchars($product['name']); ?>
            </div>

            <h1 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="product-category"><?php echo htmlspecialchars(PRODUCT_CATEGORIES[$product['category']] ?? $product['category']); ?></div>
            <div class="product-price">$<?php echo number_format($product['price'], 0); ?></div>

            <div class="divider"></div>

            <?php if (!empty($product['description'])): ?>
                <div class="product-description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></div>
            <?php endif; ?>

            <?php
            // Extract unique sizes and colors from variants
            $sizes = [];
            $colors = [];
            foreach ($variants as $v) {
                if (!empty($v['size']) && !in_array($v['size'], $sizes)) $sizes[] = $v['size'];
                if (!empty($v['color']) && !in_array($v['color'], $colors)) $colors[] = $v['color'];
            }
            ?>

            <?php if (!empty($sizes)): ?>
                <div class="variant-section">
                    <div class="variant-label">Size</div>
                    <div class="variant-options" id="size-options">
                        <?php foreach ($sizes as $size): ?>
                            <button type="button" class="variant-option" data-type="size" data-value="<?php echo htmlspecialchars($size); ?>" onclick="selectVariant(this, 'size')">
                                <?php echo htmlspecialchars(PRODUCT_SIZES[$size] ?? strtoupper($size)); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($colors)): ?>
                <div class="variant-section">
                    <div class="variant-label">Color</div>
                    <div class="variant-options" id="color-options">
                        <?php foreach ($colors as $color): ?>
                            <button type="button" class="variant-option" data-type="color" data-value="<?php echo htmlspecialchars($color); ?>" onclick="selectVariant(this, 'color')">
                                <?php echo htmlspecialchars(PRODUCT_COLORS[$color] ?? ucfirst($color)); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="quantity-row">
                <div class="variant-label" style="margin-bottom: 0;">Quantity</div>
                <div class="quantity-control">
                    <button type="button" onclick="changeQty(-1)">−</button>
                    <input type="number" id="qty" value="1" min="1" readonly>
                    <button type="button" onclick="changeQty(1)">+</button>
                </div>
            </div>

            <button class="add-to-cart-btn" id="add-to-cart" onclick="addProductToCart()">
                Add to Cart
            </button>
        </div>
    </div>

    <?php if (!empty($related)): ?>
    <div class="related-section">
        <h2 class="related-title">You May Also Like</h2>
        <div class="related-grid">
            <?php foreach ($related as $rel): ?>
                <a href="/CircleUp/store/product_page.php?id=<?php echo $rel['id']; ?>" class="related-card">
                    <div class="related-card-image">
                        <?php $rel_img = $rel['first_image'] ?: $rel['image_url']; ?>
                        <?php if ($rel_img): ?>
                            <img src="<?php echo htmlspecialchars($rel_img); ?>" alt="<?php echo htmlspecialchars($rel['name']); ?>" loading="lazy">
                        <?php endif; ?>
                    </div>
                    <div class="related-card-info">
                        <div class="related-card-name"><?php echo htmlspecialchars($rel['name']); ?></div>
                        <div class="related-card-price">$<?php echo number_format($rel['price'], 0); ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <footer>
        <nav class="footer-nav">
            <a href="/CircleUp/">Home</a>
            <span class="footer-dot"></span>
            <a href="/CircleUp/store/">Shop</a>
            <span class="footer-dot"></span>
            <a href="/CircleUp/store/cart.php">Cart</a>
            <span class="footer-dot"></span>
            <a href="/CircleUp/admin/login.php">Admin</a>
        </nav>
        <p>&copy; 2026 CircleUp — Premium Apparel</p>
    </footer>

    <script src="cart.js"></script>
    <script>
        // ========== IMAGE GALLERY ==========
        function switchImage(thumb, url) {
            const hero = document.getElementById('hero-img');
            const heroContainer = document.getElementById('hero-image');

            // Remove zoom if active
            heroContainer.classList.remove('zoomed');

            // Fade transition
            hero.style.opacity = '0';
            setTimeout(() => {
                hero.src = url;
                hero.style.opacity = '1';
            }, 150);

            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
        }

        function toggleZoom(e) {
            const container = document.getElementById('hero-image');
            if (!document.getElementById('hero-img')) return;

            if (container.classList.contains('zoomed')) {
                container.classList.remove('zoomed');
            } else {
                const rect = container.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;
                container.style.setProperty('--zoom-x', x + '%');
                container.style.setProperty('--zoom-y', y + '%');
                container.classList.add('zoomed');
            }
        }

        // ========== VARIANT SELECTION ==========
        let selectedSize = null;
        let selectedColor = null;

        function selectVariant(btn, type) {
            // Toggle selection within group
            const siblings = btn.parentElement.querySelectorAll('.variant-option');
            siblings.forEach(s => s.classList.remove('selected'));
            btn.classList.add('selected');

            if (type === 'size') selectedSize = btn.dataset.value;
            if (type === 'color') selectedColor = btn.dataset.value;
        }

        // ========== QUANTITY ==========
        function changeQty(delta) {
            const input = document.getElementById('qty');
            const newVal = Math.max(1, parseInt(input.value) + delta);
            input.value = newVal;
        }

        // ========== ADD TO CART ==========
        function addProductToCart() {
            const qty = parseInt(document.getElementById('qty').value);
            const product = {
                id: <?php echo $product['id']; ?>,
                name: <?php echo json_encode($product['name']); ?>,
                price: <?php echo $product['price']; ?>,
                category: <?php echo json_encode($product['category']); ?>,
                image: <?php echo json_encode(!empty($images) ? $images[0]['image_url'] : ''); ?>
            };

            for (let i = 0; i < qty; i++) {
                cart.addProduct(product.id, product.name, product.price, product.category, product.image);
            }

            // Show notification
            const note = document.createElement('div');
            note.className = 'notification';
            note.textContent = qty + 'x ' + product.name + ' added to cart';
            document.body.appendChild(note);
            setTimeout(() => note.remove(), 2500);
        }
    </script>
</body>
</html>
