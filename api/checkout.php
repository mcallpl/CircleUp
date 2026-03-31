<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['items']) || empty($data['items'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No items in cart']);
    exit();
}

try {
    $db = getDB();
    $line_items = [];
    $total_amount = 0;
    
    // Build Stripe line items
    foreach ($data['items'] as $item) {
        $product_id = $item['product_id'] ?? null;
        $quantity = $item['quantity'] ?? 1;
        
        if (!$product_id) continue;
        
        // Get product
        $stmt = $db->prepare("SELECT id, name, price FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        
        if (!$product) {
            http_response_code(400);
            echo json_encode(['error' => 'Product not found: ' . $product_id]);
            exit();
        }
        
        $line_items[] = [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $product['name'],
                    'images' => [],
                ],
                'unit_amount' => intval($product['price'] * 100),
            ],
            'quantity' => $quantity,
        ];
        
        $total_amount += $product['price'] * $quantity;
    }
    
    if (empty($line_items)) {
        http_response_code(400);
        echo json_encode(['error' => 'No valid items in cart']);
        exit();
    }
    
    // Create Stripe session
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'success_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/CircleUp/store/success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/CircleUp/store/?cancelled=1',
    ]);
    
    // Create order record
    $order_number = 'ORD-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    $status = 'pending';
    $customer_email = $data['email'] ?? '';
    $customer_name = $data['name'] ?? '';
    
    $stmt = $db->prepare("INSERT INTO orders (order_number, stripe_payment_intent_id, customer_email, customer_name, total_amount, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssds", $order_number, $session->payment_intent, $customer_email, $customer_name, $total_amount, $status);
    $stmt->execute();
    $order_id = $db->insert_id;
    
    // Create order items
    foreach ($data['items'] as $item) {
        $product_id = $item['product_id'] ?? null;
        $quantity = $item['quantity'] ?? 1;
        $variant_id = $item['variant_id'] ?? null;
        
        // Get price
        $stmt = $db->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $price = $product['price'] ?? 0;
        
        $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, variant_id, quantity, price) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiid", $order_id, $product_id, $variant_id, $quantity, $price);
        $stmt->execute();
    }
    
    echo json_encode([
        'success' => true,
        'checkout_url' => $session->url,
        'order_number' => $order_number,
    ]);
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Stripe error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
