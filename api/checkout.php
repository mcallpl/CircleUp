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
        if (!isset($item['id']) || !isset($item['price'])) continue;
        
        $quantity = $item['quantity'] ?? 1;
        $price = floatval($item['price']);
        
        $line_items[] = [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $item['name'] ?? 'Product',
                    'images' => !empty($item['image']) ? [$item['image']] : [],
                ],
                'unit_amount' => intval($price * 100), // Convert to cents
            ],
            'quantity' => intval($quantity),
        ];
        
        $total_amount += $price * $quantity;
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
        'customer_email' => $data['email'] ?? '',
    ]);
    
    // Create order record in database
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
        if (!isset($item['id'])) continue;
        
        $product_id = intval($item['id']);
        $quantity = intval($item['quantity'] ?? 1);
        $price = floatval($item['price']);
        
        $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iid", $order_id, $product_id, $quantity, $price);
        $stmt->execute();
    }
    
    echo json_encode([
        'success' => true,
        'checkout_url' => $session->url,
        'order_number' => $order_number,
        'session_id' => $session->id,
    ]);
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Payment error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
