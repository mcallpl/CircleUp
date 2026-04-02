<?php
require_once '../config.php';
require_once './auth.php';

requireAdmin();

$db = getDB();
$admin = getCurrentAdmin();
$product_id = $_GET['id'] ?? null;
$product = null;
$variants = [];

if ($product_id) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if (!$product) {
        header('Location: /CircleUp/admin/dashboard.php');
        exit();
    }
    
    $stmt = $db->prepare("SELECT * FROM variants WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $variants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $category = $_POST['category'] ?? '';
    $image_url = '';
    
    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit (upload_max_filesize)',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Server missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Server failed to write file to disk',
            ];
            $error = $upload_errors[$file['error']] ?? 'Unknown upload error (code ' . $file['error'] . ')';
        } elseif ($file['size'] > MAX_FILE_SIZE) {
            $error = 'File too large (max 5MB)';
        } elseif (!in_array($file['type'], ALLOWED_TYPES)) {
            $error = 'Invalid file type. Only JPG, PNG, WebP allowed.';
        } else {
            $filename = uniqid('product_') . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $filepath = UPLOAD_DIR . $filename;

            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $image_url = UPLOAD_URL . $filename;
            } else {
                $error = 'Failed to upload image — check directory permissions';
            }
        }
    } elseif ($product && !empty($product['image_url'])) {
        $image_url = $product['image_url'];
    }
    
    if (!$error) {
        if ($product_id) {
            // Update
            $stmt = $db->prepare("UPDATE products SET name = ?, description = ?, price = ?, category = ?, image_url = ? WHERE id = ?");
            $stmt->bind_param("ssdssi", $name, $description, $price, $category, $image_url, $product_id);
        } else {
            // Insert
            $stmt = $db->prepare("INSERT INTO products (name, description, price, category, image_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdss", $name, $description, $price, $category, $image_url);
        }
        
        if ($stmt->execute()) {
            $product_id = $product_id ?: $db->insert_id;
            
            // Handle variants
            if (!empty($_POST['variants'])) {
                $db->query("DELETE FROM variants WHERE product_id = $product_id");
                
                foreach ($_POST['variants'] as $variant) {
                    if (!empty($variant['size']) || !empty($variant['color']) || !empty($variant['stock'])) {
                        $size = $variant['size'] ?? null;
                        $color = $variant['color'] ?? null;
                        $stock = $variant['stock'] ?? 0;
                        $sku = strtoupper($category . '-' . str_replace(' ', '', $name) . '-' . ($color ?? 'NOCOLOR') . '-' . ($size ?? 'ONESIZE'));
                        
                        $stmt = $db->prepare("INSERT INTO variants (product_id, size, color, stock, sku) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("issss", $product_id, $size, $color, $stock, $sku);
                        $stmt->execute();
                    }
                }
            }
            
            logAction($admin['id'], 'product_' . ($product ? 'updated' : 'created'), ['product_id' => $product_id, 'name' => $name]);
            $success = 'Product ' . ($product ? 'updated' : 'created') . ' successfully!';
            
            $redirect = $admin['role'] === 'editor' ? '/CircleUp/admin/editor-dashboard.php' : '/CircleUp/admin/dashboard.php';
            header("Location: $redirect?success=1");
            exit();
        } else {
            $error = 'Failed to save product: ' . $db->error;
        }
    }
}

$is_edit = $product !== null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit' : 'Add'; ?> Product - CircleUp Admin</title>
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

        .navbar-links {
            display: flex;
            gap: 24px;
        }

        .navbar-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.5px;
            transition: color 0.3s;
        }

        .navbar-links a:hover {
            color: #333;
        }

        .product-footer {
            background: white;
            border-top: 1px solid #eee;
            padding: 30px 60px 24px;
            text-align: center;
            margin-top: 40px;
        }

        .product-footer .footer-nav {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 12px;
        }

        .product-footer .footer-nav a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
            letter-spacing: 0.5px;
            transition: color 0.3s;
        }

        .product-footer .footer-nav a:hover {
            color: #333;
        }

        .product-footer .footer-dot {
            width: 4px;
            height: 4px;
            background: #667eea;
            border-radius: 50%;
            opacity: 0.4;
            display: inline-block;
        }

        .product-footer p {
            color: #aaa;
            font-size: 11px;
        }
        
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        h1 {
            margin-bottom: 30px;
            font-size: 28px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="number"],
        input[type="email"],
        select,
        textarea {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        
        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .image-preview {
            margin-top: 10px;
        }
        
        .image-preview img {
            max-width: 200px;
            border-radius: 6px;
        }
        
        .variants-section {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #eee;
        }
        
        .variants-section h2 {
            font-size: 20px;
            margin-bottom: 20px;
        }
        
        .variant-item {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 10px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 6px;
            margin-bottom: 10px;
            align-items: flex-end;
        }
        
        .variant-item button {
            padding: 10px 15px;
            background: #ff6b6b;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-secondary {
            background: #e9ecef;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #dee2e6;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>CircleUp Admin</h1>
        <nav class="navbar-links">
            <a href="/CircleUp/">Home</a>
            <a href="/CircleUp/store/">Shop</a>
            <a href="<?php echo $admin['role'] === 'editor' ? '/CircleUp/admin/editor-dashboard.php' : '/CircleUp/admin/dashboard.php'; ?>">Dashboard</a>
        </nav>
    </div>
    
    <div class="container">
        <div class="card">
            <h1><?php echo $is_edit ? 'Edit Product' : 'Add New Product'; ?></h1>
            
            <?php if ($success): ?>
                <div class="alert success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price *</label>
                        <input type="number" id="price" name="price" step="0.01" required value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <?php foreach (PRODUCT_CATEGORIES as $key => $label): ?>
                                <option value="<?php echo $key; ?>" <?php echo ($product['category'] ?? '') === $key ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="image">Product Image</label>
                        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                        <?php if ($product && $product['image_url']): ?>
                            <div class="image-preview">
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="variants-section">
                    <h2>Variants (Sizes & Colors)</h2>
                    <div id="variants-container">
                        <?php if (!empty($variants)): ?>
                            <?php foreach ($variants as $variant): ?>
                                <div class="variant-item">
                                    <div class="form-group">
                                        <label>Size</label>
                                        <select name="variants[][size]">
                                            <option value="">None</option>
                                            <?php foreach (PRODUCT_SIZES as $key => $label): ?>
                                                <option value="<?php echo $key; ?>" <?php echo $variant['size'] === $key ? 'selected' : ''; ?>>
                                                    <?php echo $label; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Color</label>
                                        <select name="variants[][color]">
                                            <option value="">None</option>
                                            <?php foreach (PRODUCT_COLORS as $key => $label): ?>
                                                <option value="<?php echo $key; ?>" <?php echo $variant['color'] === $key ? 'selected' : ''; ?>>
                                                    <?php echo $label; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Stock</label>
                                        <input type="number" name="variants[][stock]" value="<?php echo $variant['stock']; ?>" min="0">
                                    </div>
                                    <button type="button" onclick="this.parentElement.remove()">Remove</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="variant-item">
                                <div class="form-group">
                                    <label>Size</label>
                                    <select name="variants[][size]">
                                        <option value="">None</option>
                                        <?php foreach (PRODUCT_SIZES as $key => $label): ?>
                                            <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Color</label>
                                    <select name="variants[][color]">
                                        <option value="">None</option>
                                        <?php foreach (PRODUCT_COLORS as $key => $label): ?>
                                            <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Stock</label>
                                    <input type="number" name="variants[][stock]" value="0" min="0">
                                </div>
                                <button type="button" onclick="this.parentElement.remove()">Remove</button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" onclick="addVariant()" class="btn btn-secondary" style="margin-top: 15px;">+ Add Variant</button>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $is_edit ? 'Update Product' : 'Create Product'; ?>
                    </button>
                    <a href="<?php echo $admin['role'] === 'editor' ? '/CircleUp/admin/editor-dashboard.php' : '/CircleUp/admin/dashboard.php'; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <footer class="product-footer">
        <nav class="footer-nav">
            <a href="/CircleUp/">Home</a>
            <span class="footer-dot"></span>
            <a href="/CircleUp/store/">Shop</a>
            <span class="footer-dot"></span>
            <a href="<?php echo $admin['role'] === 'editor' ? '/CircleUp/admin/editor-dashboard.php' : '/CircleUp/admin/dashboard.php'; ?>">Dashboard</a>
            <span class="footer-dot"></span>
            <a href="/CircleUp/admin/product-form.php">Add Product</a>
        </nav>
        <p>&copy; 2026 CircleUp — Admin Panel</p>
    </footer>

    <script>
        function addVariant() {
            const container = document.getElementById('variants-container');
            const html = `
                <div class="variant-item">
                    <div class="form-group">
                        <label>Size</label>
                        <select name="variants[][size]">
                            <option value="">None</option>
                            <?php foreach (PRODUCT_SIZES as $key => $label): ?>
                                <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Color</label>
                        <select name="variants[][color]">
                            <option value="">None</option>
                            <?php foreach (PRODUCT_COLORS as $key => $label): ?>
                                <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Stock</label>
                        <input type="number" name="variants[][stock]" value="0" min="0">
                    </div>
                    <button type="button" onclick="this.parentElement.remove()">Remove</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }
    </script>
</body>
</html>
